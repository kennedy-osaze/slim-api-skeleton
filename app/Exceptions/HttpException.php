<?php

namespace App\Exceptions;


class HttpException extends \RuntimeException
{
    protected $headers;

    protected $status_code;

    public function __construct(int $status_code, string $message = null, \Exception $previous = null, array $headers = [], int $code = 0)
    {
        $this->status_code = $status_code;
        $this->headers = $headers;

        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode()
    {
        return $this->status_code;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }
}
