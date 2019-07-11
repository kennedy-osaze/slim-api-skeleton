<?php

namespace App\Libraries\Validation\Rules;

class DatabaseExists extends DatabaseRule
{
    public function validate($input)
    {
        return $this->getQuery()->where($this->column, $input)->exists();
    }
}
