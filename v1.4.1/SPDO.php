<?php
/**
 * Created by PhpStorm.
 * User: Julien
 * Date: 17/04/2015
 * Time: 22:59
 */

class SPDO
{
    private $PDOInstance = null;
    private static $instance = null;

    private function __construct()
    {
        $inicontent = parse_ini_file("../../../db_connection_login.ini", true);

        if (isset($inicontent[$_SERVER['SERVER_NAME']])) {
            $SQL_HOST = $inicontent[$_SERVER['SERVER_NAME']]['SQL_HOST'];
            $SQL_USER = $inicontent[$_SERVER['SERVER_NAME']]['SQL_USER'];
            $SQL_PASS = $inicontent[$_SERVER['SERVER_NAME']]['SQL_PASS'];
            $SQL_DTB = $inicontent[$_SERVER['SERVER_NAME']]['SQL_DTB'];
        } else {
            $SQL_HOST = $inicontent['localhost']['SQL_HOST'];
            $SQL_USER = $inicontent['localhost']['SQL_USER'];
            $SQL_PASS = $inicontent['localhost']['SQL_PASS'];
            $SQL_DTB = $inicontent['localhost']['SQL_DTB'];
        }

        try {
            $this->PDOInstance = new PDO('mysql:dbname='.$SQL_DTB.';host='.$SQL_HOST, $SQL_USER, $SQL_PASS);
        } catch (PDOException $e) {
            die("PDO CONNECTION ERROR: " . $e->getMessage() . "<br/>");
        }
    }

    public static function getInstance()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new SPDO();
        }
        return self::$instance;
    }

    /**
     * Initiates a transaction
     *
     * @return bool
     */
    public function beginTransaction() {
        return $this->PDOInstance->beginTransaction();
    }

    /**
     * Commits a transaction
     *
     * @return bool
     */
    public function commit() {
        return $this->PDOInstance->commit();
    }

    /**
     * Fetch the SQLSTATE associated with the last operation on the database handle
     *
     * @return string
     */
    public function errorCode() {
        return $this->PDOInstance->errorCode();
    }

    /**
     * Fetch extended error information associated with the last operation on the database handle
     *
     * @return array
     */
    public function errorInfo() {
        return $this->PDOInstance->errorInfo();
    }

    /**
     * Execute an SQL statement and return the number of affected rows
     *
     * @param string $statement
     * @return number of affected rows
     */
    public function exec($statement) {
        return $this->PDOInstance->exec($statement);
    }

    /**
     * Retrieve a database connection attribute
     *
     * @param int $attribute
     * @return mixed
     */
    public function getAttribute($attribute) {
        return $this->PDOInstance->getAttribute($attribute);
    }

    /**
     * Return an array of available PDO drivers
     *
     * @return array
     */
    public function getAvailableDrivers(){
        return $this->PDOInstance->getAvailableDrivers();
    }

    /**
     * Returns the ID of the last inserted row or sequence value
     *
     * @param string $name Name of the sequence object from which the ID should be returned.
     * @return string
     */
    public function lastInsertId($name = '') {
        if(empty($name)) {
            $id = $this->PDOInstance->lastInsertId();
        } else {
            $id = $this->PDOInstance->lastInsertId($name);
        }
        return $id;
    }

    /**
     * Prepares a statement for execution and returns a statement object
     *
     * @param string $statement A valid SQL statement for the target database server
     * @param array|bool $driver_options Array of one or more key=>value pairs to set attribute values for the PDOStatement obj
     * returned
     * @return PDOStatement
     */
    public function prepare ($statement, $driver_options=false) {
        if(!$driver_options) $driver_options=array();
        return $this->PDOInstance->prepare($statement, $driver_options);
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     *
     * @param string $statement
     * @return PDOStatement
     */
    public function query($statement) {
        return $this->PDOInstance->query($statement);
    }

    /**
     * Execute query and return all rows in assoc array
     *
     * @param string $statement
     * @return array
     */
    public function queryFetchAllAssoc($statement) {
        return $this->PDOInstance->query($statement)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Execute query and return one row in assoc array
     *
     * @param string $statement
     * @return array
     */
    public function queryFetchRowAssoc($statement) {
        return $this->PDOInstance->query($statement)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Execute query and select one column only
     *
     * @param string $statement
     * @return mixed
     */
    public function queryFetchColAssoc($statement) {
        return $this->PDOInstance->query($statement)->fetchColumn();
    }

    /**
     * Quotes a string for use in a query
     *
     * @param string $input
     * @param int $parameter_type
     * @return string
     */
    public function quote ($input, $parameter_type=0) {
        return $this->PDOInstance->quote($input, $parameter_type);
    }

    /**
     * Rolls back a transaction
     *
     * @return bool
     */
    public function rollBack() {
        return $this->PDOInstance->rollBack();
    }

    /**
     * Set an attribute
     *
     * @param int $attribute
     * @param mixed $value
     * @return bool
     */
    public function setAttribute($attribute, $value  ) {
        return $this->PDOInstance->setAttribute($attribute, $value);
    }
}