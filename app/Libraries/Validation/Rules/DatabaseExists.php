<?php

namespace App\Libraries\Validation\Rules;

// Rule to determine whether an input exists in the database
class DatabaseExists extends DatabaseRule
{
    public function validate($input)
    {
        return $this->getQuery()->where($this->column, $input)->exists();
    }
}
