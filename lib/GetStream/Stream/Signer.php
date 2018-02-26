<?php

namespace GetStream\Stream;

use HttpSignatures\Context;
use \Firebase\JWT\JWT;

class Signer
{
    /**
     * @var string
     */
    private $api_key;

    /**
     * @var string
     */
    private $api_secret;

    /**
     * @var HttpSignatures\Context
     */
    public $context;

    /**
     * @param string $api_key
     * @param string $api_secret
     */
    public function __construct($api_key, $api_secret)
    {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->context = new Context([
          'keys' => array($api_key =>$api_secret),
          'algorithm' => 'hmac-sha256',
          'headers' => array('(request-target)', 'Date'),
        ]);
    }

    /**
     * @param  string $value
     * @return string
     */
    public function urlSafeB64encode($value)
    {
        return trim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    /**
     * @param  string $value
     * @return string
     */
    public function signature($value)
    {
        $digest = hash_hmac('sha1', $value, sha1($this->api_secret, true), true);

        return $this->urlSafeB64encode($digest);
    }

    /**
     * @param  string $feedId
     * @param  string $resource
     * @param  string $action
     * @return string
     */
    public function jwtScopeToken($feedId, $resource, $action)
    {
        $payload = [
            'action'   => $action,
            'feed_id'  => $feedId,
            'resource' => $resource,
        ];

        return JWT::encode($payload, $this->api_secret, 'HS256');
    }
}
