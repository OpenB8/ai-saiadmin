<?php
namespace plugin\saiuser\service\wechat;

use plugin\saiadmin\app\logic\system\SystemConfigLogic;
use plugin\saiadmin\utils\Arr;

/**
 * 微信配置类
 * Class WeChatConfigService
 * @package plugin\saiuser\service\wechat
 */
class WeChatConfigService
{
    /**
     * 获取小程序配置
     * @return array
     */
    public static function getMnpConfig() : array
    {
        $configLogic = new SystemConfigLogic();
        $mngSetting = $configLogic->getGroup('saiuser_mnp');
        return [
            'app_id' => Arr::getConfigValue($mngSetting,'app_id'),
            'secret' => Arr::getConfigValue($mngSetting,'app_secret'),
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' =>runtime_path() . '/wechat/' . date('Ym') . '/' . date('d') . '.log'
            ],
        ];
    }

    /**
     * @notes 获取微信公众号配置
     * @return array
     */
    public static function getOaConfig(): array
    {
        $configLogic = new SystemConfigLogic();
        $oaSetting = $configLogic->getGroup('saiuser_oa');
        return [
            'app_id' => Arr::getConfigValue($oaSetting,'wechat_appid'),
            'secret' => Arr::getConfigValue($oaSetting,'wechat_appsecret'),
            'token' => Arr::getConfigValue($oaSetting,'wechat_token'),
            'aes_key' => Arr::getConfigValue($oaSetting,'wechat_encodingaeskey'),
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' =>runtime_path() . '/wechat/' . date('Ym') . '/' . date('d') . '.log'
            ],
        ];
    }

    /**
     * @notes 获取企业微信配置
     * @return array
     */
    public static function getWorkConfig(): array
    {
        $configLogic = new SystemConfigLogic();
        $oaSetting = $configLogic->getGroup('saiuser_work');
        return [
            'corp_id' => Arr::getConfigValue($oaSetting,'corp_id'),
            'agent_id' => Arr::getConfigValue($oaSetting,'agent_id'),
            'secret' => Arr::getConfigValue($oaSetting,'app_secret'),
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' =>runtime_path() . '/wechat/' . date('Ym') . '/' . date('d') . '.log'
            ],
        ];
    }

}