<?php

namespace plugin\saiuser\app\api\controller\cms;

use plugin\saiadmin\basic\OpenController;
use plugin\saiuser\app\api\logic\cms\ArticleLogic;
use support\Request;
use support\Response;

/**
 * 新闻中心控制器
 */
class ArticleController extends OpenController
{
    protected $logic;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->logic = new ArticleLogic();
        parent::__construct();
    }

    /**
     * 轮播列表
     * @param Request $request
     * @return Response
     */
    public function banner(Request $request): Response
    {
        $data = $this->logic->bannerList();
        return $this->success($data);
    }

    /**
     * 分类列表
     * @param Request $request
     * @return Response
     */
    public function category(Request $request): Response
    {
        $data = $this->logic->categoryList();
        return $this->success($data);
    }

    /**
     * 文章列表
     * @param Request $request
     * @return Response
     */
    public function articles(Request $request): Response
    {
        $category_id = $request->get('category_id', '');
        $data = $this->logic->articleList($category_id);
        return $this->success($data);
    }

    /**
     * 热门文章
     * @param Request $request
     * @return Response
     */
    public function hotArticles(Request $request): Response
    {
        $data = $this->logic->hotArticleList();
        return $this->success($data);
    }

    /**
     * 随机文章
     * @param Request $request
     * @return Response
     */
    public function randomArticles(Request $request): Response
    {
        $limit = $request->get('limit', 3);
        $data = $this->logic->randomArticleList($limit);
        return $this->success($data);
    }

    /**
     * 文章详情
     * 可以使用 webman 的限流器
     * @param Request $request
     * @return Response
     */
    public function article(Request $request): Response
    {
        $id = $request->get('id', '');
        if (empty($id)) {
            return $this->fail('参数错误');
        }
        $data = $this->logic->getArticle($id);
        return $this->success($data);
    }

    /**
     * 查询指定id相邻的文章
     * @param Request $request
     * @return Response
     */
    public function articleAround(Request $request): Response
    {
        $id = $request->get('id', '');
        if (empty($id)) {
            return $this->fail('参数错误');
        }
        $data = $this->logic->articleAround($id);
        return $this->success($data);
    }
}
