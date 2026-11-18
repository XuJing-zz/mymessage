<?php
namespace app\controller;

use think\Model;
use think\Request;

// —— 本文件内用的模型（名字加 Cmt 前缀，避免和 Article.php 里的 Model 类重名）——
class CmtModel        extends Model { protected $name = 'comment'; }
class CmtArticleModel extends Model { protected $name = 'article'; }

class Comment extends ApiBase
{
    // 评论列表（公开）
    public function index(Request $request)
    {
        $articleId = (int)$request->get('article_id', 0);
        if ($articleId <= 0) return $this->fail('缺少 article_id');

        $rows = CmtModel::alias('c')
            ->join('user u', 'u.id = c.user_id')
            ->where('c.article_id', $articleId)
            ->field('c.id,c.user_id,c.content,c.created_at,u.username')
            ->order('c.id desc')
            ->select()
            ->toArray();

        foreach ($rows as &$r) {
            $r['id']      = (int)$r['id'];
            $r['user_id'] = (int)$r['user_id'];
        }
        unset($r);

        return $this->ok(['list' => $rows, 'total' => count($rows)]);
    }

    // 发评论（需登录，文章必须已审核）
    public function add(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $articleId = (int)$request->post('article_id', 0);
        $content   = trim((string)$request->post('content', ''));

        if ($articleId <= 0)                    return $this->fail('缺少 article_id');
        if ($content === '')                    return $this->fail('评论不能为空');
        if (mb_strlen($content, 'UTF-8') > 500) return $this->fail('评论最多 500 字');

        // 只允许给已审核的文章评论
        $article = CmtArticleModel::where('id', $articleId)->where('status', 1)->find();
        if (!$article) return $this->fail('文章不存在或未审核', 404, 404);

        $row = CmtModel::create([
            'article_id' => $articleId,
            'user_id'    => (int)$user['id'],
            'content'    => $content,
        ]);

        return $this->ok(['id' => (int)$row->id], '评论成功');
    }

    // 删评论（本人或管理员）
    public function remove(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $id = (int)$request->post('id', 0);
        if ($id <= 0) return $this->fail('缺少 id');

        $row = CmtModel::where('id', $id)->field('id,user_id')->find();
        if (!$row) return $this->fail('评论不存在', 404, 404);

        $isOwner = (int)$row->user_id === (int)$user['id'];
        $isAdmin = (int)$user['role'] === 1;
        if (!$isOwner && !$isAdmin) {
            return $this->fail('只能删除自己的评论', 1005, 403);
        }

        CmtModel::destroy($id);

        return $this->ok(null, '删除成功');
    }
}
