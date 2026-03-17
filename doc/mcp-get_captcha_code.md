# `get_captcha_code` 使用说明

## 作用

`get_captcha_code` 是项目自定义的 MCP 工具，用来根据验证码接口返回的 `uuid`，直接读取 SaiAdmin 后台登录验证码的真实值。

这个工具主要给测试使用，适合下面这类场景：

- 自动化测试需要拿到真实验证码后再调用登录接口
- 联调时不想手工识别图片验证码
- 排查“验证码明明刚生成却校验失败”的问题

## 工具名

```text
get_captcha_code
```

## 参数

只需要传一个参数：

```json
{
  "uuid": "验证码接口返回的 uuid"
}
```

## 返回值

返回验证码真实值字符串，例如：

```json
"dmmg"
```

如果没有找到，会抛出错误：

```text
captcha code not found: <uuid>
```

## 典型使用流程

### 1. 先调用验证码接口拿到 `uuid`

SaiAdmin 登录验证码接口：

```http
GET /core/captcha
```

示例响应：

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "result": 1,
    "uuid": "cb04c12a-a945-4cb6-bf85-d2fad6c40a92",
    "image": "data:image/png;base64,..."
  }
}
```

测试时重点只看 `data.uuid`。

### 2. 调用 `get_captcha_code`

传入上一步拿到的 `uuid`：

```json
{
  "uuid": "cb04c12a-a945-4cb6-bf85-d2fad6c40a92"
}
```

返回：

```json
"dmmg"
```

### 3. 带着 `uuid + code` 调登录接口

SaiAdmin 登录接口：

```http
POST /core/login
```

请求体里至少要带：

```json
{
  "username": "admin",
  "password": "******",
  "type": "pc",
  "uuid": "cb04c12a-a945-4cb6-bf85-d2fad6c40a92",
  "code": "dmmg"
}
```

## 测试建议

推荐按下面顺序做：

1. 先请求 `/core/captcha`
2. 立即记录返回的 `uuid`
3. 立刻调用 `get_captcha_code`
4. 再调用 `/core/login`

不要先发起登录，再回头查验证码。因为登录校验成功或失败后，验证码都会被消费掉。

## 当前实现说明

### 读取逻辑和登录校验保持一致

`get_captcha_code` 底层优先复用 SaiAdmin 自身的验证码读取逻辑：

- 登录校验使用 `server/plugin/saiadmin/utils/Captcha.php`
- MCP 工具使用 `server/app/mcp/SaiAdminCaptchaTool.php`

也就是说：

- 登录怎么读验证码，这个工具就怎么读验证码
- 区别仅在于登录读取后会删除验证码
- `get_captcha_code` 只读取，不删除

### 当前项目建议配置

为了让测试可以稳定通过 `uuid` 读取验证码，建议使用下面配置：

```dotenv
CACHE_MODE = redis
CAPTCHA_MODE = cache
REDIS_HOST = 127.0.0.1
REDIS_PORT = 6379
REDIS_PASSWORD = ''
REDIS_DB = 0
```

相关配置文件：

- `server/config/think-cache.php`
- `server/plugin/saiadmin/config/saithink.php`

## 常见问题

### 1. 为什么明明刚生成验证码，却提示 `captcha code not found`

常见原因有这些：

- 传错了 `uuid`
- 验证码已经过期
- 已经拿这条验证码发过登录，登录校验时会删除
- 修改了 `.env` 但实际运行中的 Webman/MCP 进程还没重启或刷新
- 当前不是 `CAPTCHA_MODE=cache`

### 2. 为什么 Redis 里直接 `GET <uuid>` 查不到

因为 Think Cache 的实际 Redis key 带前缀，真实 key 不是裸 `uuid`，而是：

```text
cache:<uuid>
```

而且 Redis 里存的是序列化后的值，例如：

```text
s:4:"dmmg";
```

所以测试不要直接按裸 key 推断验证码是否存在，优先使用 `get_captcha_code`。

### 3. 改了配置后为什么工具行为没变

Webman 和 MCP 都是常驻进程。修改下面这些配置后，需要重启或刷新运行中的服务：

- `server/.env`
- `server/config/think-cache.php`
- `server/plugin/saiadmin/config/saithink.php`

同时，Codex/桌面侧如果要立即看到新工具，也需要刷新 MCP 工具列表。

## 适用边界

这个工具的目标是方便测试直接读取验证码，不建议把它暴露给生产环境的非测试用途。

如果当前验证码模式是 `session`，那验证码不适合通过这个工具跨请求读取；测试场景请优先使用：

```dotenv
CAPTCHA_MODE = cache
```
