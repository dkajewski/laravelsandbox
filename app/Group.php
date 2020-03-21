<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Group
 * @package App
 * @property string $name
 * @property string $description
 */
class Group extends Model
{
    /**
     * @var array $groups static array with groups of users
     */
    public static $groups = [
        'User' => 1,
        'Admin' => 2
    ];

    /**
     * Permissions belonging to the group
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany('App\Permission');
    }
}
