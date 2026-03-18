<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\admin\logic\member;

use plugin\saiadmin\app\model\system\SystemMail;
use plugin\saiadmin\basic\think\BaseLogic;
use plugin\saiadmin\exception\ApiException;
use plugin\saiadmin\utils\Helper;
use plugin\saiuser\app\model\member\Member;
use plugin\saiuser\app\model\member\MemberLoginLog;
use plugin\saiuser\app\model\member\MemberPointsLog;
use plugin\saiuser\app\model\member\MemberPlatformRel;
use support\think\Db;
use Tinywan\Jwt\JwtToken;
use Webman\Event\Event;

/**
 * 会员信息逻辑层
 */
class MemberLogic extends BaseLogic
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new Member();
    }

    /**
     * 读取数据
     * @param $id
     * @return array
     */
    public function read($id): array
    {
        $model = $this->model->with(['level', 'platform'])->findOrEmpty($id);
        if ($model->isEmpty()) {
            throw new ApiException('用户查找失败');
        }
        $data = $model->toArray();
        $loginLog = MemberLoginLog::with(['platform'])
            ->where('member_id', $id)
            ->order('create_time', 'desc')
            ->limit(10)
            ->select()
            ->toArray();
        $data['login_log'] = $loginLog;
        $pointsLog = MemberPointsLog::where('member_id', $id)
            ->order('create_time', 'desc')
            ->limit(10)
            ->select()
            ->toArray();
        $data['points_log'] = $pointsLog;
        return $data;
    }

    /**
     * 添加会员
     * @param $data
     * @return mixed
     */
    public function add($data): mixed
    {
        $platform_id = 1;
        Db::startTrans();
        try {
            // 账号注册
            $user = Member::create([
                'username' => $data['username'],
                'avatar' => $data['avatar'],
                'password_hash' => password_hash(trim($data['password']), PASSWORD_DEFAULT),
                'email' => $data['email'],
                'member_level_id' => $data['member_level_id'],
                'points_balance' => 0,
                'register_platform_id' => $platform_id,
                'status' => 1,
            ]);

            // 账户关联
            MemberPlatformRel::create([
                'member_id' => $user->id,
                'platform_id' => $platform_id,
                'platform_openid' => $data['email'],
                'is_bind' => 1,
                'bind_time' => date('Y-m-d H:i:s'),
            ]);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * 修改会员
     * @param $id
     * @param $data
     * @return mixed
     */
    public function edit($id, $data): mixed
    {
        $model = $this->model->findOrEmpty($id);
        if ($model->isEmpty()) {
            throw new ApiException('用户查找失败');
        }
        return $model->save([
            'username' => $data['username'],
            'nickname' => $data['nickname'],
            'avatar' => $data['avatar'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'member_level_id' => $data['member_level_id'],
            'status' => $data['status'],
        ]);
    }

    /**
     * 用户登录
     * @param string $username
     * @param string $password
     * @param string $type
     * @return array
     */
    public function login(string $username, string $password, string $type): array
    {
        $platform_id = 1;

        $adminInfo = $this->model->where('username', $username)->findOrEmpty();
        $status = 1;
        $message = '';
        if ($adminInfo->isEmpty()) {
            $message = '账号或密码错误，请重新输入!';
            throw new ApiException($message);
        }
        if (empty($adminInfo->password_hash)) {
            throw new ApiException('该账户未设置密码');
        }
        if ($adminInfo->status === 2) {
            $status = 0;
            $message = '您已被禁止登录!';
        }
        if (!password_verify($password, $adminInfo->password_hash)) {
            $status = 0;
            $message = '账号或密码错误，请重新输入!';
        }
        if ($status === 0) {
            // 登录事件
            Event::emit('member.login', [
                'member_id' => $adminInfo->id,
                'platform_id' => $platform_id,
                'status' => $status,
                'message' => $message
            ]);
            throw new ApiException($message);
        }
        $adminInfo->last_login_time = date('Y-m-d H:i:s');
        $adminInfo->last_login_ip = request()->getRealIp();
        $adminInfo->save();

        $access_exp = config('plugin.saiuser.saithink.access_exp', 24 * 3600);

        $token = JwtToken::generateToken([
            'access_exp' => $access_exp,
            'id' => $adminInfo->id,
            'username' => $adminInfo->username,
            'plat' => 'saiuser',
            'type' => $platform_id,
        ]);

        // 登录日志事件
        Event::emit('member.login', [
            'member_id' => $adminInfo->id,
            'platform_id' => $platform_id,
            'status' => $status,
            'message' => $message
        ]);

        return $token;
    }

    /**
     * 邮箱注册
     * @param $data
     * @return bool
     */
    public function emailReg($data): bool
    {
        $platform_id = 1;

        $user = $this->model
            ->whereOr('email', trim($data['email']))
            ->whereOr('username', trim($data['username']))
            ->findOrEmpty();
        if (!$user->isEmpty()) {
            throw new ApiException('邮箱或者账号已存在');
        }
        $model = SystemMail::where('email', trim($data['email']))->where('status', 'success')->order('create_time', 'desc')->findOrEmpty();
        if ($model->isEmpty()) {
            throw new ApiException('邮箱验证码获取失败，请确认邮件是否正确发送');
        }
        if ($model->code != trim($data['email_code'])) {
            throw new ApiException('验邮箱证码错误或已过期，请填写正确的验证码');
        }
        $interval = 60 * 10;
        if (time() - strtotime($model->update_time) > $interval) {
            throw new ApiException('邮箱验证码错误或已过期，请填写正确的验证码');
        }

        $avatar = config('plugin.saiuser.saithink.avatar') . '/' . trim($data['username']) . '.png';
        Db::startTrans();
        try {
            // 账号注册
            $user = Member::create([
                'username' => trim($data['username']),
                'avatar' => $avatar,
                'password_hash' => password_hash(trim($data['password']), PASSWORD_DEFAULT),
                'email' => trim($data['email']),
                'member_level_id' => 1,
                'points_balance' => 0,
                'register_platform_id' => $platform_id,
                'status' => 1,
            ]);

            // 账户关联
            MemberPlatformRel::create([
                'member_id' => $user->id,
                'platform_id' => $platform_id,
                'platform_openid' => trim($data['email']),
                'is_bind' => 1,
                'bind_time' => date('Y-m-d H:i:s'),
            ]);

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * 重置密码-邮箱重置
     * @param $data
     * @return bool
     */
    public function emailReset($data): bool
    {
        $email = trim($data['email']);
        $email_code = trim($data['email_code']);
        $password = trim($data['password']);

        $user = $this->model->where('email', $email)->findOrEmpty();
        if ($user->isEmpty()) {
            throw new ApiException('用户查找失败，请检查邮箱是否正确');
        }
        $model = SystemMail::where('email', $email)->where('status', 'success')->order('create_time', 'desc')->findOrEmpty();
        if ($model->isEmpty()) {
            throw new ApiException('邮箱验证码获取失败，请确认邮件是否正确发送');
        }
        if ($model->code != $email_code) {
            throw new ApiException('邮箱验证码错误或已过期，请填写正确的验证码');
        }
        $interval = 60 * 10;
        if (time() - strtotime($model->update_time) > $interval) {
            throw new ApiException('邮箱验证码错误或已过期，请填写正确的验证码');
        }
        $user->password_hash = password_hash($password, PASSWORD_DEFAULT);
        return $user->save();
    }

    /**
     * 发送短信
     * @param $mobile
     * @return bool
     */
    public function sendCode($mobile): bool
    {
        if (!class_exists('plugin\saisms\app\admin\logic\SmsRecordLogic')) {
            throw new ApiException('请先安装和配置saisms短信插件');
        }
        $smsLogic = new \plugin\saisms\app\admin\logic\SmsRecordLogic();
        // 短信内容
        $phoneData = [
            'mobile' => $mobile,
            'tag_name' => config('plugin.saiuser.saithink.sms.tag'),
        ];
        $result = $smsLogic->sendCode($phoneData);
        if ($result['status'] === 'success') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 手机号注册
     * @param $data
     * @return array
     */
    public function phoneLogin($data): array
    {
        $platform_id = 4;

        if (!class_exists('plugin\saisms\app\admin\logic\SmsRecordLogic')) {
            throw new ApiException('请先安装和配置saisms短信插件');
        }
        $smsLogic = new \plugin\saisms\app\admin\logic\SmsRecordLogic();
        $model = $smsLogic->where('mobile', $data['mobile'])
            ->where('status', 'success')
            ->order('create_time', 'desc')
            ->findOrEmpty();
        if ($model->isEmpty() || ($model->is_verify == 1) || (strtotime($model->create_time) < time() - 5 * 60)) {
            // 5分钟有效期
            throw new ApiException('验证码错误或已过期');
        }
        if ($model->code != trim($data['mobile_code'])) {
            throw new ApiException('验证码错误或已过期');
        }
        $model->is_verify = 1;
        $model->save();

        $user = $this->model->where('mobile', $data['mobile'])->findOrEmpty();
        if (!$user->isEmpty()) {
            if ($user->status != 1) {
                // 登录日志事件
                Event::emit('member.login', [
                    'member_id' => $user->id,
                    'platform_id' => $platform_id,
                    'status' => 1,
                    'message' => '账号状态异常，禁止登录'
                ]);

                throw new ApiException('您的账号异常，请联系客服。');
            }

            $user->last_login_time = date('Y-m-d H:i:s');
            $user->last_login_ip = request()->getRealIp();
            $user->save();

            $access_exp = config('plugin.saiuser.saithink.access_exp', 24 * 3600);
            $token = JwtToken::generateToken([
                'access_exp' => $access_exp,
                'id' => $user->id,
                'username' => $user->username,
                'plat' => 'saiuser',
                'type' => $platform_id
            ]);

            // 登录日志事件
            Event::emit('member.login', [
                'member_id' => $user->id,
                'platform_id' => $platform_id,
                'status' => 1,
                'message' => ''
            ]);
            return $token;
        }

        // 注册
        $userSn = Member::createUserSn();
        $avatar = config('plugin.saiuser.saithink.avatar') . '/' . $userSn . '.png';

        Db::startTrans();
        try {
            $model = Member::create([
                'username' => 'u' . $userSn,
                'nickname' => '用户' . $userSn,
                'avatar' => $avatar,
                'mobile' => $data['mobile'],
                'member_level_id' => 1,
                'points_balance' => 0,
                'register_platform_id' => $platform_id,
                'status' => 1
            ]);

            MemberPlatformRel::create([
                'member_id' => $model->id,
                'platform_id' => $platform_id,
                'platform_openid' => $data['mobile'],
                'is_bind' => 1,
                'bind_time' => date('Y-m-d H:i:s'),
            ]);

            $access_exp = config('plugin.saiuser.saithink.access_exp');
            $token = JwtToken::generateToken([
                'access_exp' => $access_exp,
                'id' => $model->id,
                'username' => $model->account,
                'plat' => 'saiuser',
                'type' => $platform_id
            ]);

            Db::commit();
            return $token;
        } catch (\Exception $e) {
            Db::rollback();
            throw new ApiException($e->getMessage());
        }
    }

    /**
     * 第三方平台注册登录
     * @param $data
     * @return array
     */
    public function thirdPlatform($data): array
    {
        $platform_id = $data['platform_id'];

        $auth = MemberPlatformRel::where('platform_openid', $data['openid'])->where('platform_id', $platform_id)->findOrEmpty();
        if (!$auth->isEmpty()) {
            $user = Member::where('id', $auth->member_id)->findOrEmpty();
            if ($user->status != 1) {
                throw new ApiException('您的账号异常，请联系客服。');
            }

            $user->last_login_time = date('Y-m-d H:i:s');
            $user->last_login_ip = request()->getRealIp();
            $user->save();

            $access_exp = config('plugin.saiuser.saithink.access_exp', 24 * 3600);
            $token = JwtToken::generateToken([
                'access_exp' => $access_exp,
                'id' => $user->id,
                'username' => $user->username,
                'plat' => 'saiuser',
                'type' => $platform_id
            ]);

            // 登录日志事件
            Event::emit('member.login', [
                'member_id' => $user->id,
                'platform_id' => $platform_id,
                'status' => 1,
                'message' => ''
            ]);
            return $token;
        }

        $userSn = Member::createUserSn();
        $avatar = config('plugin.saiuser.saithink.avatar') . '/' . $userSn . '.png';

        Db::startTrans();
        try {
            $model = Member::create([
                'username' => 'u' . $userSn,
                'nickname' => !empty($data['nickname']) ? $data['nickname'] : '用户' . $userSn,
                'avatar' => !empty($data['avatar']) ? $data['avatar'] : $avatar,
                'member_level_id' => 1,
                'points_balance' => 0,
                'register_platform_id' => $platform_id,
                'status' => 1
            ]);

            MemberPlatformRel::create([
                'member_id' => $model->id,
                'platform_id' => $platform_id,
                'platform_openid' => $data['openid'],
                'is_bind' => 1,
                'bind_time' => date('Y-m-d H:i:s'),
            ]);

            $access_exp = config('plugin.saiuser.saithink.access_exp');
            $token = JwtToken::generateToken([
                'access_exp' => $access_exp,
                'id' => $model->id,
                'username' => $model->account,
                'plat' => 'saiuser',
                'type' => $platform_id
            ]);

            // 登录日志事件
            Event::emit('member.login', [
                'member_id' => $model->id,
                'platform_id' => $platform_id,
                'status' => 1,
                'message' => ''
            ]);

            Db::commit();
            return $token;
        } catch (\Exception $e) {
            Db::rollback();
            throw new ApiException($e->getMessage());
        }
    }
}
