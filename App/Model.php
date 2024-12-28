<?php
namespace Theincubator\PhpRestApiLite;

use Theincubator\PhpRestApiLite\SQLConnection;
/**
 * Basic Model class. Extend this class to get SQL connection and perform CRUD
 * operations.
 *
 * @author charanputrevu
 */
class Model {
    protected $sqlConnection;
    
    public function __construct() {
        $this->sqlConnection = new SQLConnection();
    }
}
