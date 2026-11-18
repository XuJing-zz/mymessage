<?php
namespace app\controller;

use think\Model;
use think\Request;

// —— 本文件内用的模型（名字加 Me 前缀，避免和别的控制器文件里的类重名）——
class MeArticleModel extends Model { protected $name = 'article'; }
class MeCommentModel extends Model { protected $name = 'comment'; }
class MeLikeModel    extends Model { protected $name = 'article_likes'; }

class Me extends ApiBase
{
    // 我的文章（含待审核 / 已通过 / 已驳回）
    public function articles(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        [$page, $size, $offset] = $this->pageParams($request, 10);

        $uid = (int)$user['id'];

        $total = MeArticleModel::where('user_id', $uid)->count();

        $rows = MeArticleModel::where('user_id', $uid)
            ->order('id desc')
            ->limit($offset, $size)
            ->select()
            ->toArray();

        $map = [0 => '待审核', 1 => '已通过', 2 => '已驳回'];
        foreach ($rows as &$r) {
            $r['id']            = (int)$r['id'];
            $r['status']        = (int)$r['status'];
            $r['status_text']   = $map[$r['status']] ?? '未知';
            $r['like_count']    = (int)MeLikeModel::where('article_id', $r['id'])->count();
            $r['comment_count'] = (int)MeCommentModel::where('article_id', $r['id'])->count();
        }
        unset($r);

        return $this->ok(['list' => $rows, 'total' => (int)$total, 'page' => $page, 'size' => $size]);
    }

    // 我的评论（带文章标题）
    public function comments(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        [$page, $size, $offset] = $this->pageParams($request, 20);

        $uid = (int)$user['id'];

        $total = MeCommentModel::where('user_id', $uid)->count();

        $rows = MeCommentModel::alias('c')
            ->join('article a', 'a.id = c.article_id')
            ->where('c.user_id', $uid)
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

    // 我的点赞（我赞过的文章）
    public function likes(Request $request)
    {
        [$user, $err] = $this->mustLogin();
        if ($err) return $err;
        [$page, $size, $offset] = $this->pageParams($request, 20);

        $uid = (int)$user['id'];

        $total = MeLikeModel::where('user_id', $uid)->count();

        $rows = MeLikeModel::alias('l')
            ->join('article a', 'a.id = l.article_id')
            ->join('user u', 'u.id = a.user_id')
            ->where('l.user_id', $uid)
            ->field('a.id,a.title,a.created_at,u.username')
            ->order('l.id desc')
            ->limit($offset, $size)
            ->select()
            ->toArray();

        foreach ($rows as &$r) {
            $r['id']            = (int)$r['id'];
            $r['like_count']    = (int)MeLikeModel::where('article_id', $r['id'])->count();
            $r['comment_count'] = (int)MeCommentModel::where('article_id', $r['id'])->count();
        }
        unset($r);

        return $this->ok(['list' => $rows, 'total' => (int)$total, 'page' => $page, 'size' => $size]);
    }
}
