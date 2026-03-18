<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\model\cms;

use plugin\saiadmin\app\model\system\SystemUser;
use plugin\saiadmin\basic\think\BaseModel;

/**
 * 文章列表模型
 */
class Article extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 数据库表名称
     * @var string
     */
    protected $table = 'sa_article';

    /**
     * 文章标题 搜索
     */
    public function searchTitleAttr($query, $value)
    {
        $query->where('title', 'like', '%' . $value . '%');
    }

    /**
     * 关联模型 ArticleCategory
     */
    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id', 'id');
    }

    /**
     * 关联模型 SystemUser
     */
    public function publisher()
    {
        return $this->belongsTo(SystemUser::class, 'created_by', 'id')->bind(['username', 'avatar']);
    }

}
