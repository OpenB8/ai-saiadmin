<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\admin\logic;

use plugin\saiadmin\app\model\system\SystemMail;
use plugin\saiadmin\basic\think\BaseLogic;
use plugin\saiadmin\exception\ApiException;
use plugin\saiadmin\exception\SystemException;
use plugin\saiadmin\service\EmailService;
use plugin\saiadmin\utils\Arr;
use plugin\saiuser\app\admin\logic\member\MemberLogic;
use plugin\saiuser\app\model\member\Member;
use plugin\saiuser\app\model\setting\MemberProtocol;
use plugin\saiuser\app\model\setting\SiteInfo;
use plugin\saiuser\service\dingtalk\DingtalkService;
use plugin\saiuser\service\wechat\WeChatMnpService;
use plugin\saiuser\service\wechat\WeChatService;
use plugin\saiuser\service\wechat\WorkService;
use support\Response;

/**
 * 基础功能逻辑层
 */
class IndexLogic extends BaseLogic
{
    /**
     * 协议信息
     * @param $type
     * @return array
     */
    public function protocol($type): array
    {
        $info = MemberProtocol::where('protocol_type', $type)->where('status', 1)->findOrEmpty();
        if ($info->isEmpty()) {
            throw new ApiException('数据查找失败');
        }
        return $info->toArray();
    }

    /**
     * 站点信息
     * @return array
     */
    public function siteInfo(): array
    {
        $model = SiteInfo::where('id', 1)->findOrEmpty();
        if ($model->isEmpty()) {
            throw new ApiException('站点信息读取失败');
        }
        return $model->toArray();
    }

    /**
     * 发送邮件
     * @param $email
     * @param $type
     * @return bool
     */
    public function emailSend($email, $type = 1): bool
    {
        $email = trim($email);
        $user = Member::where('email', $email)->findOrEmpty();
        if ($type == 1) {
            if (!$user->isEmpty()) {
                throw new ApiException('该邮箱账号已经注册，请更换邮箱或者直接登录！');
            }
        } else {
            if ($user->isEmpty()) {
                throw new ApiException('邮箱查找失败，请检查邮箱是否正确');
            }
        }
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $model = SystemMail::where('email', $email)->where('status', 'success')->order('update_time', 'desc')->findOrEmpty();
        if (!$model->isEmpty()) {
            $interval = 60 * 2;
            if (time() - strtotime($model->update_time) < $interval) {
                throw new ApiException('请勿频繁发送验证码，等待2分钟后再操作');
            }
            $model->code = $code;
            $model->save();
        } else {
            $config = EmailService::getConfig();
            $model = SystemMail::create([
                'gateway' => Arr::getConfigValue($config, 'Host'),
                'from' => Arr::getConfigValue($config, 'From'),
                'email' => $email,
                'code' => $code,
            ]);
        }
        $subject = config('plugin.saiuser.saithink.email.subject', 'saiadmin移动端');
        $content = config('plugin.saiuser.saithink.email.content', '如您未发起过此邮件，请忽略！');
        if ($type == 1) {
            $subject = $subject . "-注册平台账号";
        } else {
            $subject = $subject . "-平台密码找回";
        }
        $content = "<h1>验证码：{code}</h1><p>$content</p>";
        $template = [
            'code' => $code
        ];
        try {
            $result = EmailService::sendByTemplate($email, $subject, $content, $template);
            if (!empty($result)) {
                $model->status = 'failure';
                $model->response = $result;
                $model->save();
                throw new ApiException('发送失败，请查看日志');
            } else {
                $model->status = 'success';
                $model->save();
                return true;
            }
        } catch (\Exception $e) {
            $model->status = 'failure';
            $model->response = $e->getMessage();
            $model->save();
            throw new SystemException($e->getMessage());
        }
    }

    /**
     * 小程序登录
     * @param $data
     * @return array
     */
    public function mnpLogin($data): array
    {
        try {
            $wechatService = new WeChatMnpService();
            $data = $wechatService->getMnpResByCode($data['code']);
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage());
        }
        $logic = new MemberLogic();
        return $logic->thirdPlatform($data);
    }

    /**
     * 公众号服务端验证
     * @param $request
     * @return Response
     */
    public function wechat($request): \support\Response
    {
        $wechatService = new WeChatService();
        return $wechatService->serve($request);
    }

    /**
     * 企业微信 oauth 地址
     * @param $request
     * @return string
     */
    public function wechatOauth($request): string
    {
        $wechatService = new WeChatService();
        return $wechatService->getOAuthUrl($request);
    }

    /**
     * 微信公众号登录
     * @param $data
     * @return array
     */
    public function wechatLogin($data): array
    {
        try {
            $wechatService = new WeChatService();
            $data = $wechatService->getWechatByCode($data['code']);
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage());
        }
        $logic = new MemberLogic();
        return $logic->thirdPlatform($data);
    }

    /**
     * 企业微信 oauth 地址
     * @param $request
     * @return string
     */
    public function workOauth($request): string
    {
        $wechatService = new WorkService();
        return $wechatService->getOAuthUrl($request);
    }

    /**
     * 企业微信 登录
     * @param $data
     * @return array
     */
    public function workLogin($data): array
    {
        try {
            $wechatService = new WorkService();
            $data = $wechatService->getWorkByCode($data['code']);
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage());
        }
        $logic = new MemberLogic();
        return $logic->thirdPlatform($data);
    }

    /**
     * 钉钉 获取CropId
     * @return string
     */
    public function dingtalkCropId(): string
    {
        $dingtalkService = new DingtalkService();
        return $dingtalkService->getCropId();
    }

    /**
     * 钉钉 登录
     * @param $data
     * @return array
     */
    public function dingtalkLogin($data): array
    {
        try {
            $dingtalkService = new DingtalkService();
            $data = $dingtalkService->getUserByCode($data['code']);
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage());
        }
        $logic = new MemberLogic();
        return $logic->thirdPlatform($data);
    }

}
