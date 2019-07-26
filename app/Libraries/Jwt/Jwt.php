<?php

namespace App\Libraries\Jwt;

use Exception;
use Carbon\Carbon;
use InvalidArgumentException;
use UnexpectedValueException;
use Firebase\JWT\JWT as BaseJWT;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\{
    ExpiredException, SignatureInvalidException, BeforeValidException
};
use App\Libraries\Jwt\Exceptions\{
    JwtException,InvalidConfigException, TokenInvalidException, TokenExpiredException
};

class JWT extends BaseJWT
{
    /**
     * Default configuration
     *
     * @var array
     */
    protected $config = [
        'algo' => '',
        'secret' => '',
        'keys' => ['public' => '', 'private' => '', 'pass_phrase'],
        'leeway' => 0,
        'token_lifetime' => 60, // in minutes
        'authenticable' => User::class,
    ];

    
    protected $request;

    protected $payload = [];

    public function __construct(array $config, Request $request)
    {
        $this->config = array_merge($this->config, $config);
        static::$leeway = $this->config['leeway'];

        $this->request = $request;

        $this->validateConfig();
    }

    public function getTokenForUser(JwtSubjectInterface $user, $accessing_from = null)
    {
        return $this->generateToken($user->getJwtIdentifier(), $accessing_from, $user->getJwtCustomClaims());
    }

    public function generateToken(string $subject = null, string $for = null, array $custom_claims = [])
    {
        $payload = $this->makePayload(array_merge(['sub' => $subject, 'aud' => $for], $custom_claims));

        try {
            return static::encode($payload, $this->getSigningKey(), $this->config['algo']);
        } catch (Exception $e) {
            throw new JwtException('Unable to generate token:' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function makePayload(array $claims)
    {
        $now = Carbon::now('UTC');

        $payload = [
            'iss' => rtrim(preg_replace('/\?.*/', '', (string) $this->request->getUri()), '/'),
            'iat' => $now->getTimestamp(),
            'nbf' => $now->getTimestamp(),
            'exp' => $now->addMinutes((int) $this->config['token_lifetime'])->getTimestamp(),
            'jti' => str_random(),
        ];

        $payload += $claims;

        return array_filter($payload);
    }

    public function validateToken(string $token)
    {
        try {
            $this->payload = (array) static::decode(
                $token, $this->getVerifyingKey(), (array) $this->config['algo']
            );
        } catch (ExpiredException $e) {
            throw new TokenExpiredException('Token has expired', $e->getCode());
        } catch (UnexpectedValueException | SignatureInvalidException | BeforeValidException $e) {
            throw new TokenInvalidException('Unable to parse token: ' . $e->getMessage(), $e->getCode(), $e);
        } catch (Exception $e) {
            throw new JwtException('Unable to validate token: '. $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getUserByToken(string $token)
    {
        $this->validateToken($token);

        $authenticable = $this->config['authenticable'];

        return (new $authenticable)->getJwtTokenOwnerByIdentifier($this->payload['sub']);
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

        if (!class_exists((string) $config['authenticable']) || !(new $config['authenticable'] instanceof JwtSubjectInterface)) {
            throw new InvalidConfigException('The authenticable must be an instance of JwtSubjectInterface');
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
     * @return string
     */
    protected function getSigningKey()
    {
        if (!$this->algoIsAsymmetric()) {
            return $this->config['secret'];
        }

        $key = (string) $this->config['keys']['private'];
        $pass_phrase = (string) $this->config['keys']['pass_phrase'];

        $key_resource = openssl_get_privatekey($this->getKeyContent($key), $pass_phrase);

        $this->validateKeyResource($key_resource);

        return $key;
    }

    /**
     * Get the key used to verify the tokens.
     *
     * @return string
     */
    protected function getVerifyingKey()
    {
        if (!$this->algoIsAsymmetric()) {
            return $this->config['secret'];
        }

        $key = (string) $this->config['keys']['public'];
        $key_resource = openssl_get_publickey($this->getKeyContent($key));

        $this->validateKeyResource($key_resource);

        return $key;
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

    public function getRequest()
    {
        return $this->request;
    }

    public function getPayload()
    {
        return $this->payload;
    }
}
