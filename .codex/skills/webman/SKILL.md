---
name: Webman Framework Development
description: Webman 高性能 PHP 框架开发指南，包含控制器、路由、中间件、数据库、模型等核心功能的使用方法和最佳实践。
---

# Webman 开发技能指南

## 框架简介

**Webman** 是一款基于 Workerman 构建的高性能 PHP 服务框架，集成了 HTTP、WebSocket、TCP、UDP 等多种模块。通过常驻内存、协程、连接池等技术，突破了传统 PHP 的性能瓶颈。

### 核心特点

- **高稳定性**: 基于 workerman 开发，bug极少的高稳定性socket框架
- **超高性能**: 比传统 php-fpm 框架高 10-100 倍，比 go 的 gin/echo 框架高 1 倍左右
- **高复用**: 可复用现有 composer 生态，无需修改
- **高扩展性**: 支持自定义进程，可做 workerman 能做的任何事情
- **简单易用**: 学习成本极低，代码书写与传统框架没有区别

### 官方资源

- **文档**: https://www.workerman.net/doc/webman/
- **GitHub**: https://github.com/walkor/webman
- **Gitee**: https://gitee.com/walkor/webman

---

## 安装与运行

### 创建项目

```bash
composer create-project workerman/webman:~2.0
```

### 运行方式

#### Linux/Mac

```bash
# 调试模式 (终端关闭后服务也关闭)
php start.php start

# 守护进程方式 (后台运行)
php start.php start -d

# 停止
php start.php stop

# 重启
php start.php restart

# 平滑重启
php start.php reload
```

#### Windows

```bash
# 双击 windows.bat 或运行
php windows.php
```

### 访问

浏览器访问 `http://127.0.0.1:8787`

---

## 目录结构

```
.
├── app                        应用目录
│   ├── controller             控制器目录
│   ├── model                  模型目录
│   ├── view                   视图目录
│   ├── middleware             中间件目录
│   │   └── StaticFile.php     静态文件中间件
│   ├── process                自定义进程目录
│   │   ├── Http.php           Http进程
│   │   └── Monitor.php        监控进程
│   └── functions.php          业务自定义函数
├── config                     配置目录
│   ├── app.php                应用配置
│   ├── autoload.php           自动加载配置
│   ├── bootstrap.php          进程启动回调配置
│   ├── container.php          容器配置
│   ├── dependence.php         容器依赖配置
│   ├── database.php           数据库配置
│   ├── exception.php          异常配置
│   ├── log.php                日志配置
│   ├── middleware.php         中间件配置
│   ├── process.php            自定义进程配置
│   ├── redis.php              redis配置
│   ├── route.php              路由配置
│   ├── server.php             服务器配置
│   ├── view.php               视图配置
│   ├── static.php             静态文件配置
│   ├── translation.php        多语言配置
│   └── session.php            session配置
├── public                     静态资源目录
├── runtime                    运行时目录 (需可写权限)
├── start.php                  服务启动文件
├── vendor                     第三方类库目录
└── support                    类库适配
    ├── Request.php            请求类
    ├── Response.php           响应类
    └── bootstrap.php          进程启动初始化脚本
```

---

## 控制器

### 基本用法

控制器文件放在 `app/controller/` 目录下：

```php
<?php
namespace app\controller;

use support\Request;

class FooController
{
    public function index(Request $request)
    {
        return response('hello index');
    }

    public function hello(Request $request)
    {
        return response('hello webman');
    }
}
```

### 访问规则

- `http://127.0.0.1:8787/foo` → `FooController::index()`
- `http://127.0.0.1:8787/foo/hello` → `FooController::hello()`

### 控制器后缀

在 `config/app.php` 中配置 `controller_suffix`：

```php
return [
    // 设置为 'Controller' 时，控制器类名需要以 Controller 结尾
    'controller_suffix' => 'Controller',
];
```

> **建议**: 将控制器后缀设置为 `Controller`，避免与模型类名冲突。

### 控制器生命周期

在 `config/app.php` 中配置 `controller_reuse`：

- `false`: 每个请求都会创建新的控制器实例 (默认，推荐)
- `true`: 控制器实例复用，性能更好但需注意状态管理

> **警告**: 开启控制器复用时，不要更改控制器属性，因为会影响后续请求。

### 参数绑定

控制器方法可以直接绑定请求参数：

```php
<?php
namespace app\controller;

use support\Response;

class UserController
{
    public function create(string $name, int $age = 18): Response
    {
        return json(['name' => $name, 'age' => $age]);
    }
}
```

---

## 路由

### 默认路由规则

```
http://127.0.0.1:8787/{控制器}/{动作}
```

- 默认控制器: `app\controller\IndexController`
- 默认动作: `index`

### 闭包路由

在 `config/route.php` 中定义：

```php
use support\Request;

Route::any('/test', function (Request $request) {
    return response('test');
});
```

### 类路由

```php
Route::any('/testclass', [app\controller\IndexController::class, 'test']);
```

### 路由方法

```php
Route::get($uri, $callback);     // GET 请求
Route::post($uri, $callback);    // POST 请求
Route::put($uri, $callback);     // PUT 请求
Route::patch($uri, $callback);   // PATCH 请求
Route::delete($uri, $callback);  // DELETE 请求
Route::any($uri, $callback);     // 所有请求方法
```

### 路由参数

```php
// 匹配 /user/123 或 /user/abc
Route::any('/user/{id}', [UserController::class, 'get']);

// 仅匹配数字
Route::any('/user/{id:\d+}', function (Request $request, $id) {
    return response($id);
});

// 可选参数
Route::any('/user[/{name}]', function (Request $request, $name = null) {
    return response($name ?? 'tom');
});
```

### 路由分组

```php
Route::group('/blog', function () {
    Route::any('/create', function (Request $request) {
        return response('create');
    });
    Route::any('/edit', function (Request $request) {
        return response('edit');
    });
    Route::any('/view/{id}', function (Request $request, $id) {
        return response("view $id");
    });
});
```

### 禁用默认路由

```php
Route::disableDefaultRoute();
```

---

## 中间件

### 创建中间件

在 `app/middleware/` 目录下创建：

```php
<?php
namespace app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class AuthCheck implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        if (session('user')) {
            // 已登录，继续处理请求
            return $handler($request);
        }
        // 未登录，重定向到登录页
        return redirect('/user/login');
    }
}
```

### 配置中间件

在 `config/middleware.php` 中配置：

```php
return [
    // 全局中间件
    '' => [
        app\middleware\AuthCheck::class,
        app\middleware\AccessControl::class,
    ],
    // 应用中间件 (仅多应用模式有效)
    'api' => [
        app\middleware\ApiAuth::class,
    ],
];
```

### 身份验证中间件示例

```php
<?php
namespace app\middleware;

use ReflectionClass;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class AuthCheckTest implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        if (session('user')) {
            return $handler($request);
        }

        // 获取不需要登录的方法列表
        $controller = new ReflectionClass($request->controller);
        $noNeedLogin = $controller->getDefaultProperties()['noNeedLogin'] ?? [];

        if (!in_array($request->action, $noNeedLogin)) {
            return redirect('/user/login');
        }

        return $handler($request);
    }
}
```

控制器中配置不需要登录的方法：

```php
class UserController
{
    protected $noNeedLogin = ['login'];

    public function login(Request $request) { /* ... */ }
    public function info() { /* 需要登录 */ }
}
```

### 跨域中间件示例

```php
<?php
namespace app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class AccessControl implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        $response = $request->method() == 'OPTIONS'
            ? response('')
            : $handler($request);

        $response->withHeaders([
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Origin' => $request->header('origin', '*'),
            'Access-Control-Allow-Methods' => $request->header('access-control-request-method', '*'),
            'Access-Control-Allow-Headers' => $request->header('access-control-request-headers', '*'),
        ]);

        return $response;
    }
}
```

---

## 请求 (Request)

### 获取请求对象

```php
use support\Request;

class UserController
{
    public function hello(Request $request)
    {
        // 使用 $request 对象
    }
}

// 或使用助手函数
$request = request();
```

### 获取请求参数

```php
// GET 参数
$all = $request->get();           // 获取所有 GET 参数
$name = $request->get('name');     // 获取单个参数
$name = $request->get('name', 'tom'); // 带默认值

// POST 参数
$all = $request->post();
$name = $request->post('name');
$name = $request->post('name', 'tom');

// 获取所有输入 (GET + POST)
$all = $request->all();
$name = $request->input('name', 'default');

// 助手函数
$name = input('name');
$name = input('name', '张三');
$all = input();
```

### 获取部分数据

```php
// 只获取指定字段
$only = $request->only(['username', 'password']);

// 排除指定字段
$except = $request->except(['avatar', 'age']);
```

### 获取原始请求体

```php
$post = $request->rawBody();
```

### 获取 Header

```php
$headers = $request->header();           // 全部 headers (key 为小写)
$host = $request->header('host');         // 单个 header
$host = $request->header('host', 'localhost'); // 带默认值
```

### 获取 Cookie

```php
$cookies = $request->cookie();
$name = $request->cookie('name');
$name = $request->cookie('name', 'tom');
```

### 文件上传

```php
$file = $request->file('avatar');
if ($file && $file->isValid()) {
    $file->move(public_path() . '/uploads/' . $file->getUploadName());
}
```

### 其他请求信息

```php
$request->host();        // 获取 host
$request->method();      // 获取请求方法
$request->uri();         // 获取 URI
$request->path();        // 获取路径
$request->queryString(); // 获取查询字符串
$request->url();         // 获取完整 URL
$request->getRealIp();   // 获取客户端真实 IP
$request->isAjax();      // 是否 AJAX 请求
```

---

## 响应 (Response)

### 基本响应

```php
// 文本响应
return response('hello webman');

// 带状态码和 headers
return response('hello', 200, ['Content-Type' => 'text/plain']);
```

### JSON 响应

```php
return json(['code' => 0, 'msg' => 'ok']);
```

### XML 响应

```php
$xml = <<<XML
<?xml version='1.0' standalone='yes'?>
<values>
    <truevalue>1</truevalue>
</values>
XML;
return xml($xml);
```

### 视图响应

```php
return view('user/hello', ['name' => 'webman']);
```

### 重定向

```php
return redirect('/user/login');
return redirect('/user/login', 302);
```

### 设置 Header

```php
$response = response();
$response->header('Content-Type', 'application/json');
$response->withHeaders([
    'X-Header-One' => 'Value 1',
    'X-Header-Two' => 'Value 2',
]);
return $response;
```

### 设置 Cookie

```php
$response = response();
$response->cookie('name', 'value', 3600); // 过期时间秒
return $response;
```

### 文件下载

```php
return response()->download('/path/to/file.pdf', 'filename.pdf');
```

---

## 数据库

### 安装

```bash
composer require -W webman/database illuminate/pagination illuminate/events symfony/var-dumper
```

### 配置

`config/database.php`:

```php
return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'test',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'options' => [
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
            'pool' => [
                'max_connections' => 5,
                'min_connections' => 1,
                'wait_timeout' => 3,
                'idle_timeout' => 60,
                'heartbeat_interval' => 50,
            ],
        ],
    ],
];
```

### 查询构建器

```php
use support\Db;

// 查询
$users = Db::table('users')->get();
$user = Db::table('users')->where('id', 1)->first();
$name = Db::table('users')->where('id', 1)->value('name');

// 插入
Db::table('users')->insert([
    'name' => 'webman',
    'email' => 'webman@example.com',
]);

// 更新
Db::table('users')->where('id', 1)->update(['name' => 'new name']);

// 删除
Db::table('users')->where('id', 1)->delete();
```

---

## 模型 (Model)

### 创建模型

`app/model/User.php`:

```php
<?php
namespace app\model;

use support\Model;

class User extends Model
{
    protected $table = 'user';           // 表名
    protected $primaryKey = 'uid';       // 主键 (默认 id)
    public $timestamps = false;          // 是否自动维护时间戳
    protected $connection = 'mysql';     // 数据库连接

    protected $attributes = [            // 默认属性值
        'status' => 1,
    ];
}
```

### 使用模型

```php
use app\model\User;

// 查询
$user = User::find(1);
$users = User::where('status', 1)->get();

// 创建
$user = new User;
$user->name = 'webman';
$user->save();

// 或使用 create (需配置 $fillable)
$user = User::create(['name' => 'webman']);

// 更新
$user = User::find(1);
$user->name = 'new name';
$user->save();

// 删除
User::destroy(1);
$user->delete();
```

---

## Session

### 基本用法

```php
$session = $request->session();

// 存储
$session->set('name', 'webman');
$session->put(['name' => 'webman', 'age' => 18]);

// 获取
$name = $session->get('name');
$name = $session->get('name', 'default');
$all = $session->all();

// 删除
$session->forget('name');
$session->forget(['name', 'age']);
$session->delete('name');
$session->flush(); // 删除所有

// 获取并删除
$name = $session->pull('name');

// 判断存在
$has = $session->has('name');     // null 返回 false
$exists = $session->exists('name'); // null 返回 true
```

### 助手函数

```php
// 获取
$name = session('name');
$name = session('name', 'default');

// 存储
session(['name' => 'value']);
```

---

## 日志

### 使用

```php
use support\Log;

Log::debug('debug message');
Log::info('info message');
Log::notice('notice message');
Log::warning('warning message');
Log::error('error message');
Log::critical('critical message');
Log::alert('alert message');
Log::emergency('emergency message');

// 带上下文
Log::info('User login', ['user_id' => 123, 'ip' => '127.0.0.1']);
```

### 配置

`config/log.php`:

```php
return [
    'default' => [
        'handlers' => [
            [
                'class' => Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    runtime_path() . '/logs/webman.log',
                    Monolog\Logger::DEBUG,
                ],
                'formatter' => [
                    'class' => Monolog\Formatter\LineFormatter::class,
                    'constructor' => [null, 'Y-m-d H:i:s', true],
                ],
            ]
        ],
    ],
];
```

---

## 异常处理

### 配置

`config/exception.php`:

```php
return [
    '' => support\exception\Handler::class,
];
```

### 自定义异常处理

```php
<?php
namespace app\exception;

use Webman\Exception\ExceptionHandlerInterface;
use Webman\Http\Request;
use Webman\Http\Response;
use Throwable;

class Handler implements ExceptionHandlerInterface
{
    public function report(Throwable $e)
    {
        // 记录日志
    }

    public function render(Request $request, Throwable $e): Response
    {
        if ($request->expectsJson()) {
            return json(['code' => 500, 'msg' => $e->getMessage()]);
        }
        return response($e->getMessage(), 500);
    }
}
```

### 业务异常

```php
use support\exception\BusinessException;

throw new BusinessException('用户不存在', 404);
```

---

## 视图

### 配置

`config/view.php`:

```php
<?php
// 原生 PHP
return ['handler' => ''];

// Twig
use support\view\Twig;
return ['handler' => Twig::class];

// Blade
use support\view\Blade;
return ['handler' => Blade::class];

// ThinkPHP
use support\view\ThinkPHP;
return ['handler' => ThinkPHP::class];
```

### 安装视图引擎

```bash
# Twig
composer require twig/twig

# Blade
composer require psr/container ^1.1.1 webman/blade

# ThinkPHP
composer require topthink/think-template
```

### 使用视图

```php
return view('user/hello', ['name' => 'webman']);
```

视图文件位置: `app/view/user/hello.html`

---

## 多应用

### 目录结构

```
app
├── shop              商城应用
│   ├── controller
│   ├── model
│   └── view
├── api               API应用
│   ├── controller
│   └── model
└── admin             管理后台应用
    ├── controller
    ├── model
    └── view
```

### 访问规则

- `http://127.0.0.1:8787/shop/{控制器}/{方法}` → `app/shop/controller`
- `http://127.0.0.1:8787/api/{控制器}/{方法}` → `app/api/controller`
- `http://127.0.0.1:8787/admin/{控制器}/{方法}` → `app/admin/controller`

### 命名空间

```php
<?php
namespace app\api\controller;

use support\Request;

class FooController
{
    // ...
}
```

---

## 常用助手函数

```php
// 请求相关
request()                    // 获取当前请求对象
input($name, $default)       // 获取输入参数
session($key, $default)      // Session 操作

// 响应相关
response($body, $status, $headers)  // 创建响应
json($data, $options)               // JSON 响应
xml($xml)                           // XML 响应
view($template, $vars)              // 视图响应
redirect($location, $status)        // 重定向

// 路径相关
base_path($path)            // 项目根目录
app_path($path)             // app 目录
public_path($path)          // public 目录
config_path($path)          // config 目录
runtime_path($path)         // runtime 目录

// 配置相关
config($key, $default)      // 获取配置

// 环境相关
env($key, $default)         // 获取环境变量
```

---

## 注意事项

### 常驻内存注意点

1. **不要使用全局变量/静态变量存储请求相关数据** - 会导致数据污染
2. **控制器复用时不要修改控制器属性** - 会影响后续请求
3. **数据库连接会自动管理** - 不需要手动关闭
4. **Session 对象销毁时自动保存** - 不要存储到全局变量

### 重启与重载

- `php start.php restart` - 重启 (会断开所有连接)
- `php start.php reload` - 平滑重载 (不断开连接，用于代码更新)
- 安装新的 composer 包后需要 `restart`

### 调试

- 设置 `config/app.php` 中的 `debug` 为 `true` 开启调试模式
- 日志文件位于 `runtime/logs/` 目录

---

## 自定义进程

webman 支持创建自定义进程，可以实现 WebSocket 服务、定时任务等功能。

### WebSocket 服务

创建 `app/Pusher.php`:

```php
<?php
namespace app;

use Workerman\Connection\TcpConnection;

class Pusher
{
    public function onConnect(TcpConnection $connection)
    {
        echo "onConnect\n";
    }

    public function onWebSocketConnect(TcpConnection $connection, $http_buffer)
    {
        echo "onWebSocketConnect\n";
    }

    public function onMessage(TcpConnection $connection, $data)
    {
        $connection->send($data);
    }

    public function onClose(TcpConnection $connection)
    {
        echo "onClose\n";
    }
}
```

在 `config/process.php` 中配置:

```php
return [
    'websocket_test' => [
        'handler' => app\Pusher::class,
        'listen' => 'websocket://0.0.0.0:8888',
        'count' => 1,
    ],
];
```

### 定时任务进程

创建 `app/TaskTest.php`:

```php
<?php
namespace app;

use Workerman\Timer;
use support\Db;

class TaskTest
{
    public function onWorkerStart()
    {
        // 每隔 10 秒执行一次
        Timer::add(10, function(){
            Db::table('users')->where('regist_timestamp', '>', time()-10)->get();
        });
    }
}
```

配置:

```php
return [
    'task' => [
        'handler' => app\TaskTest::class
    ],
];
```

> **注意**: `listen` 省略则不监听任何端口，`count` 省略则进程数默认为 1。

---

## 事件系统 (webman-event)

### 安装

```bash
composer require tinywan/webman-event
```

### 定义事件

`extend/event/LogErrorWriteEvent.php`:

```php
<?php
declare(strict_types=1);

namespace extend\event;

use Symfony\Contracts\EventDispatcher\Event;

class LogErrorWriteEvent extends Event
{
    const NAME = 'log.error.write';

    public array $log;

    public function __construct(array $log)
    {
        $this->log = $log;
    }

    public function handle()
    {
        return $this->log;
    }
}
```

### 配置事件监听

`config/event.php`:

```php
return [
    'listener' => [
        \extend\event\LogErrorWriteEvent::NAME => \extend\event\LogErrorWriteEvent::class,
    ],
    'subscriber' => [],
];
```

### 触发事件

```php
use Tinywan\EventManager\EventManager;
use extend\event\LogErrorWriteEvent;

$error = [
    'errorMessage' => '错误消息',
    'errorCode' => 500
];
EventManager::trigger(new LogErrorWriteEvent($error), LogErrorWriteEvent::NAME);
```

---

## 验证器

### ThinkPHP 验证器

安装:

```bash
composer require topthink/think-validate
```

创建验证器 `app/index/validate/User.php`:

```php
<?php
namespace app\index\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'name'  => 'require|max:25',
        'age'   => 'number|between:1,120',
        'email' => 'email',
    ];

    protected $message = [
        'name.require' => '名称必须',
        'name.max'     => '名称最多不能超过25个字符',
        'age.number'   => '年龄必须是数字',
        'age.between'  => '年龄只能在1-120之间',
        'email'        => '邮箱格式错误',
    ];
}
```

使用:

```php
$data = [
    'name'  => 'thinkphp',
    'email' => 'thinkphp@qq.com',
];

$validate = new \app\index\validate\User;

if (!$validate->check($data)) {
    var_dump($validate->getError());
}
```

### Workerman/Validation

安装:

```bash
composer require workerman/validation
```

使用:

```php
use Respect\Validation\Validator as v;

$data = v::input($request->post(), [
    'nickname' => v::length(1, 64)->setName('昵称'),
    'username' => v::alnum()->length(5, 64)->setName('用户名'),
    'password' => v::length(5, 64)->setName('密码')
]);
```

---

## 分页组件

### 安装

```bash
composer require "jasongrimes/paginator:^1.0.3"
```

### 使用

```php
use JasonGrimes\Paginator;

$total_items = 1000;
$items_perPage = 50;
$current_page = (int)$request->get('page', 1);
$url_pattern = '/user/get?page=(:num)';

$paginator = new Paginator($total_items, $items_perPage, $current_page, $url_pattern);

return view('user/get', ['paginator' => $paginator]);
```

视图中使用:

```php
<?= $paginator; ?>
```

---

## 环境变量 (phpdotenv)

### 安装

```bash
composer require vlucas/phpdotenv
```

### 创建 .env 文件

```env
DB_HOST = 127.0.0.1
DB_PORT = 3306
DB_NAME = test
DB_USER = foo
DB_PASSWORD = 123456
```

### 在配置中使用

```php
return [
    'connections' => [
        'mysql' => [
            'host' => getenv('DB_HOST'),
            'port' => getenv('DB_PORT'),
            'database' => getenv('DB_NAME'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASSWORD'),
        ],
    ],
];
```

> **提示**: 建议将 `.env` 文件加入 `.gitignore`，创建 `.env.example` 作为配置样例。

---

## 权限控制 (Casbin)

### 安装

```bash
composer require tinywan/webman-permission
```

### 快速开始

```php
use webman\permission\Permission;

// 给用户添加权限
Permission::addPermissionForUser('eve', 'articles', 'read');

// 给用户添加角色
Permission::addRoleForUser('eve', 'writer');

// 给角色添加权限
Permission::addPolicy('writer', 'articles', 'edit');
```

### 权限检查

```php
if (Permission::enforce("eve", "articles", "edit")) {
    // 允许编辑文章
} else {
    // 拒绝请求
}
```

### 授权中间件

```php
<?php
namespace app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use webman\permission\Permission;

class AuthorizationMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        $uri = $request->path();
        $userId = 10086; // 从 session 获取
        $action = $request->method();

        if (!Permission::enforce((string) $userId, $uri, strtoupper($action))) {
            throw new \Exception('没有该接口访问权限');
        }

        return $next($request);
    }
}
```

---

## 慢业务处理

### 方案一: 消息队列

适用于需要异步处理的大量请求，结果通过 WebSocket 等方式推送给客户端。

### 方案二: 新增 HTTP 端口

在 `config/process.php` 中新增:

```php
return [
    'task' => [
        'handler' => \Webman\App::class,
        'listen' => 'http://0.0.0.0:8686',
        'count' => 8,
        'constructor' => [
            'requestClass' => \support\Request::class,
            'logger' => \support\Log::channel('default'),
            'appPath' => app_path(),
            'publicPath' => public_path()
        ]
    ]
];
```

可以通过 nginx 代理实现无感知端口切换:

```nginx
upstream webman {
    server 127.0.0.1:8787;
    keepalive 10240;
}

upstream task {
    server 127.0.0.1:8686;
    keepalive 10240;
}

server {
    server_name webman.com;
    listen 80;

    # 以 /task 开头走 8686 端口
    location /task {
        proxy_pass http://task;
    }

    # 其他请求走 8787 端口
    location / {
        proxy_pass http://webman;
    }
}
```

---

## 常用插件

| 插件                      | 功能               | 安装命令                                     |
| ------------------------- | ------------------ | -------------------------------------------- |
| webman/database           | Laravel 数据库组件 | `composer require webman/database`           |
| webman/blade              | Blade 视图引擎     | `composer require webman/blade`              |
| webman/push               | WebSocket 推送     | `composer require webman/push`               |
| tinywan/webman-event      | 事件系统           | `composer require tinywan/webman-event`      |
| tinywan/webman-permission | Casbin 权限        | `composer require tinywan/webman-permission` |
| workerman/validation      | 验证器             | `composer require workerman/validation`      |
| vlucas/phpdotenv          | 环境变量           | `composer require vlucas/phpdotenv`          |

---

## 部署建议

### Nginx 配置

```nginx
upstream webman {
    server 127.0.0.1:8787;
    keepalive 10240;
}

server {
    server_name your-domain.com;
    listen 80;
    access_log off;
    root /path/webman/public;

    location / {
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header Host $host;
        proxy_http_version 1.1;
        proxy_set_header Connection "";
        if (!-f $request_filename) {
            proxy_pass http://webman;
        }
    }
}
```

### 进程管理

推荐使用 Supervisor 管理 webman 进程:

```ini
[program:webman]
command=php /path/webman/start.php start
directory=/path/webman
user=www
numprocs=1
autostart=true
autorestart=true
startsecs=1
startretries=10
exitcodes=0
stopasgroup=true
killasgroup=true
stdout_logfile=/var/log/webman.log
stderr_logfile=/var/log/webman_error.log
```
