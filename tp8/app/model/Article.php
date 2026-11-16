<?php
namespace app\model;

use think\Model;

class Article extends Model
{
    protected $name = 'article';

    public static function countPublished(): int
    {
        return (int)self::where('status', 1)->count();
    }

    public static function publishedPage(int $offset, int $size): array
    {
        return self::alias('a')
            ->join('user u', 'u.id = a.user_id')
            ->where('a.status', 1)
            ->field('a.id,a.title,a.content,a.status,a.created_at,u.username')
            ->order('a.id desc')
            ->limit($offset, $size)
            ->select()
            ->toArray();
    }

    public static function findPublished(int $id): ?array
    {
        $row = self::alias('a')
            ->join('user u', 'u.id = a.user_id')
            ->where('a.id', $id)
            ->where('a.status', 1)
            ->field('a.id,a.title,a.content,a.status,a.created_at,u.username')
            ->find();
        return $row ? $row->toArray() : null;
    }

    public static function findAny(int $id): ?array
    {
        $row = self::alias('a')
            ->join('user u', 'u.id = a.user_id')
            ->where('a.id', $id)
            ->field('a.id,a.title,a.content,a.status,a.created_at,u.username')
            ->find();
        return $row ? $row->toArray() : null;
    }

    public static function findRow(int $id): ?array
    {
        $row = self::where('id', $id)->field('id,user_id,status')->find();
        return $row ? $row->toArray() : null;
    }

    public static function createArticle(int $userId, string $title, string $content): int
    {
        $row = self::create([
            'user_id' => $userId,
            'title'   => $title,
            'content' => $content,
            'status'  => 0,
        ]);
        return (int)$row->id;
    }

    public static function remove(int $id): void
    {
        self::destroy($id);
    }

    public static function setStatus(int $id, int $status): void
    {
        self::where('id', $id)->update(['status' => $status]);
    }

    public static function adminCount(string $status, string $kw): int
    {
        $q = self::alias('a');
        if ($status !== '' && in_array((int)$status, [0, 1, 2], true)) $q->where('a.status', (int)$status);
        if ($kw !== '') $q->whereLike('a.title', '%' . $kw . '%');
        return (int)$q->count();
    }

    public static function adminPage(string $status, string $kw, int $offset, int $size): array
    {
        $q = self::alias('a')->join('user u', 'u.id = a.user_id')
            ->field('a.id,a.title,a.status,a.created_at,u.username');
        if ($status !== '' && in_array((int)$status, [0, 1, 2], true)) $q->where('a.status', (int)$status);
        if ($kw !== '') $q->whereLike('a.title', '%' . $kw . '%');
        return $q->order('a.id desc')->limit($offset, $size)->select()->toArray();
    }

    public static function mineCount(int $userId): int
    {
        return (int)self::where('user_id', $userId)->count();
    }

    public static function minePage(int $userId, int $offset, int $size): array
    {
        return self::where('user_id', $userId)
            ->order('id desc')
            ->limit($offset, $size)
            ->select()
            ->toArray();
    }

    /* ---------- 统计 ---------- */

    public static function likeCount(int $id): int
    {
        return ArticleLike::countByArticle($id);
    }

    public static function commentCount(int $id): int
    {
        return Comment::countByArticle($id);
    }

    public static function hasLiked(int $id, int $userId): bool
    {
        return $userId > 0 && ArticleLike::isLiked($id, $userId);
    }
}