# SaiPay 统一支付插件

## 简介
`saipay` 是基于 SaiAdmin 框架开发的统一支付插件，旨在简化支付功能的集成。它封装了支付宝（Alipay）和微信支付（WechatPay）的常用接口，提供了统一的调用方式，并支持跨模块调用。

## 功能特性
- **统一接口**：通过 `PayService::pay()` 一个方法即可完成多种渠道和类型的支付。
- **多渠道支持**：目前支持支付宝和微信支付。
- **多场景支持**：支持扫码支付（Scan）、网页支付（Web）、H5支付、App支付、小程序支付（Mini/MP）等。
- **配置灵活**：支持从数据库读取配置（SystemConfigLogic），也支持动态传递配置参数（适合多商户模式）。
- **跨模块调用**：其他应用插件（如 `doc`、`shop` 等）可以直接调用本插件的服务，无需重复集成支付 SDK。

## 目录结构
```
server/plugin/saipay/
├── app/
│   ├── api/controller/DemoController.php  # 使用示例控制器
│   └── model/Order.php                    # 订单模型
├── config/
│   └── payment.php                        # 基础支付配置
├── service/
│   ├── PayService.php                     # 核心统一支付服务
│   ├── AlipayService.php                  # 支付宝服务实现
│   └── WechatPayService.php               # 微信支付服务实现
└── README.md                              # 本文档
```

## 安装与配置

### 1. 配置参数
插件默认会尝试读取系统配置（`saipay` 分组下的配置）。您也可以在调用时动态传递配置。

### 2. 路由配置
确保 `config/route.php` 中已注册相关路由（如果需要对外提供 API）。

## 核心用法

### 引入服务
```php
use plugin\saipay\service\PayService;
```

### 发起支付
```php
// 方法签名
public static function pay(string $channel, string $type, array $params, array $config = [])
```

- **$channel**: 支付渠道
    - `PayService::CHANNEL_ALIPAY` ('alipay')
    - `PayService::CHANNEL_WECHAT` ('wechat')
- **$type**: 支付类型
    - `PayService::TYPE_SCAN` (扫码支付)
    - `PayService::TYPE_WEB` (电脑网页支付)
    - `PayService::TYPE_H5` (手机网页支付)
    - `PayService::TYPE_APP` (APP支付)
    - `PayService::TYPE_MINI` (小程序支付)
- **$params**: 订单参数
    - `out_trade_no`: 商户订单号 (必填)
    - `total_amount`: 订单金额 (必填, 单位: 元)
    - `subject`: 订单标题 (必填)
    - `quit_url`: H5支付同步跳转地址 (选填)
    - `openid`: 用户OpenID (微信小程序/公众号支付必填)
- **$config**: 额外配置 (可选)
    - 用于覆盖默认配置，例如多商户场景下传入特定商户的 `app_id`、`private_key` 等。

## 使用案例

以下代码摘自 `app/api/controller/DemoController.php`，展示了如何在控制器中使用。

### 1. 支付宝扫码支付
```php
use plugin\saipay\service\PayService;

public function alipayScan()
{
    // 1. 准备订单参数
    $params = [
        'out_trade_no' => 'ALI_' . time(),
        'total_amount' => 0.01,
        'subject'      => '支付宝扫码测试'
    ];

    try {
        // 2. 调用支付服务
        $result = PayService::pay(
            PayService::CHANNEL_ALIPAY, 
            PayService::TYPE_SCAN, 
            $params
        );
        
        // 3. 处理返回结果
        // 支付宝扫码支付返回的是包含二维码链接的数组：['pay_url' => 'qr_code_url']
        return json(['code' => 200, 'data' => $result]);
        
    } catch (\Exception $e) {
        return json(['code' => 500, 'msg' => $e->getMessage()]);
    }
}
```

### 2. 微信扫码支付
```php
use plugin\saipay\service\PayService;

public function wechatScan()
{
    $params = [
        'out_trade_no' => 'WE_' . time(),
        'total_amount' => 0.01,
        'subject'      => '微信扫码测试'
    ];

    try {
        $result = PayService::pay(
            PayService::CHANNEL_WECHAT, 
            PayService::TYPE_SCAN, 
            $params
        );
        
        // 微信扫码支付返回结果通常也包含 'pay_url'
        return json(['code' => 200, 'data' => $result]);
        
    } catch (\Exception $e) {
        return json(['code' => 500, 'msg' => $e->getMessage()]);
    }
}
```

### 3. 跨模块动态配置调用（多商户模式）
假设您在开发一个 `shop` 插件，每个店铺有自己的支付账号。

```php
use plugin\saipay\service\PayService;

public function shopPay($storeId)
{
    // 1. 获取店铺支付配置 (示例)
    $storeConfig = [
        'app_id' => '20210001...',
        'private_key' => 'MIIEvA...',
        'ali_public_key' => 'MIIBIj...',
        // ... 其他必要配置
    ];

    // 2. 准备订单参数
    $params = [
        'out_trade_no' => 'SHOP_' . time(),
        'total_amount' => 100.00,
        'subject'      => '店铺商品购买'
    ];

    // 3. 传入配置进行支付
    $result = PayService::pay(
        PayService::CHANNEL_ALIPAY, 
        PayService::TYPE_SCAN, 
        $params, 
        $storeConfig // <--- 关键：传入动态配置
    );

    return $result;
}
```

## 注意事项
1. **金额单位**：`PayService` 统一使用 **元** 为单位，内部会自动根据渠道转换为分（如微信支付）。
2. **异常处理**：建议使用 `try-catch` 捕获异常，以便处理支付失败的情况。
3. **回调处理**：支付回调逻辑需在 `NotifyController` 中实现（待完善）。
