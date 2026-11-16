<?php
namespace app\model;

use think\Model;

class Comment extends Model
{
    protected $name = 'comment';

    public static function byArticle(int $articleId): array
    {
        return self::alias('c')
            ->join('user u', 'u.id = c.user_id')
            ->where('c.article_id', $articleId)
            ->field('c.id,c.user_id,c.content,c.created_at,u.username')
            ->order('c.id desc')
            ->select()
            ->toArray();
    }

    public static function countByArticle(int $articleId): int
    {
        return (int)self::where('article_id', $articleId)->count();
    }

    public static function createComment(int $articleId, int $userId, string $content): int
    {
        $row = self::create([
            'article_id' => $articleId,
            'user_id'    => $userId,
            'content'    => $content,
        ]);
        return (int)$row->id;
    }

    public static function findRow(int $id): ?array
    {
        $row = self::where('id', $id)->field('id,user_id,article_id')->find();
        return $row ? $row->toArray() : null;
    }

    public static function remove(int $id): void
    {
        self::destroy($id);
    }

    public static function adminCount(string $kw): int
    {
        $q = self::alias('c');
        if ($kw !== '') $q->whereLike('c.content', '%' . $kw . '%');
        return (int)$q->count();
    }

    public static function adminPage(string $kw, int $offset, int $size): array
    {
        $q = self::alias('c')
            ->join('user u', 'u.id = c.user_id')
            ->join('article a', 'a.id = c.article_id')
            ->field('c.id,c.article_id,c.user_id,c.content,c.created_at,u.username,a.title as article_title');
        if ($kw !== '') $q->whereLike('c.content', '%' . $kw . '%');
        return $q->order('c.id desc')->limit($offset, $size)->select()->toArray();
    }

    public static function mineCount(int $userId): int
    {
        return (int)self::where('user_id', $userId)->count();
    }

    public static function minePage(int $userId, int $offset, int $size): array
    {
        return self::alias('c')
            ->join('article a', 'a.id = c.article_id')
            ->where('c.user_id', $userId)
            ->field('c.id,c.article_id,c.content,c.created_at,a.title as article_title')
            ->order('c.id desc')
            ->limit($offset, $size)
            ->select()
            ->toArray();
    }
}