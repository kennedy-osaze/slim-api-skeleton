<?php

namespace App\Libraries\Validation\Rules;

use Closure;
use Respect\Validation\Rules\AbstractRule;
use Illuminate\Database\Capsule\Manager as Capsule;

abstract class DatabaseRule extends AbstractRule
{
    public $table;

    public $field;

    public $options;

    public function __construct(Capsule $database, string $table, string $field, Closure $callback = null)
    {
        $this->table = $table;
        $this->field = $field;
        $this->callback = $callback;
    }

    protected function getQuery()
    {
        $query = $this->database->table($this->table);

        if ($this->callback) {
            call_user_func($this->callback, $query);
        }

        return $query;
    }

    abstract public function validate($input);
}
