<?php

namespace plugin\saiuser\app\api\controller\common;

use plugin\saiadmin\basic\OpenController;
use plugin\saiadmin\utils\Captcha;
use plugin\saiuser\app\api\logic\common\IndexLogic;
use plugin\saiuser\app\admin\logic\member\MemberLogic;
use support\Request;
use support\Response;

class IndexController extends OpenController
{

    protected $logic;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->logic = new IndexLogic();
        parent::__construct();
    }

    /**
     * 协议信息
     * @param Request $request
     * @return Response
     */
    public function protocol(Request $request): Response
    {
        $type = $request->input('type', '');
        if (empty($type)) {
            return $this->fail('参数错误');
        }
        $data = $this->logic->protocol($type);
        return $this->success($data);
    }

    /**
     * 站点信息
     * @param Request $request
     * @return Response
     */
    public function siteInfo(Request $request): Response
    {
        $data = $this->logic->siteInfo();
        return $this->success($data);
    }

    /**
     * 获取字典数据
     * @param Request $request
     * @return Response
     */
    public function dictData(Request $request): Response
    {
        $code = $request->get('code', '');
        if (empty($code)) {
            return $this->fail('参数错误');
        }
        return $this->success(dictDataList($code));
    }

    /**
     * 获取验证码
     */
    public function captcha(): Response
    {
        $captcha = new Captcha();
        $result = $captcha->imageCaptcha();
        if ($result['result'] !== 1) {
            return $this->fail($result['message']);
        }
        return $this->success($result);
    }

    /**
     * 发送验证码
     */
    public function sendCode(Request $request): Response
    {
        $mobile = $request->post('mobile', '');
        if (empty($mobile) || strlen($mobile) != 11) {
            return $this->fail('请输入正确格式的手机号码');
        }
        $logic = new MemberLogic();
        $request = $logic->sendCode($mobile);
        if ($request) {
            return $this->success([], '发送成功');
        } else {
            return $this->fail('发送失败');
        }
    }

    /**
     * 注册账户-发送邮件
     */
    public function sendEmail(Request $request): Response
    {
        $email = $request->post('email', '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('请输入正确的邮箱格式');
        }
        $result = $this->logic->emailSend($email, 1);
        return $this->success($result);
    }

    /**
     * 找回密码-发送邮件
     */
    public function sendForgotEmail(Request $request): Response
    {
        $email = $request->post('email', '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('请输入正确的邮箱格式');
        }
        $code = $request->post('code', '');
        $uuid = $request->post('uuid', '');
        $captcha = new Captcha();
        if (!$captcha->checkCaptcha($uuid, $code)) {
            return $this->fail('验证码错误');
        }
        $result = $this->logic->emailSend($email, 2);
        return $this->success($result);
    }

    /**
     * 邮件重置密码
     */
    public function emailReset(Request $request): Response
    {
        $data = $request->post();
        $logic = new MemberLogic();
        $result = $logic->emailReset($data);
        return $this->success($result);
    }

    /**
     * 账户登录
     * @param Request $request
     * @return Response
     */
    public function accountLogin(Request $request): Response
    {
        $username = $request->post('username', '');
        $password = $request->post('password', '');
        $type = $request->post('type', 1);

        $code = $request->post('code', '');
        $uuid = $request->post('uuid', '');
        $captcha = new Captcha();
        if (!$captcha->checkCaptcha($uuid, $code)) {
            return $this->fail('验证码错误');
        }
        $logic = new MemberLogic();
        $data = $logic->login($username, $password, $type);
        return $this->success($data);
    }

    /**
     * 账户注册
     * @param Request $request
     * @return Response
     */
    public function accountRegister(Request $request): Response
    {
        $code = $request->post('code', '');
        $uuid = $request->post('uuid', '');
        $captcha = new Captcha();
        if (!$captcha->checkCaptcha($uuid, $code)) {
            return $this->fail('验证码错误');
        }
        $data = $request->post();
        $logic = new MemberLogic();
        $result = $logic->emailReg($data);
        return $this->success($result);
    }

    /**
     * 手机号码登录
     */
    public function phoneLogin(Request $request): Response
    {
        $data = $request->more([
            ['mobile', ''],
            ['mobile_code', ''],
        ]);
        $code = $request->post('code', '');
        $uuid = $request->post('uuid', '');
        $captcha = new Captcha();
        if (!$captcha->checkCaptcha($uuid, $code)) {
            return $this->fail('验证码错误');
        }
        $logic = new MemberLogic();
        $data = $logic->phoneLogin($data);
        return $this->success($data);
    }

    /**
     * 小程序登录
     */
    public function mnpLogin(Request $request): Response
    {
        $data = $this->logic->mnpLogin($request->post());
        return $this->success($data);
    }

    /**
     * 微信公众号
     */
    public function wechat(Request $request): Response
    {
        return $this->logic->wechat($request);
    }

    /**
     * 公众号微信 oauth
     * @param Request $request
     * @return Response
     */
    public function wechatOauth(Request $request): Response
    {
        $url = $request->post('url', '');
        if (empty($url)) {
            return $this->fail('参数错误');
        }
        $data = $this->logic->wechatOauth($url);
        return $this->success($data);
    }

    /**
     * 公众号登录
     * @param Request $request
     * @return Response
     */
    public function wechatLogin(Request $request): Response
    {
        $data = $this->logic->wechatLogin($request->post());
        return $this->success($data);
    }

    /**
     * 企业微信 oauth
     * @param Request $request
     * @return Response
     */
    public function workOauth(Request $request): Response
    {
        $url = $request->post('url', '');
        if (empty($url)) {
            return $this->fail('参数错误');
        }
        $data = $this->logic->workOauth($url);
        return $this->success($data);
    }

    /**
     * 企业微信 登录
     * @param Request $request
     * @return Response
     */
    public function workLogin(Request $request): Response
    {
        $data = $this->logic->workLogin($request->post());
        return $this->success($data);
    }

    /**
     * 钉钉 获取CropId
     * @param Request $request
     * @return Response
     */
    public function dingtalkCropId(Request $request): Response
    {
        $data = $this->logic->dingtalkCropId();
        return $this->success($data);
    }

    /**
     * 钉钉 登录
     * @param Request $request
     * @return Response
     */
    public function dingtalkLogin(Request $request): Response
    {
        $data = $this->logic->dingtalkLogin($request->post());
        return $this->success($data);
    }
}
