<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\Jwt\JwtSubjectInterface;
use App\Libraries\Auth\AuthenticableTrait;
use App\Libraries\Auth\AuthenticableInterface;

class User extends Model implements AuthenticableInterface, JwtSubjectInterface
{
    use AuthenticableTrait;

    protected $fillable = [
        'name', 'email', 'password'
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
        return static::where($this->getKeyName(), $identifier)->first();
    }
}
