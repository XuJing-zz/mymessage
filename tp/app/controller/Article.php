<?php
namespace app\controller;

use think\facade\Db;
use think\Request;

class Article extends ApiBase
{
    // GET api/articles?page=1&size=10
    public function index(Request $request)
    {
        $page = max(1, (int)$request->get('page', 1));
        $size = (int)$request->get('size', 10);
        $size = $size < 1 ? 10 : min(50, $size);
        $offset = ($page - 1) * $size;

        $total = Db::name('article')->where('status', 1)->count();

        $rows = Db::name('article')
            ->alias('a')
            ->join('user u', 'u.id = a.user_id')
            ->where('a.status', 1)
            ->field('a.id,a.title,a.content,a.status,a.created_at,u.username')
            ->order('a.id desc')
            ->limit($offset, $size)
            ->select()
            ->toArray();

        foreach ($rows as &$r) {
            $r['id']            = (int)$r['id'];
            $r['status']        = (int)$r['status'];
            $r['like_count']    = (int)Db::name('article_likes')->where('article_id', $r['id'])->count();
            $r['comment_count'] = (int)Db::name('comment')->where('article_id', $r['id'])->count();
        }
        unset($r);

        return $this->ok(['list' => $rows, 'total' => (int)$total, 'page' => $page, 'size' => $size]);
    }

    // GET api/article?id=1
    public function read(Request $request)
    {
        $id = (int)$request->get('id', 0);
        if ($id <= 0) return $this->fail('缺少 id');

        $me   = $this->currentUser();
        $myId = $me['id'] ?? 0;

        $article = Db::name('article')
            ->alias('a')
            ->join('user u', 'u.id = a.user_id')
            ->where('a.id', $id)
            ->where('a.status', 1)
            ->field('a.id,a.title,a.content,a.status,a.created_at,u.username')
            ->find();

        if (!$article) return $this->fail('文章不存在或未审核', 404, 404);

        $article['id']            = (int)$article['id'];
        $article['status']        = (int)$article['status'];
        $article['like_count']    = (int)Db::name('article_likes')->where('article_id', $id)->count();
        $article['comment_count'] = (int)Db::name('comment')->where('article_id', $id)->count();
        $article['liked']         = (bool)Db::name('article_likes')
            ->where('article_id', $id)->where('user_id', $myId)->find();

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

        return $this->ok(['article' => $article, 'comments' => $comments]);
    }

    // POST api/article  (发文章)
    public function add(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $title   = trim((string)$request->post('title', ''));
        $content = trim((string)$request->post('content', ''));

        if ($title === '')                       return $this->fail('标题不能为空');
        if (mb_strlen($title, 'UTF-8') > 200)    return $this->fail('标题最多 200 字');
        if ($content === '')                     return $this->fail('内容不能为空');
        if (mb_strlen($content, 'UTF-8') < 4)    return $this->fail('内容不能少于 4 个字');

        $id = Db::name('article')->insertGetId([
            'user_id' => (int)$user['id'],
            'title'   => $title,
            'content' => $content,
            'status'  => 0,
        ]);

        return $this->ok(['id' => (int)$id], '提交成功，等待审核');
    }

    // POST api/article/delete
    public function remove(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $id = (int)$request->post('id', 0);
        if ($id <= 0) return $this->fail('缺少 id');

        $row = Db::name('article')->where('id', $id)->find();
        if (!$row) return $this->fail('文章不存在', 404, 404);

        $isOwner = (int)$row['user_id'] === (int)$user['id'];
        $isAdmin = (int)$user['role'] === 1;
        if (!$isOwner && !$isAdmin) return $this->fail('只能删除自己的文章', 1005, 403);

        Db::name('article')->where('id', $id)->delete();

        return $this->ok(null, '删除成功');
    }
}