<?php

namespace App\Libraries\Validation\Rules;

use Closure;
use Respect\Validation\Rules\AbstractRule;
use Illuminate\Database\Capsule\Manager as Capsule;

abstract class DatabaseRule extends AbstractRule
{
    protected $database;

    protected $table;

    protected $column;

    protected $options;

    public function __construct(Capsule $database, string $table, string $column, Closure $callback = null)
    {
        $this->database = $database;
        $this->table = $table;
        $this->column = $column;
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
