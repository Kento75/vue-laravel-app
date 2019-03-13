<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    /** プライマリキーの型 */
    protected $keyType = 'string';

    /** IDの桁数 */
    const ID_LENGTH = 12;

    /** JSONに含めるアクセサ */
    protected $appends = [
        'url',
    ];

    /**
     * JSONに含める情報
     * id,url,owner
     */
    protected $visible = [
        "id",
        "owner",
        "url",
        "comments",
    ];

    // 1ページあたりの写真表示数
    protected $perPage = 9;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (! array_get($this->attributes, 'id')) {
            $this->setId();
        }
    }

    /**
     * ランダムなID値をid属性に代入する
     */
    private function setId()
    {
        $this->attributes['id'] = $this->getRandomId();
    }

    /**
     * ランダムなID値を生成する
     * @return string
     */
    private function getRandomId()
    {
        $characters = array_merge(
            range(0, 9), range('a', 'z'),
            range('A', 'Z'), ['-', '_']
        );

        $length = count($characters);

        $id = "";

        for ($i = 0; $i < self::ID_LENGTH; $i++) {
            $id .= $characters[random_int(0, $length - 1)];
        }

        return $id;
    }

    /**
     * Usersテーブル　リレーションシップ
     */
    public function owner()
    {
        return $this->belongsTo("App\User", "user_id", "id", "users");
    }

    /**
     * Usersテーブル リレーションシップ
     */
    public function likes()
    {
        return $this->belongsToMany("App\User", "likes")->withTimestamps();
    }

    /**
     * commentsテーブル リレーションシップ
     */
    public function comments()
    {
        return $this->hasMany("App\Comment")->orderBy("id", "desc");
    }

    /**
     * url アクセサ
     *  @return string
     */
    public function getUrlAttribute()
    {
        return Storage::cloud()->url($this->attributes["filename"]);
    }
}
