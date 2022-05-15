<?php

class Database {
	private static $instance = null;

	private $connection;
	private $database;

	private function __construct($database) {
		$this->connection = new PDO(SQL_TYPE . ':host=' . SQL_HOST . ';dbname=' . $database, SQL_USER, SQL_PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
	}

	public static function Sql($database = SQL_DB) {
		if(!isset(self::$instance) || self::$instance->database != $database) {
			self::$instance = new Database($database);
		}

		self::$instance->database = $database;

		return self::$instance;
	}

	public function doQuery($sql, $params = []) {
		$query = $this->connection->prepare($sql);

		if(count($params)) {
			foreach($params as $key => $value) {
				$query->bindValue($key + 1, $value);
			}
		}

		$query->execute();
		
		return $query;
	}

	public function doSelect($sql, $params = []) {
		$query = $this->doQuery($sql, $params);
		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

	public function doInsert($sql, $params = []) {
		$query = $this->doQuery($sql, $params);
		return $this->connection->lastInsertId();
	}

	public function doUpdate($sql, $params = []) {
		$query = $this->doQuery($sql, $params);

		return $query->rowCount();
	}
}

?>
