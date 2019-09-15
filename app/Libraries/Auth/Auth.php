<?php

namespace App\Libraries\Auth;

use Psr\Container\ContainerInterface;
use Illuminate\Database\Eloquent\Model;
use App\Libraries\Auth\AuthenticableInterface;

class Auth
{
    protected $user;

    protected $container;

    protected $model;

    public function __construct(ContainerInterface $container, Model $model = null)
    {
        $this->container = $container;

        if (! ($model instanceof AuthenticableInterface)) {
            throw new \Exception('The model property must extend the App\Libraries\Auth\AuthenticableInterface interface.');
        }

        $this->model = $model;
    }

    public function attempt(array $credentials)
    {
        if (!$user = $this->getUserByCredentials($credentials)) {
            return false;
        }

        if (!$this->userHasValidCredentials($user, $credentials)) {
            return false;
        }

        $this->user = $user;
        
        return $this->container->get('jwt')->getTokenForUser($user);
    }

    protected function getUserByCredentials(array $credentials)
    {
        if (count($credentials) < 2) {
            return null;
        }

        $query = $this->model->newQuery();

        foreach ($credentials as $key => $value) {
            if ($key === 'password') {
                continue;
            }

            $query = (is_array($value)) ? $query->whereIn($key, $value) : $query->where($key, $value);
        }

        return $query->first();
    }

    protected function userHasValidCredentials(AuthenticableInterface $user, array $credentials)
    {
        $password = (string) $credentials['password'];

        return password_verify($password, $user->getAuthPassword());
    }

    public function user()
    {
        if ($this->user !== null) {
            return $this->user;
        }

        try {
            $this->user = $this->container->get('jwt')->getUserByToken();
        } catch (\Exception $e) {
            $this->user = null;
        }

        return $this->user;
    }

    public function id()
    {
        if ($user = $this->user()) {
            return $user->getJwtIdentifier();
        }
    }

    public function check()
    {
        return !is_null($this->user());
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }
}
