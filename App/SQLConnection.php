<?php
namespace Theincubator\PhpRestApiLite;
/*
 * Copyrights BloodInfo@2022
 * This is copyrighted software for public service distribution. Any illegal software use and manipulation will be prosecuted.  * 
 */
use Theincubator\PhpRestApiLite\Helpers\Exceptions\SQLExceptions;
/**
 * Description of SQLConnection
 *
 * @author charanputrevu
 */
class SQLConnection {
    
    /**
     * Holds MySQLi object
     * @var MySQLi
     */
    protected $mysqli;
    
    /**
     * Holds MySQLi prepared query.
     * @var mysqli_stml|false
     */
    protected $query;
    
    /**
     * Has auto increment id when a new SQL row is inserted.
     * @var int
     */
    public $insertId;
    
    /**
     * Has the number of row affected by a SQL query.
     * @var int
     */
    public $affectedRows;
    
    /**
     * Has SQL error number if the statement produces an error.
     * @var int
     */
    public $sqlErrNo;
    
    /**
     * Has the SQL error string if the statement products an error.
     * @var string
     */
    public $sqlError;
    
    /**
     * Contains MySQLi resource.
     * @var mysqli_result|false
     */
    public $result;
    
    public function __construct() {
        $settings = new Settings();
        $this->mysqli = new mysqli($settings->getProperty('host'), $settings->getProperty('username'), $settings->getProperty('password'),
                $settings->getProperty('db_name'), $settings->getProperty('port'));
        if ($this->mysqli->connect_error) {
            return (new SQLExceptions)->connectionError($this->mysqli->connect_error);
        }
    }
    
    /**
     * Prepares SQL statement for parameter binding.
     * @param string $query
     * @throws SQLExceptions
     * @return void
     */
    public function prepareQuery(string $query) {
        $this->query = $this->mysqli->prepare($query);
        if ($this->query == false) {
            throw (new SQLExceptions)->queryError($this->mysqli->error);
        }
    }
    
    /**
     * Binds params with SQL statement.
     * @param array $params
     * @return void
     */
    public function bindParams(array $params) {
        $valueTypeString = "";
        foreach($params as $key => $param) {
            $varType = gettype($param);
            switch ($varType) {
                case "string":
                    $valueTypeString .= "s";
                    break;
                case "double":
                    $valueTypeString .= "d";
                    break;
                case "integer":
                    $valueTypeString .= "i";
                    break;
                case "BLOB":
                    $valueTypeString .= "b";
                    break;
                case "boolean":
                    $valueTypeString .= "i";
                    $params[$key] = intval($param);
                    break;
            }
        }
        $value = array_values($params);
        try {
            $this->query->bind_param($valueTypeString, ...$value);
        } catch (Exception $e) {
            echo $e->getMessage();
            echo $this->mysqli->error;
        }
    }
    
    /**
     * Execute SQL statement and set execution results to variables.
     * @return void
     */
    public function execute() {
        $this->query->execute();

        $this->result = $this->query->get_result();
        $this->affectedRows = $this->mysqli->affected_rows;
        $this->insertId = $this->mysqli->insert_id;
        $this->sqlErrNo = $this->mysqli->errno;
        $this->sqlError = $this->mysqli->error;
    }
    
    /**
     * Fetch results from mysqli_result
     * @param int $mode
     * @return array
     */
    public function fetchResults(int $mode = MYSQLI_ASSOC) {
        return $this->result->fetch_all($mode);
    }
    
    /**
     * Get row count returned basing the statement criteria.
     * @param string $table
     * @param array $where
     * @param array $columns
     * @param string $cond
     * @return int
     */
    public function getRowCount(string $table, array $where, array $columns = ['*'], string $cond = 'AND') {
        $columnStr = implode(',', $columns);
        $whereStr = '';
        $whereLength = count($where);
        $i = 0;
        foreach ($where as $key => $value) {
            $i++;
            $whereStr .= "$key = ? ";
            if ($i < $whereLength) {
                $whereStr .= "$cond ";
            }
        }
        $query = "SELECT COUNT($columnStr) AS cnt FROM $table WHERE $whereStr";
        $this->prepareQuery($query);
        $this->bindParams($where);
        $this->execute();
        $result = $this->fetchResults();
        
        return $result['0']['cnt'];
    }
}
