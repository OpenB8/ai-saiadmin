<?php

namespace plugin\saiai\app\api\controller;

use support\Request;
use support\Response;
use plugin\saiadmin\basic\BaseController;
use plugin\saiai\app\api\logic\IndexLogic;
use plugin\saiai\app\api\logic\ChatGroupLogic;
use plugin\saiai\app\api\logic\ChatLogic;
use Workerman\Protocols\Http\ServerSentEvents;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use plugin\saiai\app\service\AiFactory;

class IndexController extends BaseController
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->logic = new IndexLogic();
        parent::__construct();
    }

    /**
     * AI 对话接口（SSE 流式输出）
     */
    public function index(Request $request): void
    {
        $connection = $request->connection;

        // 设置 SSE 响应头
        $connection->send(new Response(200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Credentials' => 'true',
        ], "\r\n"));

        // 发送开始消息
        $connection->send(new ServerSentEvents([
            'event' => 'message',
            'data' => json_encode(['type' => 'start'], JSON_UNESCAPED_UNICODE)
        ]));

        $userMessage = $request->input('message', '你好，介绍一下自己');
        $type = $request->input('type', 'deepseek'); // 支持选择模型类型
        $groupId = $request->input('group_id'); // 会话分组ID

        // 获取当前用户ID
        $userId = $this->adminId;

        // 如果没有分组ID，创建一个新会话
        if (!$groupId) {
            $groupLogic = new ChatGroupLogic();
            //以此提问前10个字作为标题
            $title = mb_substr($userMessage, 0, 10);
            $group = $groupLogic->createGroup($userId, $title);
            $groupId = $group->id;

            // 发送新会话ID给前端
            $connection->send(new ServerSentEvents([
                'event' => 'message',
                'data' => json_encode(['type' => 'session_id', 'data' => $groupId], JSON_UNESCAPED_UNICODE)
            ]));
        } else {
            // 如果已有分组，更新标题（可选，这里暂且只在第一条时定标题，后续不改）
            // 或者可以有一个单独的接口改标题
        }

        // 保存用户提问 (group_id)
        $chatLogic = new ChatLogic();
        $chatLogic->saveChat($userId, 'user', $userMessage, $type, $groupId);

        $generator = $this->chat($userMessage, $type);
        $fullContent = '';

        foreach ($generator as $chunk) {
            // 解析 chunk 提取内容进行拼接
            $data = json_decode($chunk, true);
            if (isset($data['type']) && $data['type'] === 'content') {
                $fullContent .= $data['data'];
            }

            $connection->send(new ServerSentEvents([
                'event' => 'message',
                'data' => $chunk
            ]));
        }

        // 保存 AI 回答
        if ($fullContent) {
            $chatLogic->saveChat($userId, 'assistant', $fullContent, $type, $groupId);
        }

        $connection->close();
    }

    /**
     * 可用模型列表
     * @param Request $request
     * @return Response
     */
    public function modelList(Request $request): Response
    {
        $list = $this->logic->modelList();
        return $this->success($list);
    }

    /**
     * 获取默认模型
     * @param Request $request
     * @return Response
     */
    public function defaultModel(Request $request): Response
    {
        $data = $this->logic->getDefaultModel();
        return $this->success($data);
    }

    protected function chat(string $userMessage, string $type): \Generator
    {
        try {
            $agent = AiFactory::createAgent($type);

            $messages = new MessageBag(
                Message::forSystem('你是一个友好的AI助手，请用中文回答用户的问题。'),
                Message::ofUser($userMessage)
            );

            $response = $agent->call($messages, [
                'temperature' => 0.7,
                'stream' => true,
            ]);

            foreach ($response->getContent() as $content) {
                if ($content) {
                    yield $this->output('content', $content);
                }
            }

            yield $this->output('done', '');

        } catch (\Throwable $e) {
            yield $this->output('error', $e->getMessage());
        }
    }

    protected function output(string $type, mixed $data): string
    {
        return json_encode([
            'type' => $type,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE);
    }
}
