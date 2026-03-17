<?php

namespace plugin\saisms\service;

use Overtrue\EasySms\Exceptions\GatewayErrorException;
use Overtrue\EasySms\Gateways\Gateway;
use Overtrue\EasySms\Contracts\MessageInterface;
use Overtrue\EasySms\Contracts\PhoneNumberInterface;
use Overtrue\EasySms\Support\Config;
use Overtrue\EasySms\Traits\HasHttpRequest;

/**
 * Class Link.
 * 凌凯短信网关
 */
class Link extends Gateway
{
    use HasHttpRequest;

    public const ENDPOINT_URL = 'https://inolink.com/ws/BatchSend2.aspx';

    public function send(PhoneNumberInterface $to, MessageInterface $message, Config $config)
    {
        $params = [
            'CorpID' => $config->get('CorpID'),
            'Pwd' => $config->get('Pwd'),
            'Mobile' => $to->getNumber(),
            'Content' => mb_convert_encoding($message->getContent($this), "gb2312", "utf-8")
        ];
        $result = $this->get(self::ENDPOINT_URL, $params);
        if ($result < 0) {
            throw new GatewayErrorException('短信发送失败', $result, []);
        }
        return $result;
    }

}
