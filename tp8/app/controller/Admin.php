<?php
namespace app\controller;

use app\model\Article;
use app\model\Comment;
use app\model\User;
use think\Request;

class Admin extends ApiBase
{
    public function articles(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;

        [$page, $size, $offset] = $this->pageParams($request, 10);
        $status = (string)$request->get('status', '');
        $kw     = trim((string)$request->get('keyword', ''));

        $total = Article::adminCount($status, $kw);
        $rows  = Article::adminPage($status, $kw, $offset, $size);

        $map = [0 => '待审核', 1 => '已通过', 2 => '已驳回'];
        foreach ($rows as &$r) {
            $r['id']            = (int)$r['id'];
            $r['status']        = (int)$r['status'];
            $r['status_text']   = $map[$r['status']] ?? '未知';
            $r['like_count']    = Article::likeCount($r['id']);
            $r['comment_count'] = Article::commentCount($r['id']);
        }
        unset($r);

        return $this->ok(['list' => $rows, 'total' => $total, 'page' => $page, 'size' => $size]);
    }

    public function article(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;

        $id = (int)$request->get('id', 0);
        if ($id <= 0) return $this->fail('缺少 id');

        $a = Article::findAny($id);
        if (!$a) return $this->fail('文章不存在', 404, 404);

        $map = [0 => '待审核', 1 => '已通过', 2 => '已驳回'];
        $a['id']            = (int)$a['id'];
        $a['status']        = (int)$a['status'];
        $a['status_text']   = $map[$a['status']] ?? '未知';
        $a['like_count']    = Article::likeCount($id);
        $a['comment_count'] = Article::commentCount($id);

        $comments = Comment::byArticle($id);
        foreach ($comments as &$c) {
            $c['id']      = (int)$c['id'];
            $c['user_id'] = (int)$c['user_id'];
        }
        unset($c);

        return $this->ok(['article' => $a, 'comments' => $comments]);
    }

    public function audit(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $id     = (int)$request->post('id', 0);
        $status = (int)$request->post('status', -1);

        if ($id <= 0)                         return $this->fail('缺少 id');
        if (!in_array($status, [1, 2], true)) return $this->fail('status 只能是 1（通过）或 2（驳回）');

        if (!Article::findRow($id)) return $this->fail('文章不存在', 404, 404);

        Article::setStatus($id, $status);

        return $this->ok(null, $status === 1 ? '审核通过' : '已驳回');
    }

    public function commentDelete(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $id = (int)$request->post('id', 0);
        if ($id <= 0) return $this->fail('缺少 id');

        if (!Comment::findRow($id)) return $this->fail('评论不存在', 404, 404);

        Comment::remove($id);

        return $this->ok(null, '删除成功');
    }

    public function comments(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;

        [$page, $size, $offset] = $this->pageParams($request, 20);
        $kw = trim((string)$request->get('keyword', ''));

        $total = Comment::adminCount($kw);
        $rows  = Comment::adminPage($kw, $offset, $size);

        foreach ($rows as &$r) {
            $r['id']         = (int)$r['id'];
            $r['article_id'] = (int)$r['article_id'];
            $r['user_id']    = (int)$r['user_id'];
        }
        unset($r);

        return $this->ok(['list' => $rows, 'total' => $total, 'page' => $page, 'size' => $size]);
    }

    public function users(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;

        [$page, $size, $offset] = $this->pageParams($request, 20);
        $kw = trim((string)$request->get('keyword', ''));

        $total = User::countAll($kw);
        $rows  = User::page($offset, $size, $kw);

        foreach ($rows as &$r) {
            $r['id']        = (int)$r['id'];
            $r['role']      = (int)$r['role'];
            $r['role_text'] = $r['role'] === 1 ? '管理员' : '普通用户';
        }
        unset($r);

        return $this->ok(['list' => $rows, 'total' => $total, 'page' => $page, 'size' => $size]);
    }
}