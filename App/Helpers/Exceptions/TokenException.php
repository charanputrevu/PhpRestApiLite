<?php
namespace Theincubator\PhpRestApiLite\Helpers\Exceptions;
/*
 * Copyrights BloodInfo@2022
 * This is copyrighted software for public service distribution. Any illegal software use and manipulation will be prosecuted.  * 
 */
use Exception;
/**
 * Description of TokenException
 *
 * @author charanputrevu
 */
class TokenException extends Exception{
    public function invalidToken() {
        return new Exception("Invalid token", 401);
    }
    
    public function authorizationRequired() {
        return new Exception("Authorization Required", 403);
    }
    
    public function tokenExpired() {
        return new Exception("Token Expired", 403);
    }
    
    public function tooEarly() {
        return new Exception("Token being used in invalid time frame", 425);
    }
}
