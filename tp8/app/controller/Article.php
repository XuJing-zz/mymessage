<?php
namespace app\controller;

use think\Model;
use think\Request;

// —— 本文件内的模型（名字不能和控制器重名，表名必须写清楚）——
class ArticleModel extends Model { protected $name = 'article'; }
class CommentModel extends Model { protected $name = 'comment'; }
class LikeModel    extends Model { protected $name = 'article_likes'; }

class Article extends ApiBase
{
    // 文章列表
    public function index(Request $request)
    {
        [$page, $size, $offset] = $this->pageParams($request, 5);

        $total = ArticleModel::where('status', 1)->count();

        $rows = ArticleModel::alias('a')
            ->join('user u', 'u.id = a.user_id')
            ->where('a.status', 1)
            ->field('a.id,a.title,a.content,a.status,a.created_at,u.username')
            ->order('a.id desc')
            ->limit($offset, $size)
            ->select()
            ->toArray();

        foreach ($rows as &$row) {
            $row['id']            = (int)$row['id'];
            $row['status']        = (int)$row['status'];
            $row['like_count']    = (int)LikeModel::where('article_id', $row['id'])->count();
            $row['comment_count'] = (int)CommentModel::where('article_id', $row['id'])->count();
        }
        unset($row);

        return $this->ok(['list' => $rows, 'total' => $total, 'page' => $page, 'size' => $size]);
    }

    // 文章详情（公开接口，未登录也能看）
    public function read(Request $request)
    {
        $id = (int)$request->get('id', 0);
        if ($id <= 0) return $this->fail('缺少 id');

        $me   = $this->currentUser();
        $myId = $me['id'] ?? 0;

        $row = ArticleModel::alias('a')
            ->join('user u', 'u.id = a.user_id')
            ->where('a.id', $id)
            ->where('a.status', 1)
            ->field('a.id,a.title,a.content,a.status,a.created_at,u.username')
            ->find();

        if (!$row) return $this->fail('文章不存在或未审核', 404, 404);
        $article = $row->toArray();

        $article['id']            = (int)$article['id'];
        $article['status']        = (int)$article['status'];
        $article['like_count']    = (int)LikeModel::where('article_id', $id)->count();
        $article['comment_count'] = (int)CommentModel::where('article_id', $id)->count();
        $article['liked']         = $myId > 0
            && (bool)LikeModel::where('article_id', $id)->where('user_id', $myId)->find();

        $comments = CommentModel::alias('c')
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

    // 发文章
    public function add(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $title   = trim((string)$request->post('title', ''));
        $content = trim((string)$request->post('content', ''));

        if ($title === '')   return $this->fail('标题不能为空');
        if ($content === '') return $this->fail('内容不能为空');

        $row = ArticleModel::create([
            'user_id' => (int)$user['id'],
            'title'   => $title,
            'content' => $content,
            'status'  => 0,
        ]);

        return $this->ok(['id' => (int)$row->id], '提交成功，等待审核');
    }

    // 删文章
    public function remove(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $id = (int)$request->post('id', 0);
        if ($id <= 0) return $this->fail('缺少 id');

        $row = ArticleModel::where('id', $id)->field('id,user_id')->find();
        if (!$row) return $this->fail('文章不存在', 404, 404);

        $isOwner = (int)$row->user_id === (int)$user['id'];
        $isAdmin = (int)$user['role'] === 1;
        if (!$isOwner && !$isAdmin) return $this->fail('只能删除自己的文章', 1005, 403);

        ArticleModel::destroy($id);

        return $this->ok(null, '删除成功');
    }
}