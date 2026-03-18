<?php
namespace plugin\saipay\app\api\controller;

use support\Request;
use support\Response;
use plugin\saipay\service\PayService;
use plugin\saipay\app\model\Order;
use plugin\saiadmin\basic\OpenController;

/**
 * 支付使用案例控制器
 */
class DemoController extends OpenController
{
    /**
     * 支付宝扫码支付示例
     */
    public function alipayScan(): Response
    {
        // 订单信息
        $orderData = [
            'order_no' => 'ALI_SCAN_' . uuid(), // 订单号
            'order_name' => '支付宝扫码测试', // 订单名称
            'order_price' => 0.01, // 订单金额
            'pay_price' => 0.00, // 支付金额
            'remark' => NULL,
            'pay_method' => PayService::CHANNEL_ALIPAY, // 支付方式
            'pay_type' => PayService::TYPE_SCAN, // 支付类型
            'pay_status' => 2, // 未支付
            'order_status' => 1, // 已下单
            'plugin' => 'saipay', // 插件名称
            'order_id' => 0, // 关联订单
            'member_id' => 0 // 关联用户
        ];
        // 创建订单
        $model = Order::create($orderData);
        if (!$model) {
            return $this->fail('订单创建失败');
        }

        $params = [
            'out_trade_no' => $orderData['order_no'],
            'total_amount' => $orderData['order_price'],
            'subject' => $orderData['order_name']
        ];
        $result = PayService::pay(PayService::CHANNEL_ALIPAY, PayService::TYPE_SCAN, $params);
        $result['order_price'] = $orderData['order_price'];
        $result['pay_method'] = $orderData['pay_method'];
        $result['pay_url_expire'] = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // 保存支付二维码和过期时间
        $model->pay_url = $result['pay_url'];
        $model->pay_url_expire = $result['pay_url_expire'];
        $model->save();
        
        return $this->success($result);
    }

    /**
     * 微信扫码支付示例
     */
    public function wechatScan(): Response
    {
        // 订单信息
        $orderData = [
            'order_no' => 'WE_SCAN_' . uuid(), // 订单号
            'order_name' => '微信扫码测试', // 订单名称
            'order_price' => 0.01, // 订单金额
            'pay_price' => 0.00, // 支付金额
            'remark' => NULL,
            'pay_method' => PayService::CHANNEL_WECHAT, // 支付方式
            'pay_type' => PayService::TYPE_SCAN, // 支付类型
            'pay_status' => 2, // 未支付
            'order_status' => 1, // 已下单
            'plugin' => 'saipay', // 插件名称
            'order_id' => 0, // 关联订单
            'member_id' => 0 // 关联用户
        ];
        // 创建订单
        $model = Order::create($orderData);
        if (!$model) {
            return $this->fail('订单创建失败');
        }

        $params = [
            'out_trade_no' => $orderData['order_no'],
            'total_amount' => $orderData['order_price'],
            'subject' => $orderData['order_name']
        ];
        $result = PayService::pay(PayService::CHANNEL_WECHAT, PayService::TYPE_SCAN, $params);
        $result['order_price'] = $orderData['order_price'];
        $result['pay_method'] = $orderData['pay_method'];
        $result['pay_type'] = $orderData['pay_type'];
        $result['pay_url_expire'] = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // 保存支付二维码和过期时间
        $model->pay_url = $result['pay_url'];
        $model->pay_url_expire = $result['pay_url_expire'];
        $model->save();
        
        return $this->success($result);
    }

    /**
     * 继续支付
     */
    public function payOrder(Request $result): Response
    {
        $data = $result->post();
        $order = Order::where('order_no', $data['order_no'])->findOrEmpty();
        if ($order->isEmpty()) {
            return $this->fail('订单信息不存在');
        }
        if ($order->pay_status === 1) {
            return $this->fail('订单已支付');
        }
        // 之前已经生成过订单, 且未过期, 直接返回支付链接
        $pay_method = $data['pay_method'] ?? $order->pay_method;
        $pay_type = $data['pay_type'] ?? $order->pay_type;
        if ($pay_method === $order->pay_method) {
            if (!empty($order->pay_url)) {
                if (time() <= strtotime($order->pay_url_expire)) {
                    $result = [
                        'order_price' => $order->order_price,
                        'pay_method' => $order->pay_method,
                        'pay_type' => $order->pay_type,
                        'order_no' => $order->order_no,
                        'pay_url' => $order->pay_url,
                        'pay_url_expire' => $order->pay_url_expire,
                    ];
                    return $this->success($result);
                }
            }
        }
        // 更换了支付方式或者订单支付链接过期，重新拉起支付
        $params = [
            'out_trade_no' => $order->order_no,
            'total_amount' => $order->order_price,
            'subject' => $order->order_name
        ];
        $result = PayService::pay($pay_method, $pay_type, $params);
        $result['order_price'] = $order->order_price;
        $result['pay_method'] = $pay_method;
        $result['pay_type'] = $pay_type;
        $result['pay_url_expire'] = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // 保存支付二维码和过期时间
        $order->pay_method = $pay_method;
        $order->pay_type = $pay_type;
        $order->pay_url = $result['pay_url'];
        $order->pay_url_expire = $result['pay_url_expire'];
        $order->save();
        
        return $this->success($result);
    }
}
