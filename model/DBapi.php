<?php
namespace model\dbapi;

interface api{
	public function get($name, $params);
	public function update($name, $params);
	public function insert($name, $params);
	public function delete($name, $id);
}
abstract class PDOquery{
	protected $db;
	function __construct($dbtype, $host, $dbname, $login, $passwd){
		try{
				$this->db = new \PDO($dbtype . ':host=' . $host . ';dbname=' . $dbname, $login, $passwd);
			} 
			catch (PDOException $e) {
				echo $e->getMessage();
			}
	}
	protected function makeInsert($query){
		$this->db->exec($query);
		return $this->db->lastInsertId();
	}
	protected function makeUpdate($query){
		return $this->db->exec($query);
	}
	public function makeSelect($query){
		$res = $this->db->query($query);
		if ($res){
			return $res->fetchAll(\PDO::FETCH_ASSOC);
		}
		else{
			return FALSE;
		}
	}
	public function makeQuery($query){
		$res = $this->db->query($query);
		
		try{
			return $res->fetchAll(\PDO::FETCH_ASSOC);
		}
		catch (PDOException $e){
			return ['nodata'];
		}
	}
	public function getSafeParam($param){
		return $this->db->quote($param);
	}
}
class DBapi extends PDOquery implements api{

	function __construct($dbtype, $host, $dbname, $login, $passwd){
		parent::__construct($dbtype, $host, $dbname, $login, $passwd);
	}
	public function get($name, $params){
		$query = 'SELECT * FROM ' . $name;
		if ($params){
			$query .= ' WHERE ';
			$countParam = count($params);
			$i = 1;
			foreach($params as $key=>$value){
				if ($countParam == $i){
					$query .= $key . ' = ' . $this->db->quote($value);
				}
				else{
					$query .= $key . ' = ' . $this->db->quote($value) . ' AND ';
				}
				$i++;
			}

		}

		$res = parent::makeSelect($query);
		return $res;
	}
	public function update($name, $params){
		$query = 'UPDATE ' . $name . ' SET ';
		

		if (isset($params['id'])){
			if (empty($params['id'])){
				return FALSE;
			}
			else{
				if (!intval($params['id'])){
					return FALSE;
				}
			}
		}
		else{
			return FALSE;
		}
		$countParam = count($params) - 1;
		$i = 1;

		foreach($params as $key=>$value){
			if (strtolower($key) !== 'id'){
				$value = $this->db->quote($value);
				if ($countParam == $i){
					$query .= $key . ' = ' . $value;
				}
				else{
					$query .= $key . ' = ' . $value . ',';
				}
				$i++;
			}
			else{
				$wh = ' WHERE id = ' .  $this->db->quote($value);
			}

			
		}

		$query .= $wh;
		
		return parent::makeUpdate($query);
		
	}
	public function insert($name, $params){
		$query = 'INSERT INTO ' . $name . ' ';
		if ($params){
			if (isset($params['id'])){
				return false;
			}
			$i = 1;
			$fields = '';
			$values = '';
			$countParam = count($params);
			foreach ($params as $key=>$value){
				if ($i == $countParam){
					$fields .= $key;
					$values .= $this->db->quote($value);
				}
				else{
					$fields .= $key . ', ';
					$values .= $this->db->quote($value) . ', ';
				}

				$i++;
			}

		}
		$query .= '(' . $fields . ')' . ' VALUES ' . '(' . $values . ')';
		
		return parent::makeInsert($query);

	}
	public function delete($name, $id){
		$query = 'DELETE FROM ' . $name . ' WHERE id = ' .  $this->db->quote($id);
		return parent::makeUpdate($query);
	}
	public function getHIstory(){
		return parent::makeQuery('SELECT * FROM payment_history LEFT JOIN users ON payment_history.userID = users.ID');
	}



}
