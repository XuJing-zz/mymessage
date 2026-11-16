<?php
namespace app\model;

use think\Model;

class User extends Model
{
    protected $name = 'user';

    public static function findByName(string $username): ?array
    {
        $row = self::where('username', $username)->find();
        return $row ? $row->toArray() : null;
    }

    public static function createUser(string $username, string $hash): int
    {
        $row = self::create([
            'username' => $username,
            'password' => $hash,
            'role'     => 0,
        ]);
        return (int)$row->id;
    }

    public static function countAll(string $kw = ''): int
    {
        $q = self::field('id');
        if ($kw !== '') $q->whereLike('username', '%' . $kw . '%');
        return (int)$q->count();
    }

    public static function page(int $offset, int $size, string $kw = ''): array
    {
        $q = self::field('id,username,role,created_at');
        if ($kw !== '') $q->whereLike('username', '%' . $kw . '%');
        return $q->order('id asc')->limit($offset, $size)->select()->toArray();
    }
}