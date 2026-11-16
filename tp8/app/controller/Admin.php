<?php
namespace app\controller;

use think\facade\Db;
use think\Request;

class Admin extends ApiBase
{
    private function page(Request $request, int $def = 10): array
    {
        $page = max(1, (int)$request->get('page', 1));
        $size = (int)$request->get('size', $def);
        $size = $size < 1 ? $def : min(50, $size);
        return [$page, $size, ($page - 1) * $size];
    }

    // GET api/admin/articles?status=&page=&keyword=
    public function articles(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;

        [$page, $size, $offset] = $this->page($request);

        $q = Db::name('article')->alias('a')->join('user u', 'u.id = a.user_id');

        $status = $request->get('status', '');
        if ($status !== '' && in_array((int)$status, [0, 1, 2], true)) {
            $q->where('a.status', (int)$status);
        }
        $kw = trim((string)$request->get('keyword', ''));
        if ($kw !== '') {
            $q->whereLike('a.title', '%' . $kw . '%');
        }

        $total = (clone $q)->count();

        $rows = $q->field('a.id,a.title,a.status,a.created_at,u.username')
            ->order('a.id desc')
            ->limit($offset, $size)
            ->select()
            ->toArray();

        $map = [0 => '待审核', 1 => '已通过', 2 => '已驳回'];
        foreach ($rows as &$r) {
            $r['id']            = (int)$r['id'];
            $r['status']        = (int)$r['status'];
            $r['status_text']   = $map[$r['status']] ?? '未知';
            $r['like_count']    = (int)Db::name('article_likes')->where('article_id', $r['id'])->count();
            $r['comment_count'] = (int)Db::name('comment')->where('article_id', $r['id'])->count();
        }
        unset($r);

        return $this->ok(['list' => $rows, 'total' => (int)$total, 'page' => $page, 'size' => $size]);
    }

    // GET api/admin/article?id=1
    public function article(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;

        $id = (int)$request->get('id', 0);
        if ($id <= 0) return $this->fail('缺少 id');

        $a = Db::name('article')
            ->alias('a')
            ->join('user u', 'u.id = a.user_id')
            ->where('a.id', $id)
            ->field('a.id,a.title,a.content,a.status,a.created_at,u.username')
            ->find();

        if (!$a) return $this->fail('文章不存在', 404, 404);

        $map = [0 => '待审核', 1 => '已通过', 2 => '已驳回'];
        $a['id']            = (int)$a['id'];
        $a['status']        = (int)$a['status'];
        $a['status_text']   = $map[$a['status']] ?? '未知';
        $a['like_count']    = (int)Db::name('article_likes')->where('article_id', $id)->count();
        $a['comment_count'] = (int)Db::name('comment')->where('article_id', $id)->count();

        $comments = Db::name('comment')
            ->alias('c')
            ->join('user u', 'u.id = c.user_id')
            ->where('c.article_id', $id)
            ->field('c.id,c.user_id,c.content,c.created_at,u.username')
            ->order('c.id desc')
            ->select()
            ->toArray();

        foreach ($comments as &$c) {
            $c['id']      = (int)$c['id'];
            $c['user_id'] = (int)$c['user_id'];
        }
        unset($c);

        return $this->ok(['article' => $a, 'comments' => $comments]);
    }

    // POST api/admin/article/audit
    public function audit(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $id     = (int)$request->post('id', 0);
        $status = (int)$request->post('status', -1);

        if ($id <= 0)                         return $this->fail('缺少 id');
        if (!in_array($status, [1, 2], true)) return $this->fail('status 只能是 1（通过）或 2（驳回）');

        $exists = Db::name('article')->where('id', $id)->find();
        if (!$exists) return $this->fail('文章不存在', 404, 404);

        Db::name('article')->where('id', $id)->update(['status' => $status]);

        return $this->ok(null, $status === 1 ? '审核通过' : '已驳回');
    }

    // POST api/admin/comment/delete
    public function commentDelete(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $id = (int)$request->post('id', 0);
        if ($id <= 0) return $this->fail('缺少 id');

        $row = Db::name('comment')->where('id', $id)->find();
        if (!$row) return $this->fail('评论不存在', 404, 404);

        Db::name('comment')->where('id', $id)->delete();

        return $this->ok(null, '删除成功');
    }

    // GET api/admin/comments
    public function comments(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;

        [$page, $size, $offset] = $this->page($request, 20);

        $q = Db::name('comment')
            ->alias('c')
            ->join('user u', 'u.id = c.user_id')
            ->join('article a', 'a.id = c.article_id');

        $articleId = (int)$request->get('article_id', 0);
        if ($articleId > 0) $q->where('c.article_id', $articleId);

        $kw = trim((string)$request->get('keyword', ''));
        if ($kw !== '') {
            $q->whereLike('c.content', '%' . $kw . '%');
        }

        $total = (clone $q)->count();

        $rows = $q->field('c.id,c.article_id,c.user_id,c.content,c.created_at,u.username,a.title as article_title')
            ->order('c.id desc')
            ->limit($offset, $size)
            ->select()
            ->toArray();

        foreach ($rows as &$r) {
            $r['id']         = (int)$r['id'];
            $r['article_id'] = (int)$r['article_id'];
            $r['user_id']    = (int)$r['user_id'];
        }
        unset($r);

        return $this->ok(['list' => $rows, 'total' => (int)$total, 'page' => $page, 'size' => $size]);
    }

    // GET api/admin/users
    public function users(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;

        [$page, $size, $offset] = $this->page($request, 20);

        $q = Db::name('user');
        $kw = trim((string)$request->get('keyword', ''));
        if ($kw !== '') $q->whereLike('username', '%' . $kw . '%');

        $total = (clone $q)->count();

        $rows = $q->field('id,username,role,created_at')
            ->order('id asc')
            ->limit($offset, $size)
            ->select()
            ->toArray();

        foreach ($rows as &$r) {
            $r['id']        = (int)$r['id'];
            $r['role']      = (int)$r['role'];
            $r['role_text'] = $r['role'] === 1 ? '管理员' : '普通用户';
        }
        unset($r);

        return $this->ok(['list' => $rows, 'total' => (int)$total, 'page' => $page, 'size' => $size]);
    }
}