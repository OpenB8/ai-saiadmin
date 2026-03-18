<?php
namespace plugin\saipay\app\api\controller;

use support\Request;
use support\Response;
use plugin\saipay\service\AlipayService;
use plugin\saipay\service\WechatPayService;
use plugin\saipay\service\UnipayService;
use plugin\saiadmin\basic\OpenController;
use plugin\saipay\app\api\logic\OrderLogic;

/**
 * 回调控制器
 */
class NotifyController extends OpenController
{
    /**
     * 支付宝回调
     * @param Request $request
     * @return Response
     */
    public function alipay(Request $request): Response
    {
        $data = $request->all();
        $result = AlipayService::notify($data);
        if ($result['trade_status'] === "TRADE_SUCCESS") {
            // 处理业务
            $logic = new OrderLogic();
            $logic->notifyOrder($result['out_trade_no'], $result['total_amount']);
            return new Response(200, [], 'success');
        } else {
            return new Response(500, [], 'fail');
        }
    }

    /**
     * 微信支付回调
     * @param Request $request
     * @return Response
     */
    public function wechat(Request $request): Response
    {
        $data = $request->all();
        $result = WechatPayService::notify($data);
        if ($result['resource'] && $result['resource']['ciphertext']) {
            // 处理业务
            $ret = $result['resource']['ciphertext'];
            if ($ret['trade_state'] === 'SUCCESS') {
                $logic = new OrderLogic();
                $logic->notifyOrder($ret['out_trade_no'], $ret['amount']['total'] / 100);
                return new Response(200, [], 'success');
            }
        }
        return new Response(500, [], 'fail');
    }

    /**
     * 银联支付回调
     * @param Request $request
     * @return Response
     */
    public function unipay(Request $request): Response
    {
        $data = $request->all();
        $result = UnipayService::notify($data);
        if ($result['respCode'] === '00') {
            // 处理业务
            $logic = new OrderLogic();
            $logic->notifyOrder($result['orderId'], $result['txnAmt']);
            return new Response(200, [], 'success');
        } else {
            return new Response(500, [], 'fail');
        }
    }

    /**
     * 检查订单支付状态
     * @param Request $request
     * @return Response
     */
    public function checkOrder(Request $request): Response
    {
        $order_no = $request->post('order_no');
        $logic = new OrderLogic();
        $result = $logic->checkOrder($order_no);
        return $this->success($result);
    }

}
