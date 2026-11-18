<?php
namespace app\controller;

use think\Model;
use think\Request;

// —— 本文件内用的模型（名字加 Ad 前缀，避免和别的控制器文件里的类重名）——
class AdArticleModel extends Model { protected $name = 'article'; }
class AdCommentModel extends Model { protected $name = 'comment'; }
class AdLikeModel    extends Model { protected $name = 'article_likes'; }
class AdUserModel    extends Model { protected $name = 'user'; }

class Admin extends ApiBase
{
    // 文章列表（后台，可按状态/关键词筛选）
    public function articles(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;

        [$page, $size, $offset] = $this->pageParams($request, 10);

        $status = (string)$request->get('status', '');
        $kw     = trim((string)$request->get('keyword', ''));

        $query = AdArticleModel::alias('a')->join('user u', 'u.id = a.user_id');

        if ($status !== '' && in_array((int)$status, [0, 1, 2], true)) {
            $query->where('a.status', (int)$status);
        }
        if ($kw !== '') {
            $query->whereLike('a.title', '%' . $kw . '%');
        }

        $total = (clone $query)->count();

        $rows = $query->field('a.id,a.title,a.status,a.created_at,u.username')
            ->order('a.id desc')
            ->limit($offset, $size)
            ->select()
            ->toArray();

        $map = [0 => '待审核', 1 => '已通过', 2 => '已驳回'];
        foreach ($rows as &$r) {
            $r['id']            = (int)$r['id'];
            $r['status']        = (int)$r['status'];
            $r['status_text']   = $map[$r['status']] ?? '未知';
            $r['like_count']    = (int)AdLikeModel::where('article_id', $r['id'])->count();
            $r['comment_count'] = (int)AdCommentModel::where('article_id', $r['id'])->count();
        }
        unset($r);

        return $this->ok(['list' => $rows, 'total' => (int)$total, 'page' => $page, 'size' => $size]);
    }

    // 文章详情（后台，任何状态都能看）
    public function article(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;

        $id = (int)$request->get('id', 0);
        if ($id <= 0) return $this->fail('缺少 id');

        $row = AdArticleModel::alias('a')
            ->join('user u', 'u.id = a.user_id')
            ->where('a.id', $id)
            ->field('a.id,a.title,a.content,a.status,a.created_at,u.username')
            ->find();

        if (!$row) return $this->fail('文章不存在', 404, 404);

        $a = $row->toArray();
        $map = [0 => '待审核', 1 => '已通过', 2 => '已驳回'];
        $a['id']            = (int)$a['id'];
        $a['status']        = (int)$a['status'];
        $a['status_text']   = $map[$a['status']] ?? '未知';
        $a['like_count']    = (int)AdLikeModel::where('article_id', $id)->count();
        $a['comment_count'] = (int)AdCommentModel::where('article_id', $id)->count();

        $comments = AdCommentModel::alias('c')
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

    // 审核：1 通过 / 2 驳回
    public function audit(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $id     = (int)$request->post('id', 0);
        $status = (int)$request->post('status', -1);

        if ($id <= 0)                         return $this->fail('缺少 id');
        if (!in_array($status, [1, 2], true)) return $this->fail('status 只能是 1（通过）或 2（驳回）');

        if (!AdArticleModel::where('id', $id)->find()) {
            return $this->fail('文章不存在', 404, 404);
        }

        AdArticleModel::where('id', $id)->update(['status' => $status]);

        return $this->ok(null, $status === 1 ? '审核通过' : '已驳回');
    }

    // 管理员删评论
    public function commentDelete(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $id = (int)$request->post('id', 0);
        if ($id <= 0) return $this->fail('缺少 id');

        if (!AdCommentModel::where('id', $id)->find()) {
            return $this->fail('评论不存在', 404, 404);
        }

        AdCommentModel::destroy($id);

        return $this->ok(null, '删除成功');
    }

    // 留言列表
    public function comments(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;

        [$page, $size, $offset] = $this->pageParams($request, 20);
        $kw = trim((string)$request->get('keyword', ''));

        $query = AdCommentModel::alias('c')
            ->join('user u', 'u.id = c.user_id')
            ->join('article a', 'a.id = c.article_id');

        if ($kw !== '') {
            $query->whereLike('c.content', '%' . $kw . '%');
        }

        $total = (clone $query)->count();

        $rows = $query->field('c.id,c.article_id,c.user_id,c.content,c.created_at,u.username,a.title as article_title')
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

    // 用户列表
    public function users(Request $request)
    {
        [$admin, $err] = $this->mustAdmin();
        if ($err) return $err;

        [$page, $size, $offset] = $this->pageParams($request, 20);
        $kw = trim((string)$request->get('keyword', ''));

        $query = AdUserModel::field('id,username,role,created_at');
        if ($kw !== '') {
            $query->whereLike('username', '%' . $kw . '%');
        }

        $total = (clone $query)->count();

        $rows = $query->order('id asc')
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
