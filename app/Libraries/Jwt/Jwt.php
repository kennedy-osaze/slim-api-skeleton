<?php

namespace App\Libraries\Jwt;

use Exception;
use App\Models\User;
use InvalidArgumentException;
use Firebase\JWT\JWT as BaseJWT;
use Illuminate\Database\Eloquent\Model;
use App\Libraries\Jwt\Exceptions\JwtException;
use App\Libraries\Jwt\Exceptions\InvalidConfigException;


class JWT extends BaseJWT
{
    protected $config = [
        'algo' => '',
        'secret' => '',
        'keys' => ['public' => '', 'private' => '', 'pass_phrase'],
        'leeway' => 0,
        'token_lifetime' => 60,
        'authenticable' => User::class,
    ];

    protected $payload = [];

    public function __construct(array $config)
    {
        $this->config = array_merge($this->config, $config);

        $this->validateConfig();
    }

    public function generateToken(string $token_issuer = null, string $token_issued_to = null, array $claims = [])
    {
        try {
            $this->payload = [
                'iss' => '',
                'aud' => '',
                'sub' => '',

                'iat' => '',
                'nbf' => '',
                'exp' => ''
            ];

            return static::encode($this->payload, $this->getSigningKey(), $this->config['algo']);
        } catch (Exception $e) {
            throw new JwtException('Unable to generate token', 100, $e);
        }
    }

    public function validateToken(string $token)
    {
        // try {

        // } catch () {

        // } catch () {

        // }
    }

    protected function validateConfig()
    {
        $config = $this->config;

        if (!array_key_exists($config['algo'], static::$supported_algs)) {
            throw new InvalidConfigException("The \"{$config['algo']}\" algorithm is not supported");
        }

        $algo_uses_openssl = $this->algoIsAsymmetric();

        if (!$algo_uses_openssl && empty($config['secret'])) {
            throw new InvalidConfigException("The \"{$config['algo']}\" algorithm requires a JWT secret");
        }

        if ($algo_uses_openssl && count(array_filter($config['keys'])) < 2) {
            throw new InvalidConfigException("The \"{$config['algo']}\" algorithm requires both a public key and a private key which could be a resource or a file path");
        }

        if (!class_exists((string) $config['authenticable']) || !(new $config['authenticable'] instanceof Model)) {
            throw new InvalidConfigException('The authenticable must be an instance of ' . Model::class);
        }
    }

    protected function algoIsAsymmetric()
    {
        if (!array_key_exists($this->config['algo'], static::$supported_algs)) {
            return false;
        }

        return in_array('openssl', static::$supported_algs[$this->config['algo']]);
    }

    /**
     * Get the key used to sign the tokens.
     *
     * @return resource|string
     */
    protected function getSigningKey()
    {
        if (!$this->algoIsAsymmetric()) {
            return $this->config['secret'];
        }

        $key_resource = openssl_get_privatekey(
            $this->getKeyContent((string) $this->config['keys']['private']),
            (string) $this->config['keys']['pass_phrase']
        );

        $this->validateKeyResource($key_resource);

        return $key_resource;
    }

    /**
     * Get the key used to verify the tokens.
     *
     * @return resource|string
     */
    protected function getVerifyingKey()
    {
        if (!$this->algoIsAsymmetric()) {
            return $this->config['secret'];
        }

        $key_resource = openssl_get_publickey($this->getKeyContent((string) $this->config['keys']['public']));

        $this->validateKeyResource($key_resource);

        return $key_resource;
    }

    /**
     * Get the content of the key
     *
     * @return string
     */
    protected function getKeyContent(string $key)
    {
        return file_exists($key) ? file_get_contents($key) : $key;
    }

    /**
     * Validates key resource
     *
     * @throws InvalidArgumentException
     * @return void
     */
    protected function validateKeyResource($key_resource)
    {
        if ($key_resource === false) {
            throw new InvalidArgumentException('Unable to parse key: ' . openssl_error_string());
        }

        $details = openssl_pkey_get_details($key_resource);

        if (!isset($details['key']) || $details['type'] !== OPENSSL_KEYTYPE_RSA) {
            throw new InvalidArgumentException('Key has an invalid RSA signature');
        }
    }
}
