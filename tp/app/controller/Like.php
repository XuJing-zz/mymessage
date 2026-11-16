<?php
namespace app\controller;

use think\facade\Db;
use think\Request;

class Like extends ApiBase
{
    // POST api/like
    public function like(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $articleId = (int)$request->post('article_id', 0);
        if ($articleId <= 0) return $this->fail('缺少 article_id');

        $article = Db::name('article')->where('id', $articleId)->where('status', 1)->find();
        if (!$article) return $this->fail('文章不存在或未审核', 404, 404);

        $exists = Db::name('article_likes')
            ->where('article_id', $articleId)
            ->where('user_id', (int)$user['id'])
            ->find();

        if ($exists) return $this->fail('你已经点过赞了', 1004);

        Db::name('article_likes')->insert([
            'article_id' => $articleId,
            'user_id'    => (int)$user['id'],
        ]);

        $count = (int)Db::name('article_likes')->where('article_id', $articleId)->count();

        return $this->ok(['liked' => true, 'like_count' => $count], '点赞成功');
    }

    // POST api/unlike
    public function unlike(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $articleId = (int)$request->post('article_id', 0);
        if ($articleId <= 0) return $this->fail('缺少 article_id');

        Db::name('article_likes')
            ->where('article_id', $articleId)
            ->where('user_id', (int)$user['id'])
            ->delete();

        $count = (int)Db::name('article_likes')->where('article_id', $articleId)->count();

        return $this->ok(['liked' => false, 'like_count' => $count], '已取消点赞');
    }
}