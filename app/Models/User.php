<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\Jwt\JwtSubjectInterface;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model implements JwtSubjectInterface
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'email'
    ];

    public function getJwtIdentifier()
    {
        return $this->getKey();
    }

    public function getJwtCustomClaims()
    {
        return [
            //
        ];
    }

    public function getJwtTokenOwnerByIdentifier($identifier)
    {
        return static::findOrFail($identifier);
    }
}
