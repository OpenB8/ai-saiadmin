# `saisms` 插件复用说明

## 适用场景

这份说明用于把当前项目里的 `saisms` 短信插件，复用到另一个 SaiAdmin / Webman 项目中。

当前这套插件已经包含：

- 短信配置管理
- 短信标签管理
- 短信发送记录管理
- `easy-sms` 统一发送封装
- 自定义网关 `link`
- 已接入的短信宝网关 `smsbao`

## 必须复制的目录

后端插件代码：

```text
server/plugin/saisms
```

前端管理页面：

```text
saiadmin-artd/src/views/plugin/saisms
```

如果目标项目目录结构和当前项目一致，通常直接复制这两个目录即可。

## 依赖要求

后端需要安装：

```bash
cd server
composer require overtrue/easy-sms
```

当前项目里依赖已经写在：

```text
server/composer.json
```

## 后端核心文件

复用时重点看这几类文件：

- `server/plugin/saisms/service/Sms.php`
- `server/plugin/saisms/service/Smsbao.php`
- `server/plugin/saisms/service/Link.php`
- `server/plugin/saisms/app/admin/controller/*`
- `server/plugin/saisms/app/admin/logic/*`
- `server/plugin/saisms/app/model/*`
- `server/plugin/saisms/app/validate/*`
- `server/plugin/saisms/config/saithink.php`

其中：

- `Sms.php` 是统一发送入口
- `Smsbao.php` 是短信宝网关实现
- `Link.php` 是另一个自定义网关示例

## 前端核心文件

前端页面主要分 3 组：

- `saiadmin-artd/src/views/plugin/saisms/config`
- `saiadmin-artd/src/views/plugin/saisms/tag`
- `saiadmin-artd/src/views/plugin/saisms/record`

接口层在：

- `saiadmin-artd/src/views/plugin/saisms/api/config.ts`
- `saiadmin-artd/src/views/plugin/saisms/api/tag.ts`
- `saiadmin-artd/src/views/plugin/saisms/api/record.ts`

这些接口默认请求的是：

```text
/app/saisms/admin/SmsConfig/*
/app/saisms/admin/SmsTag/*
/app/saisms/admin/SmsRecord/*
```

所以目标项目也要保留同样的插件控制器路径。

## 数据库要求

### 1. 必须有 3 张表

```text
saisms_config
saisms_tag
saisms_record
```

用途分别是：

- `saisms_config`：短信网关配置
- `saisms_tag`：短信标签与模板内容
- `saisms_record`：发送记录与验证码记录

### 2. 必须补菜单和按钮权限

至少要有这 3 个页面菜单：

- `SAISMS`
- `短信配置`
- `短信标签`
- `短信记录`

还要有这些按钮权限编码：

```text
saisms:config:index
saisms:config:save
saisms:config:update
saisms:config:read
saisms:config:destroy
saisms:config:changeStatus

saisms:tag:index
saisms:tag:save
saisms:tag:update
saisms:tag:read
saisms:tag:destroy
saisms:tag:testTag

saisms:record:index
saisms:record:read
saisms:record:destroy
```

如果页面能进、按钮却不显示，优先检查 `sa_system_menu.code` 有没有补齐这些值。

### 3. 推荐补默认数据

推荐至少补这两条默认数据：

短信配置：

```json
{
  "gateway": "smsbao",
  "config_name": "短信宝",
  "config": {
    "user": "",
    "password": "",
    "api_key": ""
  }
}
```

短信标签：

```json
{
  "tag_name": "action_code",
  "gateway": "smsbao",
  "template_id": "",
  "content": "【你的签名】您的验证码是{code}，5分钟内有效。"
}
```

## 短信宝配置说明

短信宝网关标识固定是：

```text
smsbao
```

后台配置时建议这样填：

- `gateway`: `smsbao`
- `config_name`: `短信宝`
- `config.user`: 短信宝用户名
- `config.password`: 短信宝登录密码
- `config.api_key`: 可选，已开通的话优先使用

说明：

- `password` 支持明文，发送时会自动转成 MD5
- 如果已经有 `api_key`，发送时优先使用 `api_key`
- 短信宝当前主要依赖 `content` 直接发短信，`template_id` 可以为空
- `content` 里建议直接写入短信签名

## 插件缓存配置

插件网关缓存配置在：

```text
server/plugin/saisms/config/saithink.php
```

当前使用的配置读取键是：

```text
plugin.saisms.saithink.saisms_gateway
```

如果你复制到别的项目，记得不要写成别的插件名。

## 业务代码怎么调用

推荐统一走：

```php
$smsLogic = new \plugin\saisms\app\admin\logic\SmsRecordLogic();
$result = $smsLogic->sendCode([
    'mobile' => '13800138000',
    'tag_name' => 'action_code',
]);
```

如果你要强制指定短信宝发送：

```php
$smsLogic = new \plugin\saisms\app\admin\logic\SmsRecordLogic();
$result = $smsLogic->sendCode([
    'mobile' => '13800138000',
    'tag_name' => 'action_code',
    'gateway' => ['smsbao'],
]);
```

这套逻辑已经包含：

- 生成验证码
- 根据标签拼接短信内容
- 发送短信
- 落发送记录
- 保存发送状态和返回结果

## 发送测试

后台已经有测试入口：

```text
SAISMS -> 短信标签 -> 发送测试
```

建议复用完成后，按这个顺序验证：

1. 新增或检查 `smsbao` 网关配置
2. 检查 `action_code` 标签内容
3. 在“短信标签”页点“发送测试”
4. 去“短信记录”页查看发送结果

## 已处理的坑

### 1. `saisms_record` 不能走软删除

`saisms_record` 表没有 `delete_time` 字段，所以模型里必须显式关闭软删除。

当前实现已经处理在：

```text
server/plugin/saisms/app/model/SmsRecord.php
```

如果不关，会报类似错误：

```text
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'saisms_record.delete_time'
```

### 2. `sa_system_menu` 按钮节点可能只有名称没有 `code`

有些库里页面菜单已经存在，但按钮权限节点的 `code` 为空。

这时页面路由能进，但前端权限判断会失效。需要把按钮节点的 `code` 补成上面那组 `saisms:*` 权限编码。

### 3. 复制后要清缓存

如果你在目标项目里改过短信配置、网关配置或者缓存键，建议：

- 重启 Webman
- 重新登录后台
- 重新进入 SAISMS 页面测试

## 最小复用清单

如果你只想最快复用，按这个最小清单做：

1. 复制 `server/plugin/saisms`
2. 复制 `saiadmin-artd/src/views/plugin/saisms`
3. 安装 `overtrue/easy-sms`
4. 建 `saisms_config / saisms_tag / saisms_record`
5. 补 `SAISMS` 菜单和 `saisms:*` 权限编码
6. 新增 `smsbao` 配置
7. 新增 `action_code` 标签
8. 重启后端和前端
9. 用“短信标签 -> 发送测试”验证
