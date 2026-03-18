<?php
// +----------------------------------------------------------------------
// | saiadmin [ saiadmin快速开发框架 ]
// +----------------------------------------------------------------------
// | Author: your name
// +----------------------------------------------------------------------
namespace plugin\saiuser\app\api\logic\cms;

use plugin\saiadmin\basic\think\BaseLogic;
use plugin\saiuser\app\model\cms\ArticleBanner;
use plugin\saiuser\app\model\cms\ArticleCategory;
use plugin\saiuser\app\model\cms\Article;

/**
 * 新闻中心逻辑层
 */
class ArticleLogic extends BaseLogic
{
    /**
     * 轮播图列表
     * @return array
     */
    public function bannerList()
    {
        $data = ArticleBanner::where('status', 1)->select()->toArray();
        return $data;
    }

    /**
     * 分类列表
     * @return array
     */
    public function categoryList()
    {
        $data = ArticleCategory::where('status', 1)->select()->toArray();
        return $data;
    }

    /**
     * 文章列表
     * @param mixed $category_id
     * @return array
     */
    public function articleList($category_id)
    {
        $this->setOrderField('create_time');
        $this->setOrderType('desc');
        $query = Article::where('status', 1)->order('sort', 'desc');
        if (!empty($category_id)) {
            $query = $query->where('category_id', $category_id);
        }
        return $this->getList($query);
    }

    /**
     * 热门文章
     * @return array
     */
    public function hotArticleList()
    {
        $data = Article::where('status', 1)->order('views', 'desc')->limit(5)->select()->toArray();
        return $data;
    }

    /**
     * 随机文章
     * @return array
     */
    public function randomArticleList($limit)
    {
        $data = Article::where('status', 1)->orderRaw('RAND()')->limit($limit)->select()->toArray();
        return $data;
    }

    /**
     * 文章详情
     * @param mixed $id
     * @return array
     */
    public function getArticle($id)
    {
        $data = Article::with('publisher')->where('id', $id)->where('status', 1)->findOrEmpty();
        if ($data->isEmpty()) {
            return [];
        }
        $data->views = $data->views + 1;
        $data->save();
        return $data->toArray();
    }

    /**
     * 查询指定id相邻的文章
     * @param mixed $id
     * @return array
     */
    public function articleAround($id)
    {
        $model = Article::where('id', $id)->where('status', 1)->findOrEmpty();
        if ($model->isEmpty()) {
            return [];
        }
        $query = Article::where('status', 1)->where('category_id', $model->category_id);

        // 查询上一篇文章（id小于当前id，取最大的一条）
        $prev = (clone $query)->where('id', '<', $id)->order('id', 'desc')->find();

        // 查询下一篇文章（id大于当前id，取最小的一条）
        $next = (clone $query)->where('id', '>', $id)->order('id', 'asc')->find();

        return [
            'prev' => $prev ? $prev->toArray() : null,
            'next' => $next ? $next->toArray() : null,
        ];
    }

}
