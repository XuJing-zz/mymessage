<?php
namespace app\controller;

use app\model\Article;
use app\model\Comment as CommentModel;
use think\Request;

class Comment extends ApiBase
{
    public function index(Request $request)
    {
        $articleId = (int)$request->get('article_id', 0);
        if ($articleId <= 0) return $this->fail('缺少 article_id');

        $rows = CommentModel::byArticle($articleId);
        foreach ($rows as &$r) {
            $r['id']      = (int)$r['id'];
            $r['user_id'] = (int)$r['user_id'];
        }
        unset($r);

        return $this->ok(['list' => $rows, 'total' => count($rows)]);
    }

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

        if (!Article::findPublished($articleId)) {
            return $this->fail('文章不存在或未审核', 404, 404);
        }

        $id = CommentModel::createComment($articleId, (int)$user['id'], $content);

        return $this->ok(['id' => $id], '评论成功');
    }

    public function remove(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $id = (int)$request->post('id', 0);
        if ($id <= 0) return $this->fail('缺少 id');

        $row = CommentModel::findRow($id);
        if (!$row) return $this->fail('评论不存在', 404, 404);

        $isOwner = (int)$row['user_id'] === (int)$user['id'];
        $isAdmin = (int)$user['role'] === 1;
        if (!$isOwner && !$isAdmin) return $this->fail('只能删除自己的评论', 1005, 403);

        CommentModel::remove($id);

        return $this->ok(null, '删除成功');
    }
}