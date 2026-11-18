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
