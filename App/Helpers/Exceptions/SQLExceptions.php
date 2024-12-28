<?php
namespace Theincubator\PhpRestApiLite\Helpers\Exceptions;

use Exception;
/**
 * Class to handle exceptions related to SQL.
 *
 * @author charanputrevu
 */
class SQLExceptions extends Exception{
    /**
     * Throw error if there is SQL connection error
     * @param string $error
     * @return Exception
     */
    public function connectionError(string $error = '') {
        if (!empty($error) || $error === '') {
            $error = "Cannot connect to MySQLi database";
        }
        return new Exception($error, 500);
    }
    
    /**
     * Throw error if the query is malformed or has a problem.
     * @param string $error
     * @return Exception
     */
    public function queryError(string $error) {
        return new Exception($error, 500);
    }
}
