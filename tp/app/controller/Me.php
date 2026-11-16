<?php
namespace app\controller;

use think\facade\Db;
use think\Request;

class Me extends ApiBase
{
    // GET api/me/articles
    public function articles(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;

        $page = max(1, (int)$request->get('page', 1));
        $size = (int)$request->get('size', 10);
        $size = $size < 1 ? 10 : min(50, $size);
        $offset = ($page - 1) * $size;

        $total = Db::name('article')->where('user_id', (int)$user['id'])->count();

        $rows = Db::name('article')
            ->where('user_id', (int)$user['id'])
            ->order('id desc')
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

    // GET api/me/comments
    public function comments(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;

        $page = max(1, (int)$request->get('page', 1));
        $size = (int)$request->get('size', 20);
        $size = $size < 1 ? 20 : min(50, $size);
        $offset = ($page - 1) * $size;

        $total = Db::name('comment')->where('user_id', (int)$user['id'])->count();

        $rows = Db::name('comment')
            ->alias('c')
            ->join('article a', 'a.id = c.article_id')
            ->where('c.user_id', (int)$user['id'])
            ->field('c.id,c.article_id,c.content,c.created_at,a.title as article_title')
            ->order('c.id desc')
            ->limit($offset, $size)
            ->select()
            ->toArray();

        foreach ($rows as &$r) {
            $r['id']         = (int)$r['id'];
            $r['article_id'] = (int)$r['article_id'];
        }
        unset($r);

        return $this->ok(['list' => $rows, 'total' => (int)$total, 'page' => $page, 'size' => $size]);
    }

    // GET api/me/likes
    public function likes(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;

        $page = max(1, (int)$request->get('page', 1));
        $size = (int)$request->get('size', 20);
        $size = $size < 1 ? 20 : min(50, $size);
        $offset = ($page - 1) * $size;

        $total = Db::name('article_likes')->where('user_id', (int)$user['id'])->count();

        $rows = Db::name('article_likes')
            ->alias('l')
            ->join('article a', 'a.id = l.article_id')
            ->join('user u', 'u.id = a.user_id')
            ->where('l.user_id', (int)$user['id'])
            ->field('a.id,a.title,a.created_at,u.username')
            ->order('l.id desc')
            ->limit($offset, $size)
            ->select()
            ->toArray();

        foreach ($rows as &$r) {
            $r['id']            = (int)$r['id'];
            $r['like_count']    = (int)Db::name('article_likes')->where('article_id', $r['id'])->count();
            $r['comment_count'] = (int)Db::name('comment')->where('article_id', $r['id'])->count();
        }
        unset($r);

        return $this->ok(['list' => $rows, 'total' => (int)$total, 'page' => $page, 'size' => $size]);
    }
}