<?php
namespace plugin\saiuser\service\dingtalk;

use plugin\saiadmin\app\logic\system\SystemConfigLogic;
use plugin\saiadmin\utils\Arr;

/**
 * 钉钉配置类
 * Class ConfigService
 * @package plugin\saiuser\service\dingtalk
 */
class ConfigService
{
    /**
     * 获取h5配置
     * @return array
     */
    public static function getH5Config() : array
    {
        $configLogic = new SystemConfigLogic();
        $mngSetting = $configLogic->getGroup('saiuser_dd');
        return [
            'corp_id' => Arr::getConfigValue($mngSetting,'corp_id'),
            'agent_id' => Arr::getConfigValue($mngSetting,'agent_id'),
            'app_key' => Arr::getConfigValue($mngSetting,'app_key'),
            'app_secret' => Arr::getConfigValue($mngSetting,'app_secret'),
        ];
    }

}