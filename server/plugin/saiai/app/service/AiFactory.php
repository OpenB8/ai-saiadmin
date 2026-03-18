<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: sai <1430792918@qq.com>
// +----------------------------------------------------------------------
namespace plugin\saiai\app\service;

use plugin\saiadmin\exception\ApiException;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\AI\Platform\Bridge\Generic\PlatformFactory;
use Symfony\AI\Platform\Bridge\Generic\ModelCatalog;
use Symfony\AI\Platform\Bridge\Generic\CompletionsModel;
use Symfony\AI\Platform\Capability;

use Symfony\AI\Platform\Bridge\Gemini\PlatformFactory as GeminiPlatformFactory;
use Symfony\AI\Platform\Bridge\OpenAi\PlatformFactory as OpenAIPlatformFactory;
use Symfony\AI\Platform\Bridge\DeepSeek\PlatformFactory as DeepPlatformFactory;

use Symfony\AI\Platform\Bridge\Gemini\ModelCatalog as GeminiModelCatalog;
use Symfony\AI\Platform\Bridge\OpenAi\ModelCatalog as OpenAiModelCatalog;
use Symfony\AI\Platform\Bridge\DeepSeek\ModelCatalog as DeepSeekModelCatalog;

use Symfony\AI\Agent\Agent;
use Symfony\AI\Agent\Toolbox\AgentProcessor;
use Symfony\AI\Agent\Toolbox\Toolbox;
use plugin\saiai\app\tool\DocTool;
use plugin\saiai\app\tool\DbTool;
use plugin\saiai\app\model\config\AiConfig;

class AiFactory
{
    public static function createAgent(string $type): Agent
    {
        $config = AiConfig::where('type', $type)->where('status', 1)->findOrEmpty();
        if ($config->isEmpty()) {
            $config = AiConfig::where('is_default', 1)->where('status', 1)->findOrEmpty();
        }

        if ($config->isEmpty()) {
            throw new ApiException('没有找到模型配置');
        }

        $apiKey = $config->ai_key;
        $model = $config->model;
        $platformType = $config->type;

        switch ($platformType) {
            case 'generic':
                $httpClient = HttpClient::create();
                $modelCatalog = new ModelCatalog([
                    'z-ai/glm4.7' => [
                        'class' => CompletionsModel::class,
                        'capabilities' => [Capability::INPUT_MESSAGES, Capability::OUTPUT_TEXT],
                    ],
                    'minimaxai/minimax-m2.1' => [
                        'class' => CompletionsModel::class,
                        'capabilities' => [Capability::INPUT_MESSAGES, Capability::OUTPUT_TEXT],
                    ],
                ]);
                $platform = PlatformFactory::create('https://integrate.api.nvidia.com', $apiKey, $httpClient, $modelCatalog);
                break;
            case 'openai':
                $platform = OpenAIPlatformFactory::create($apiKey);
                break;
            case 'deepseek':
                $platform = DeepPlatformFactory::create($apiKey);
                break;
            case 'gemini':
            default:
                $platform = GeminiPlatformFactory::create($apiKey);
                break;
        }

        // Register tools
        $toolbox = new Toolbox([
            new DocTool(),
            new DbTool(),
        ]);
        $agentProcessor = new AgentProcessor($toolbox);

        return new Agent($platform, $model, [$agentProcessor], [$agentProcessor]);
    }

    /**
     * 获取模型列表
     * @param string $platform
     * @return array
     */
    public static function getModelCategory(string $platform): array
    {
        $data = [];
        switch ($platform) {
            case 'generic':
                $data = [
                    'z-ai/glm4.7' => 'NVIDIA GLM4.7',
                    'minimaxai/minimax-m2.1' => 'Minimax M2.1'
                ];
                break;
            case 'openai':
                $category = new OpenAiModelCatalog();
                $data = $category->getModels();
                break;
            case 'deepseek':
                $category = new DeepSeekModelCatalog();
                $data = $category->getModels();
                break;
            case 'gemini':
            default:
                $category = new GeminiModelCatalog();
                $data = $category->getModels();
                break;
        }
        $list = [];
        foreach ($data as $key => $value) {
            $list[] = [
                'label' => $key,
                'value' => $key
            ];
        }
        return $list;
    }
}
