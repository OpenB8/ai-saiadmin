---
name: ThinkORM 3.0 Development
description: ThinkORM 3.0 数据库 ORM 开发指南，包含数据库配置、CRUD 操作、链式查询、模型定义、关联关系、模型事件等核心功能的使用方法和最佳实践。
---

# ThinkORM 3.0 开发技能指南

## 框架简介

**ThinkORM** 是一个基于 PHP 和 PDO 的数据库中间层和 ORM 类库，早期作为 ThinkPHP 的核心组件，现已独立出来。3.0+ 版本要求 PHP 8.0+。

### 主要特性

- 基于 PDO 和 PHP 强类型实现
- 支持原生查询和查询构造器
- 自动参数绑定和预查询
- 强大灵活的模型用法
- 支持预载入关联查询和延迟关联查询
- 支持多数据库及动态切换
- 支持 MongoDb、MySQL、PostgreSQL、SQLite、SqlServer、Oracle
- 支持分布式及事务
- 支持断点重连、JSON 查询
- 支持 PSR-16 缓存及 PSR-3 日志规范

### 安装

```bash
composer require topthink/think-orm
```

---

## 数据库配置

### 基础配置

```php
use think\facade\Db;

Db::setConfig([
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'type'     => 'mysql',      // 数据库类型
            'hostname' => '127.0.0.1',  // 主机地址
            'username' => 'root',       // 用户名
            'password' => '',           // 密码
            'database' => 'demo',       // 数据库名
            'hostport' => '3306',       // 端口号
            'charset'  => 'utf8mb4',    // 编码
            'prefix'   => 'think_',     // 表前缀
            'debug'    => true,         // 调试模式
        ],
    ],
]);
```

### 常用配置参数

| 参数              | 描述                            | 默认值    |
| ----------------- | ------------------------------- | --------- |
| `type`            | 数据库类型                      | 无        |
| `hostname`        | 数据库地址                      | 127.0.0.1 |
| `database`        | 数据库名称                      | 无        |
| `username`        | 用户名                          | 无        |
| `password`        | 密码                            | 无        |
| `hostport`        | 端口号                          | 无        |
| `charset`         | 编码                            | utf8      |
| `prefix`          | 表前缀                          | 无        |
| `deploy`          | 分布式部署 (0-集中式, 1-分布式) | 0         |
| `rw_separate`     | 读写分离                        | false     |
| `fields_strict`   | 严格检查字段                    | true      |
| `auto_timestamp`  | 自动时间戳                      | false     |
| `break_reconnect` | 断线重连                        | false     |
| `fields_cache`    | 字段缓存                        | false     |

### 分布式数据库 (读写分离)

```php
'mysql' => [
    'deploy'      => 1,  // 启用分布式
    'rw_separate' => true,  // 开启读写分离
    'hostname'    => '192.168.1.1,192.168.1.2,192.168.1.3',
    'database'    => 'demo',
    'username'    => 'root,slave,slave',
    'password'    => '123456',
],
```

> 第一个地址是主服务器（写），其余是从服务器（读）

---

## 查询数据

### 查询单条数据

```php
// 使用 table (完整表名)
Db::table('think_user')->where('id', 1)->find();

// 使用 name (自动加前缀)
Db::name('user')->where('id', 1)->find();

// 查询不存在返回空数组
Db::name('user')->where('id', 1)->findOrEmpty();

// 查询不存在抛出异常
Db::name('user')->where('id', 1)->findOrFail();
```

### 查询多条数据

```php
// 查询数据集
$list = Db::name('user')->where('status', 1)->select();

// 转换为数组
$list = Db::name('user')->where('status', 1)->select()->toArray();

// 判断数据集是否为空
if ($list->isEmpty()) {
    echo '数据集为空';
}
```

### 查询单个字段值

```php
// 获取某个字段的值
$name = Db::name('user')->where('id', 1)->value('name');

// 获取某列的值
$names = Db::name('user')->where('status', 1)->column('name');

// 指定索引
$users = Db::name('user')->where('status', 1)->column('name', 'id');

// 返回所有字段并指定索引
$users = Db::name('user')->where('status', 1)->column('*', 'id');
```

### 数据分批处理

```php
// 分批处理，每次100条
Db::name('user')->chunk(100, function($users) {
    foreach ($users as $user) {
        // 处理数据
    }
    // 返回 false 中止后续处理
    // return false;
});

// 游标查询 (大数据低内存)
foreach (Db::name('user')->where('status', 1)->cursor() as $user) {
    echo $user['name'];
}
```

---

## 新增数据

### 单条新增

```php
// save 方法 (自动判断新增/更新)
$data = ['name' => 'thinkphp', 'email' => 'test@qq.com'];
Db::name('user')->save($data);

// insert 方法
Db::name('user')->insert($data);

// 新增并返回自增ID
$userId = Db::name('user')->insertGetId($data);

// 关闭严格模式 (忽略不存在的字段)
Db::name('user')->strict(false)->insert($data);

// MySQL replace 写入
Db::name('user')->replace()->insert($data);
```

### 批量新增

```php
$data = [
    ['name' => 'user1', 'email' => 'user1@qq.com'],
    ['name' => 'user2', 'email' => 'user2@qq.com'],
    ['name' => 'user3', 'email' => 'user3@qq.com'],
];
Db::name('user')->insertAll($data);

// 分批写入，每次最多1000条
Db::name('user')->limit(1000)->insertAll($data);
```

---

## 更新数据

### 基础更新

```php
// save 方法 (需要包含主键)
Db::name('user')->save(['id' => 1, 'name' => 'thinkphp']);

// update 方法
Db::name('user')->where('id', 1)->update(['name' => 'thinkphp']);

// 使用 data 方法
Db::name('user')->where('id', 1)->data(['name' => 'thinkphp'])->update();
```

### 自增/自减

```php
// 自增
Db::name('user')->where('id', 1)->inc('score')->update();
Db::name('user')->where('id', 1)->inc('score', 5)->update();

// 自减
Db::name('user')->where('id', 1)->dec('score')->update();
Db::name('user')->where('id', 1)->dec('score', 5)->update();

// 延时更新 (600秒后写入)
Db::name('blog')->where('id', 1)->setInc('read_count', 1, 600);
```

### 使用 SQL 函数

```php
Db::name('user')->where('id', 1)->update([
    'name'      => raw('UPPER(name)'),
    'score'     => raw('score-3'),
    'read_time' => raw('read_time+1'),
]);
```

---

## 删除数据

```php
// 根据主键删除
Db::name('user')->delete(1);
Db::name('user')->delete([1, 2, 3]);

// 条件删除
Db::name('user')->where('id', 1)->delete();
Db::name('user')->where('id', '<', 10)->delete();

// 删除所有数据 (危险)
Db::name('user')->delete(true);

// 软删除
Db::name('user')->where('id', 1)
    ->useSoftDelete('delete_time', time())
    ->delete();
```

---

## 链式操作

```php
Db::name('user')
    ->where('status', 1)
    ->field('id, name, email')
    ->order('create_time', 'desc')
    ->limit(10)
    ->select();
```

### 常用链式方法

| 方法        | 说明         |
| ----------- | ------------ |
| `where`     | AND 查询条件 |
| `whereOr`   | OR 查询条件  |
| `whereTime` | 时间查询     |
| `field`     | 指定查询字段 |
| `order`     | 排序         |
| `limit`     | 限制数量     |
| `page`      | 分页查询     |
| `group`     | 分组         |
| `having`    | 分组筛选     |
| `join`      | 联表查询     |
| `alias`     | 表别名       |
| `distinct`  | 去重         |
| `lock`      | 锁机制       |
| `cache`     | 查询缓存     |

---

## Where 条件查询

### 表达式查询

```php
// 等于
Db::name('user')->where('id', 1)->select();
Db::name('user')->where('id', '=', 1)->select();

// 不等于
Db::name('user')->where('id', '<>', 1)->select();

// 大于/小于
Db::name('user')->where('id', '>', 100)->select();
Db::name('user')->where('id', '<=', 100)->select();

// LIKE
Db::name('user')->where('name', 'like', 'think%')->select();
Db::name('user')->whereLike('name', 'think%')->select();

// IN
Db::name('user')->where('id', 'in', [1, 2, 3])->select();
Db::name('user')->whereIn('id', [1, 2, 3])->select();

// BETWEEN
Db::name('user')->where('id', 'between', [1, 10])->select();
Db::name('user')->whereBetween('id', [1, 10])->select();

// NULL
Db::name('user')->where('name', 'null')->select();
Db::name('user')->whereNull('name')->select();

// EXP 表达式
Db::name('user')->whereExp('id', '> score')->select();
```

### 时间查询

```php
// 大于某个时间
Db::name('user')->whereTime('create_time', '>', '2023-01-01')->select();

// 时间区间
Db::name('user')->whereBetweenTime('create_time', '2023-01-01', '2023-12-31')->select();

// 快捷查询
Db::name('user')->whereYear('create_time')->select();     // 今年
Db::name('user')->whereMonth('create_time')->select();    // 本月
Db::name('user')->whereDay('create_time')->select();      // 今天
Db::name('user')->whereWeek('create_time')->select();     // 本周
```

### 数组条件

```php
// 多条件 AND
Db::name('user')->where([
    ['status', '=', 1],
    ['name', 'like', 'think%'],
])->select();

// 关联数组 (等于)
Db::name('user')->where(['status' => 1, 'type' => 2])->select();
```

---

## 聚合查询

```php
// 统计数量
Db::name('user')->count();
Db::name('user')->where('status', 1)->count();

// 最大值/最小值
Db::name('user')->max('score');
Db::name('user')->min('score');

// 平均值/求和
Db::name('user')->avg('score');
Db::name('user')->sum('score');
```

---

## 事务处理

### 自动事务

```php
Db::transaction(function () {
    Db::name('user')->where('id', 1)->update(['status' => 1]);
    Db::name('user_log')->insert(['user_id' => 1, 'action' => 'update']);
});
```

### 手动事务

```php
Db::startTrans();
try {
    Db::name('user')->where('id', 1)->update(['status' => 1]);
    Db::name('user_log')->insert(['user_id' => 1, 'action' => 'update']);
    Db::commit();
} catch (\Exception $e) {
    Db::rollback();
    throw $e;
}
```

---

## 模型定义

### 基础模型

```php
<?php
namespace app\model;

use think\Model;

class User extends Model
{
    // 指定表名 (默认使用类名小写)
    protected $table = 'think_user';

    // 指定主键
    protected $pk = 'user_id';

    // 指定数据库连接
    protected $connection = 'mysql';

    // 开启自动时间戳
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';
}
```

### 模型 CRUD

```php
// 查询
$user = User::find(1);
$users = User::where('status', 1)->select();

// 新增
$user = new User;
$user->name = 'thinkphp';
$user->save();

// 或使用 create
$user = User::create(['name' => 'thinkphp', 'email' => 'test@qq.com']);

// 更新
$user = User::find(1);
$user->name = 'new name';
$user->save();

// 静态更新
User::update(['name' => 'thinkphp'], ['id' => 1]);

// 删除
$user = User::find(1);
$user->delete();

// 静态删除
User::destroy(1);
User::destroy([1, 2, 3]);
User::destroy(function($query) {
    $query->where('status', 0);
});
```

---

## 获取器

获取器用于在获取数据时自动进行处理：

```php
<?php
namespace app\model;

use think\Model;

class User extends Model
{
    // 状态值转换
    public function getStatusAttr($value)
    {
        $status = [-1 => '删除', 0 => '禁用', 1 => '正常', 2 => '待审核'];
        return $status[$value] ?? '未知';
    }

    // 虚拟字段 (组合字段)
    public function getFullNameAttr($value, $data)
    {
        return $data['first_name'] . ' ' . $data['last_name'];
    }
}

// 使用
$user = User::find(1);
echo $user->status;     // 输出 "正常" 而不是 1
echo $user->full_name;  // 输出组合后的名字
```

### 动态获取器

```php
User::withAttr('name', function($value, $data) {
    return strtolower($value);
})->select();
```

### 获取原始数据

```php
$user = User::find(1);
echo $user->getData('status');  // 获取原始值
dump($user->getData());         // 获取全部原始数据
```

---

## 修改器

修改器用于在写入数据时自动进行处理：

```php
<?php
namespace app\model;

use think\Model;

class User extends Model
{
    // 自动转小写
    public function setNameAttr($value)
    {
        return strtolower($value);
    }

    // 密码加密
    public function setPasswordAttr($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }
}

// 使用
$user = new User;
$user->name = 'THINKPHP';  // 实际保存 "thinkphp"
$user->password = '123456'; // 自动加密
$user->save();
```

---

## 类型转换

```php
<?php
namespace app\model;

use think\Model;

class User extends Model
{
    protected $type = [
        'status'   => 'integer',
        'score'    => 'float',
        'birthday' => 'datetime',
        'info'     => 'array',
        'config'   => 'json',
    ];
}
```

支持的类型：

| 类型        | 说明                           |
| ----------- | ------------------------------ |
| `integer`   | 整型                           |
| `float`     | 浮点型                         |
| `boolean`   | 布尔型                         |
| `array`     | 数组 (自动 json_encode/decode) |
| `object`    | 对象 (stdClass)                |
| `serialize` | 序列化                         |
| `json`      | JSON                           |
| `timestamp` | 时间戳                         |
| `datetime`  | 日期时间                       |

### PHP8.1+ 枚举类型

```php
<?php
namespace app\enum;

enum Status: int
{
    case Normal = 1;
    case Disabled = 0;
    case Pending = 2;
}

// 模型中使用
class User extends Model
{
    protected $type = [
        'status' => Status::class,
    ];
}

// 写入
$user->status = Status::Normal; // 实际写入 1

// 读取
$user = User::find(1);
dump($user->status); // Status 枚举对象
```

---

## 搜索器

搜索器用于封装查询条件：

```php
<?php
namespace app\model;

use think\Model;

class User extends Model
{
    public function searchNameAttr($query, $value, $data)
    {
        $query->where('name', 'like', $value . '%');
    }

    public function searchCreateTimeAttr($query, $value, $data)
    {
        $query->whereBetweenTime('create_time', $value[0], $value[1]);
    }

    public function searchStatusAttr($query, $value, $data)
    {
        $query->where('status', $value);
        if (isset($data['sort'])) {
            $query->order($data['sort']);
        }
    }
}

// 使用
User::withSearch(['name', 'create_time', 'status'], [
    'name'        => 'think',
    'create_time' => ['2023-01-01', '2023-12-31'],
    'status'      => 1,
    'sort'        => ['create_time' => 'desc'],
])->select();
```

---

## 查询范围

```php
<?php
namespace app\model;

use think\Model;

class User extends Model
{
    // 定义查询范围
    public function scopeActive($query)
    {
        $query->where('status', 1)->where('delete_time', null);
    }

    public function scopeRecent($query, $days = 7)
    {
        $query->whereTime('create_time', '>=', "-{$days} days");
    }
}

// 使用
User::scope('active')->select();
User::scope('recent', 30)->select();
User::active()->recent(7)->select();

// 全局查询范围
class User extends Model
{
    protected $globalScope = ['status'];

    public function scopeStatus($query)
    {
        $query->where('status', 1);
    }
}

// 关闭全局查询范围
User::withoutGlobalScope()->select();
User::withoutGlobalScope(['status'])->select();
```

---

## 软删除

```php
<?php
namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class User extends Model
{
    use SoftDelete;

    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0; // 默认值 (可选)
}

// 软删除
$user = User::find(1);
$user->delete(); // delete_time 字段会被设置

// 真实删除
$user->force()->delete();
User::destroy(1, true);

// 查询包含软删除数据
User::withTrashed()->select();

// 只查询软删除数据
User::onlyTrashed()->select();

// 恢复数据
$user = User::onlyTrashed()->find(1);
$user->restore();
```

---

## 只读字段

```php
<?php
namespace app\model;

use think\Model;

class User extends Model
{
    // 这些字段一旦写入不允许更改
    protected $readonly = ['name', 'email'];
}

// 动态设置
$user->readonly(['name', 'email'])->save();
```

---

## JSON 字段

```php
<?php
namespace app\model;

use think\Model;

class User extends Model
{
    // 设置 JSON 类型字段
    protected $json = ['info'];

    // 设置 JSON 字段类型
    protected $jsonType = [
        'info->user_id' => 'int',
    ];

    // JSON 返回数组而不是对象
    protected $jsonAssoc = true;
}

// 写入
$user = new User;
$user->info = ['email' => 'test@qq.com', 'nickname' => '流年'];
$user->save();

// 查询
$user = User::find(1);
echo $user->info->email;
echo $user->info->nickname;

// 条件查询
User::where('info->nickname', '流年')->find();
User::whereJsonContains('info', 'thinkphp')->find();

// 更新
$user->info->email = 'new@qq.com';
$user->save();
```

---

## 模型事件

### 事件列表

| 事件            | 描述   | 方法名            |
| --------------- | ------ | ----------------- |
| `AfterRead`     | 查询后 | `onAfterRead`     |
| `BeforeInsert`  | 新增前 | `onBeforeInsert`  |
| `AfterInsert`   | 新增后 | `onAfterInsert`   |
| `BeforeUpdate`  | 更新前 | `onBeforeUpdate`  |
| `AfterUpdate`   | 更新后 | `onAfterUpdate`   |
| `BeforeWrite`   | 写入前 | `onBeforeWrite`   |
| `AfterWrite`    | 写入后 | `onAfterWrite`    |
| `BeforeDelete`  | 删除前 | `onBeforeDelete`  |
| `AfterDelete`   | 删除后 | `onAfterDelete`   |
| `BeforeRestore` | 恢复前 | `onBeforeRestore` |
| `AfterRestore`  | 恢复后 | `onAfterRestore`  |

### 定义事件

```php
<?php
namespace app\model;

use think\Model;

class User extends Model
{
    public static function onBeforeUpdate($user)
    {
        if ($user->name == 'admin') {
            return false; // 阻止更新
        }
    }

    public static function onAfterDelete($user)
    {
        // 删除关联数据
        Profile::destroy($user->id);
    }
}
```

### 模型观察者

```php
<?php
namespace app\observer;

use app\model\User;

class UserObserver
{
    public function onBeforeUpdate(User $user)
    {
        // 更新前处理
    }

    public function onAfterDelete(User $user)
    {
        // 删除后处理
    }
}

// 模型中设置观察者
class User extends Model
{
    protected $eventObserver = UserObserver::class;
}
```

---

## 模型关联

### 关联类型

| 方法             | 关联类型      |
| ---------------- | ------------- |
| `hasOne`         | 一对一        |
| `belongsTo`      | 一对一 (反向) |
| `hasMany`        | 一对多        |
| `belongsToMany`  | 多对多        |
| `hasOneThrough`  | 远程一对一    |
| `hasManyThrough` | 远程一对多    |
| `morphOne`       | 多态一对一    |
| `morphMany`      | 多态一对多    |
| `morphTo`        | 多态          |

### 一对一 (hasOne)

```php
<?php
namespace app\model;

use think\Model;

class User extends Model
{
    public function profile()
    {
        return $this->hasOne(Profile::class);
        // hasOne('关联模型', '外键', '主键')
        // 外键默认: user_id (当前模型名_id)
    }
}

// 使用
$user = User::find(1);
echo $user->profile->email;
```

### 一对一反向 (belongsTo)

```php
<?php
namespace app\model;

use think\Model;

class Profile extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
        // belongsTo('关联模型', '外键', '关联主键')
    }
}
```

### 一对多 (hasMany)

```php
<?php
namespace app\model;

use think\Model;

class Article extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

// 使用
$article = Article::find(1);
dump($article->comments);
dump($article->comments()->where('status', 1)->select());
```

### 多对多 (belongsToMany)

```php
<?php
namespace app\model;

use think\Model;

class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
        // belongsToMany('关联模型', '中间表', '外键', '关联键')
    }
}

// 使用
$user = User::find(1);
foreach ($user->roles as $role) {
    echo $role->name;
    dump($role->pivot); // 中间表数据
}

// 关联操作
$user->roles()->attach(1);  // 添加关联
$user->roles()->attach(1, ['remark' => 'test']); // 带额外数据
$user->roles()->detach([1, 2, 3]); // 删除关联
```

### 预载入查询 (解决 N+1 问题)

```php
// 预载入
$users = User::with('profile')->select();
$users = User::with(['profile', 'roles'])->select();

// 带条件的预载入
$users = User::with(['comments' => function($query) {
    $query->where('status', 1)->field('id,user_id,content');
}])->select();

// 嵌套预载入
$users = User::with(['profile.phone'])->select();

// 一对一使用 JOIN 方式
$users = User::withJoin('profile')->select();

// 延迟预载入
$users = User::select();
$users->load(['profile']);
```

### 关联统计

```php
// 统计数量
$users = User::withCount('articles')->select();
foreach ($users as $user) {
    echo $user->articles_count;
}

// 其他统计
$users = User::withSum('orders', 'amount')->select();
$users = User::withMax('scores', 'score')->select();
$users = User::withMin('scores', 'score')->select();
$users = User::withAvg('scores', 'score')->select();
```

### 根据关联条件查询

```php
// 查询有评论的文章
$articles = Article::has('comments')->select();
$articles = Article::has('comments', '>', 3)->select();

// 查询符合关联条件的数据
$articles = Article::hasWhere('comments', ['status' => 1])->select();
$articles = Article::hasWhere('comments', function($query) {
    $query->where('status', 1);
})->select();
```

### 关联写入

```php
// 一对一
$user = User::find(1);
$user->profile()->save(['email' => 'test@qq.com']);

// 一对多
$article = Article::find(1);
$article->comments()->save(['content' => 'test']);
$article->comments()->saveAll([
    ['content' => 'comment1'],
    ['content' => 'comment2'],
]);

// 关联自动写入
$blog = new Blog;
$blog->name = 'thinkphp';
$blog->content = new Content;
$blog->content->data = '内容';
$blog->together(['content'])->save();

// 关联删除
$blog = Blog::with('content')->find(1);
$blog->together(['content'])->delete();
```

---

## 模型输出

### 隐藏/显示/追加字段

```php
$user = User::find(1);

// 隐藏字段
$user->hidden(['password', 'create_time'])->toArray();

// 只显示指定字段
$user->visible(['id', 'name', 'email'])->toArray();

// 追加虚拟字段 (需有获取器)
$user->append(['status_text'])->toArray();

// JSON 输出
echo $user->toJson();
echo json_encode($user);
```

### 关联字段处理

```php
$users = User::with('profile')->select();

// 隐藏关联字段
$users->hidden(['profile.email'])->toArray();
$users->hidden(['profile' => ['address', 'phone']])->toArray();

// 显示关联字段
$users->visible(['profile' => ['email', 'phone']])->toArray();

// 追加关联字段
$users->append(['profile.status_text'])->toArray();
```

---

## 查询缓存

```php
// 缓存查询结果
Db::name('user')->cache(true)->find(1);

// 指定缓存时间 (60秒)
Db::name('user')->cache(60)->find(1);

// 指定缓存标识
Db::name('user')->cache('user_1', 60)->find(1);

// 带标签的缓存
Db::name('user')->cache('user_1', 60, 'user_tag')->find(1);

// 更新/删除时自动清理缓存
Db::name('user')->cache('user_data')->select();
Db::name('user')->cache('user_data')->update(['id' => 1, 'name' => 'new']);
```

---

## 字段缓存

```php
// 配置开启
'fields_cache'      => true,
'schema_cache_path' => 'path/to/cache',

// ThinkPHP 中使用命令生成
php think optimize:schema
php think optimize:schema --table user
```

---

## SQL 监听

```php
// 设置日志对象 (需遵循 PSR-3)
Db::setLog($log);

// 添加 SQL 监听
Db::listen(function($sql, $runtime, $master) {
    // $sql: SQL 语句
    // $runtime: 运行时间 (秒)
    // $master: 主从标记 (布尔值或 null)
    echo $sql . ' [' . $runtime . 's]';
});
```

---

## 最佳实践

### 查询最佳实践

```php
// ✅ 使用模型查询
$user = User::where('status', 1)->find();

// ✅ 大数据使用游标
foreach (User::cursor() as $user) { }

// ✅ 预载入解决 N+1
$users = User::with(['profile', 'roles'])->select();

// ❌ 避免在循环中查询关联
foreach ($users as $user) {
    $user->profile; // 每次都查询数据库
}
```

### 写入最佳实践

```php
// ✅ 使用模型事件处理关联逻辑
public static function onAfterDelete($user)
{
    Profile::destroy($user->id);
}

// ✅ 批量操作使用事务
Db::transaction(function() use ($data) {
    foreach ($data as $item) {
        User::create($item);
    }
});

// ❌ 避免在一个模型实例中多次 save
$user = new User;
$user->save($data1);
$user->save($data2); // 可能导致问题
```

### 性能优化

```php
// ✅ 开启字段缓存
'fields_cache' => true,

// ✅ 使用查询缓存
User::cache(3600)->where('status', 1)->select();

// ✅ 只查询需要的字段
User::field('id, name, email')->select();

// ✅ 使用索引
Db::name('user')->force('idx_status')->where('status', 1)->select();
```

---

## 常用代码片段

### 分页查询

```php
// Db 查询
$list = Db::name('user')->where('status', 1)->paginate(15);

// 模型查询
$list = User::where('status', 1)->paginate(15);

// 自定义分页参数
$list = User::paginate([
    'list_rows' => 15,
    'page'      => 1,
]);
```

### 事务嵌套

```php
Db::transaction(function () {
    Db::name('user')->insert(['name' => 'user1']);

    Db::transaction(function () {
        Db::name('user')->insert(['name' => 'user2']);
    });
});
```

### 原生 SQL

```php
// 查询
Db::query('SELECT * FROM think_user WHERE id = ?', [1]);

// 写入
Db::execute('INSERT INTO think_user (name) VALUES (?)', ['thinkphp']);
```

---

## 参考资料

| 文档                | 链接                                       |
| ------------------- | ------------------------------------------ |
| ThinkORM 官方文档   | https://doc.thinkphp.cn/@think-orm         |
| ThinkPHP 数据库章节 | https://doc.thinkphp.cn/v8_1/database.html |
| GitHub 仓库         | https://github.com/top-think/think-orm     |
