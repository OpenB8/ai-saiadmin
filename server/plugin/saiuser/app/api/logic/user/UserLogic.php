<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\api\logic\user;

use plugin\saiadmin\basic\think\BaseLogic;
use plugin\saiadmin\exception\ApiException;
use plugin\saiadmin\app\logic\system\SystemAttachmentLogic;
use plugin\saiuser\app\admin\logic\member\MemberLogic;
use plugin\saiuser\app\admin\logic\member\MemberLoginLogLogic;
use plugin\saiuser\app\admin\logic\member\MemberPointsLogLogic;
use plugin\saiuser\app\cache\MemberInfoCache;
use plugin\saiuser\app\model\member\Member;

/**
 * 用户逻辑层
 */
class UserLogic extends BaseLogic
{

    /**
     * 上传文件/图片
     * @param string $type image|file
     * @param bool $isLocal 是否本地存储
     * @return array
     */
    public function upload(string $type, bool $isLocal = false): array
    {
        $logic = new SystemAttachmentLogic();
        return $logic->uploadBase($type, $isLocal);
    }

    /**
     * 修改资料
     * @param int $memberId
     * @param array $data
     * @return bool
     */
    public function updateProfile(int $memberId, array $data): bool
    {
        $logic = new MemberLogic();
        $logic->where('id', $memberId)->update([
            'nickname' => $data['nickname'] ?? '',
            'avatar' => $data['avatar'] ?? '',
        ]);
        MemberInfoCache::clearUserInfo($memberId);
        return true;
    }

    /**
     * 修改密码
     * @param int $memberId
     * @param array $data
     * @return bool
     */
    public function updatePassword(int $memberId, array $data): bool
    {
        $logic = new MemberLogic();
        $model = $logic->where('id', $memberId)->findOrEmpty();
        if ($model->isEmpty()) {
            throw new ApiException('用户查找失败');
        }
        if (!password_verify($data['old_password'], $model->password_hash)) {
            throw new ApiException('原密码错误');
        }

        $model->save([
            'password_hash' => password_hash(trim($data['password']), PASSWORD_DEFAULT),
        ]);
        MemberInfoCache::clearUserInfo($memberId);
        return true;
    }

    /**
     * 登录日志
     * @param int $memberId
     * @param array $params
     * @return array
     */
    public function getLoginLogs(int $memberId, array $params): array
    {
        $logic = new MemberLoginLogLogic();
        $params['member_id'] = $memberId;
        $logic->setOrderType('desc');
        $query = $logic->search($params);
        $query->with(['platform']);
        return $logic->getList($query);
    }

    /**
     * 获取当前积分
     * @param int $memberId
     * @return int
     */
    public function getIntegral(int $memberId): int
    {
        $member = Member::where('id', $memberId)->findOrEmpty();
        if ($member->isEmpty()) {
            throw new ApiException('用户信息不存在');
        }
        return $member->points_balance ?? 0;
    }

    /**
     * 积分明细
     * @param int $memberId
     * @param array $params
     * @return array
     */
    public function getPointsLogs(int $memberId, array $params): array
    {
        $logic = new MemberPointsLogLogic();
        $params['member_id'] = $memberId;
        $logic->setOrderType('desc');
        $query = $logic->search($params);
        return $logic->getList($query);
    }

}
