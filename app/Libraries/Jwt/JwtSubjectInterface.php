<?php

namespace App\Libraries\Jwt;

interface JwtSubjectInterface
{
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJwtIdentifier();

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJwtCustomClaims();

    /**
     * Returns a full representation of the token owner using specified identifier
     *
     * @param mixed $identifier
     *
     * @return mixed
     */
    public function getJwtTokenOwnerByIdentifier($identifier);
}
