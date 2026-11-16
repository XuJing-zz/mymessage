<?php
namespace app\controller;

use app\model\Article;
use app\model\ArticleLike;
use think\Request;

class Like extends ApiBase
{
    public function like(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $articleId = (int)$request->post('article_id', 0);
        if ($articleId <= 0) return $this->fail('缺少 article_id');

        if (!Article::findPublished($articleId)) {
            return $this->fail('文章不存在或未审核', 404, 404);
        }

        if (ArticleLike::isLiked($articleId, (int)$user['id'])) {
            return $this->fail('你已经点过赞了', 1004);
        }

        ArticleLike::add($articleId, (int)$user['id']);

        return $this->ok(['liked' => true, 'like_count' => ArticleLike::countByArticle($articleId)], '点赞成功');
    }

    public function unlike(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $articleId = (int)$request->post('article_id', 0);
        if ($articleId <= 0) return $this->fail('缺少 article_id');

        ArticleLike::remove($articleId, (int)$user['id']);

        return $this->ok(['liked' => false, 'like_count' => ArticleLike::countByArticle($articleId)], '已取消点赞');
    }
}