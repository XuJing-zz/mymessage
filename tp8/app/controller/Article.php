<?php
namespace app\controller;

use app\model\Article as ArticleModel;
use app\model\Comment;
use think\Request;

class Article extends ApiBase
{
    public function index(Request $request)
    {
        [$page, $size, $offset] = $this->pageParams($request, 10);

        $total = ArticleModel::countPublished();
        $rows  = ArticleModel::publishedPage($offset, $size);

        foreach ($rows as &$r) {
            $r['id']            = (int)$r['id'];
            $r['status']        = (int)$r['status'];
            $r['like_count']    = ArticleModel::likeCount($r['id']);
            $r['comment_count'] = ArticleModel::commentCount($r['id']);
        }
        unset($r);

        return $this->ok(['list' => $rows, 'total' => $total, 'page' => $page, 'size' => $size]);
    }

    public function read(Request $request)
    {
        $id = (int)$request->get('id', 0);
        if ($id <= 0) return $this->fail('缺少 id');

        $me   = $this->currentUser();
        $myId = $me['id'] ?? 0;

        $article = ArticleModel::findPublished($id);
        if (!$article) return $this->fail('文章不存在或未审核', 404, 404);

        $article['id']            = (int)$article['id'];
        $article['status']        = (int)$article['status'];
        $article['like_count']    = ArticleModel::likeCount($id);
        $article['comment_count'] = ArticleModel::commentCount($id);
        $article['liked']         = ArticleModel::hasLiked($id, $myId);

        $comments = Comment::byArticle($id);
        foreach ($comments as &$c) {
            $c['id']      = (int)$c['id'];
            $c['user_id'] = (int)$c['user_id'];
        }
        unset($c);

        return $this->ok(['article' => $article, 'comments' => $comments]);
    }

    public function add(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $title   = trim((string)$request->post('title', ''));
        $content = trim((string)$request->post('content', ''));

        if ($title === '')                    return $this->fail('标题不能为空');
        if (mb_strlen($title, 'UTF-8') > 200) return $this->fail('标题最多 200 字');
        if ($content === '')                  return $this->fail('内容不能为空');
        if (mb_strlen($content, 'UTF-8') < 4) return $this->fail('内容不能少于 4 个字');

        $id = ArticleModel::createArticle((int)$user['id'], $title, $content);

        return $this->ok(['id' => $id], '提交成功，等待审核');
    }

    public function remove(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        if ($err = $this->needPost($request)) return $err;

        $id = (int)$request->post('id', 0);
        if ($id <= 0) return $this->fail('缺少 id');

        $row = ArticleModel::findRow($id);
        if (!$row) return $this->fail('文章不存在', 404, 404);

        $isOwner = (int)$row['user_id'] === (int)$user['id'];
        $isAdmin = (int)$user['role'] === 1;
        if (!$isOwner && !$isAdmin) return $this->fail('只能删除自己的文章', 1005, 403);

        ArticleModel::remove($id);

        return $this->ok(null, '删除成功');
    }
}