<?php
namespace app\controller;

use app\model\Article;
use app\model\ArticleLike;
use app\model\Comment;
use think\Request;

class Me extends ApiBase
{
    public function articles(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        [$page, $size, $offset] = $this->pageParams($request, 10);

        $total = Article::mineCount((int)$user['id']);
        $rows  = Article::minePage((int)$user['id'], $offset, $size);

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

    public function comments(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        [$page, $size, $offset] = $this->pageParams($request, 20);

        $total = Comment::mineCount((int)$user['id']);
        $rows  = Comment::minePage((int)$user['id'], $offset, $size);

        foreach ($rows as &$r) {
            $r['id']         = (int)$r['id'];
            $r['article_id'] = (int)$r['article_id'];
        }
        unset($r);

        return $this->ok(['list' => $rows, 'total' => $total, 'page' => $page, 'size' => $size]);
    }

    public function likes(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        [$page, $size, $offset] = $this->pageParams($request, 20);

        $total = ArticleLike::countByUser((int)$user['id']);
        $rows  = ArticleLike::pageByUser((int)$user['id'], $offset, $size);

        foreach ($rows as &$r) {
            $r['id']            = (int)$r['id'];
            $r['like_count']    = Article::likeCount($r['id']);
            $r['comment_count'] = Article::commentCount($r['id']);
        }
        unset($r);

        return $this->ok(['list' => $rows, 'total' => $total, 'page' => $page, 'size' => $size]);
    }
}