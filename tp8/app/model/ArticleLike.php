<?php
namespace app\model;

use think\Model;

class ArticleLike extends Model
{
    protected $name = 'article_likes';

    public static function isLiked(int $articleId, int $userId): bool
    {
        return (bool)self::where('article_id', $articleId)->where('user_id', $userId)->find();
    }

    public static function add(int $articleId, int $userId): void
    {
        self::create(['article_id' => $articleId, 'user_id' => $userId]);
    }

    public static function remove(int $articleId, int $userId): void
    {
        self::where('article_id', $articleId)->where('user_id', $userId)->delete();
    }

    public static function countByArticle(int $articleId): int
    {
        return (int)self::where('article_id', $articleId)->count();
    }

    public static function countByUser(int $userId): int
    {
        return (int)self::where('user_id', $userId)->count();
    }

    public static function pageByUser(int $userId, int $offset, int $size): array
    {
        return self::alias('l')
            ->join('article a', 'a.id = l.article_id')
            ->join('user u', 'u.id = a.user_id')
            ->where('l.user_id', $userId)
            ->field('a.id,a.title,a.created_at,u.username')
            ->order('l.id desc')
            ->limit($offset, $size)
            ->select()
            ->toArray();
    }
}