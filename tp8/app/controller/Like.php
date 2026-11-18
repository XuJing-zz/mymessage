<?php
namespace app\controller;

use think\Model;
use think\Request;

// —— 本文件内用的模型（名字加 Lk 前缀，避免和别的控制器文件里的类重名）——
class LkModel        extends Model { protected $name = 'article_likes'; }
class LkArticleModel extends Model { protected $name = 'article'; }

class Like extends ApiBase
{
    // 点赞
    public function like(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;// 必须是 POST 请求

        $articleId = (int)$request->post('article_id', 0);// 文章ID
        if ($articleId <= 0) return $this->fail('缺少 article_id');// 文章ID不能为空

        // ① 文章必须存在且已审核
        $article = LkArticleModel::where('id', $articleId)->where('status', 1)->find();
        if (!$article) return $this->fail('文章不存在或未审核', 404, 404);

        // ② 不能重复点赞（否则会撞数据库唯一索引，抛 500）
        $exists = LkModel::where('article_id', $articleId)
            ->where('user_id', (int)$user['id'])
            ->find();
        if ($exists) return $this->fail('你已经点过赞了', 1004);

        LkModel::create([
            'article_id' => $articleId,
            'user_id'    => (int)$user['id'],
        ]);// 点赞

        $count = (int)LkModel::where('article_id', $articleId)->count();

        return $this->ok(['liked' => true, 'like_count' => $count], '点赞成功');
    }

    // 取消点赞
    public function unlike(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $articleId = (int)$request->post('article_id', 0);
        if ($articleId <= 0) return $this->fail('缺少 article_id');

        LkModel::where('article_id', $articleId)
            ->where('user_id', (int)$user['id'])
            ->delete();

        $count = (int)LkModel::where('article_id', $articleId)->count();

        return $this->ok(['liked' => false, 'like_count' => $count], '已取消点赞');
    }
}
