# SaiSms 统一短信插件

## 简介
`saisms` 是基于 SaiAdmin 框架开发的统一短信插件，旨在简化短信功能的集成。基于开源项目 [easy-sms](https://github.com/overtrue/easy-sms) 进行封装处理，内置了配置表、标签表、短信记录表，提供了统一的调用方式，并支持跨模块调用。

## 功能特性
- **支持目前市面多家服务商**
- **一套写法兼容所有平台**
- **简单配置即可灵活增减服务商**
- **内置多种服务商轮询策略、支持自定义轮询策略**
- **统一的返回值格式，便于日志与监控**
- **自动轮询选择可用的服务商**

## 目录结构

```
server/plugin/saisms/
├── app/
│   ├── admin/                             # 后端管理模块
├── config/
│   └── saithink.php                       # 缓存配置
├── service/
│   ├── Link.php                           # 自定义第三方服务商`凌凯`案例
│   └── SMS.php                            # 短信发送功能实现
└── README.md                              # 本文档
```


## 使用案例

### 短信发送

```php
// 短信发送功能
$smsLogic = new \plugin\saisms\app\admin\logic\SmsRecordLogic();
// 短信内容
$phoneData = [
    'mobile' => $data['mobile'],
    'tag_name' => 'action_code', // 使用标签
];
// 发送执行
$result = $smsLogic->sendCode($phoneData);
if ($result['status'] === 'success') {
    return true;
} else {
    return false;
}


```

### 短信验证

```php
// 短信验证
$smsLogic = new \plugin\saisms\app\admin\logic\SmsRecordLogic();
$model = $smsLogic->where('mobile', $data['mobile'])
    ->where('status', 'success')
    ->order('create_time', 'desc')
    ->findOrEmpty();
if ($model->isEmpty() || ($model->is_verify == 1) || (strtotime($model->create_time) < time() - 5 * 60)) {
    // 5分钟有效期
    throw new ApiException('验证码错误或已过期');
}
if ($model->code != trim($data['code'])) {
    throw new ApiException('验证码错误或已过期');
}
$model->is_verify = 1;
$model->save();

```

## 注意事项
1. **短信配置**：请先到 `SAISMS` -> `短信配置` 中配置短信服务商。
2. **标签配置**：标签主要是模板功能，就是你当前要使用哪个模板或者内容发送短信。
