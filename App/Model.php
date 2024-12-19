<?php
namespace Theincubator\PhpRestApiLite;
/*
 * Copyrights BloodInfo@2022
 * This is copyrighted software for public service distribution. Any illegal software use and manipulation will be prosecuted.  * 
 */
use Theincubator\PhpRestApiLite\SQLConnection;
/**
 * Description of Model
 *
 * @author charanputrevu
 */
class Model {
    protected $sqlConnection;
    
    public function __construct() {
        $this->sqlConnection = new SQLConnection();
    }
}
