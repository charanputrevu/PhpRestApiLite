<?php
namespace Theincubator\PhpRestApiLite\Helpers;
use Theincubator\PhpRestApiLite\Helpers\Settings;
use Theincubator\PhpRestApiLite\Helpers\Exceptions\TokenException;
/*
 * Copyrights BloodInfo@2022
 * This is copyrighted software for public service distribution. Any illegal software use and manipulation will be prosecuted.  * 
 */

/**
 * Description of JWT
 *
 * @author charanputrevu
 */
class JWT {
    protected $key;
    
    protected $header = array(
        "alg" => 'HS256',
        "typ" => 'JWT'
    );
    
    protected $payload = array();
    
    protected $signature;


    public function __construct() {
        $setts = new Settings();
        $this->key = $setts->getProperty('key');
    }
    
    /**
     * Generate a JWT token
     * @return string
     */
    public function generateToken() {
        $headers = $this->base64url_encode(json_encode($this->header));
        $payload = $this->base64url_encode(json_encode($this->payload));
        $signature = $this->base64url_encode(hash_hmac('sha256', "$headers.$payload", $this->key, true));

        return "$headers.$payload.$signature";
    }
    
    /**
     * Omit unnecessary data from an encoded string
     * @param string $data
     * @return string
     */
    public function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    /**
     * Decode token details from encrypted token string.
     * @param array $headers
     * @return boolean
     * @throws TokenException
     */
    public function decodeToken(array $headers) {
        if (empty($headers) || array_key_exists('Authorization', $headers)) {
            throw (new TokenException)->authorizationRequired();
        }
        
        $token = $this->extractToken($headers['Authorization']);
        if ($token === null) {
            throw (new TokenException)->authorizationRequired();
        }
        
        $tokenArr = explode('.', $token);
        $this->header = json_decode(base64_decode($tokenArr[0]), true);
        $this->payload = json_decode(base64_decode($tokenArr[1]), true);
        $this->signature = $tokenArr[2];
        
        return $this->verifyToken();
    }
    
    /**
     * Extract the token part from the string
     * @param string $token
     * @return string
     */
    protected function extractToken(string $token) {
        $matches = [];
        preg_match('/Bearer\s(\S+)/', $token, $matches);
        
        return $matches[1];
    }

    /**
     * Verify if the received token is valid or not.
     * @return boolean
     * @throws TokenException
     */
    public function verifyToken() {
        $headers = $this->base64url_encode(json_encode($this->header));
        $payload = $this->base64url_encode(json_encode($this->payload));
        $signature = $this->base64url_encode(hash_hmac('sha256', "$headers.$payload", $this->key, true));
        if ($signature !== $this->signature) {
            throw (new TokenException)->invalidToken();
        }
        if (!isset($this->payload['exp'])) {
            throw (new TokenException)->tokenExpired();
        }
        
        $lastTokenTime = strtotime($this->payload['exp']);
        $currentTime = strtotime(date('Y-m-d H:m:s'));
        
        $diffInMin = ($currentTime - $lastTokenTime)/60;
        
        if ($diffInMin > 10) {
            throw (new TokenException)->tokenExpired();
        }
        
        return true;
    }
}
