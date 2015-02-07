<?php
namespace StatTest\lib;

class DB{
	
	private $__db;
	
	private $__sth;
	
	public function __construct($dbHost, $dbName, $dbUser, $dbPassword){
		try{
			$this->__db = new \PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName, $dbUser, $dbPassword);
			
			//$this->__db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
			$this->__db->query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
		} catch (\PDOException $e) {
			echo "DB error: " . $e->getMessage() . "<br>";
			die();
		}
	}
	
	public function __call($name, $args)
    {
        return $this->__db->$name($args[0]);
    }
	
	public function query($SQL, $Bind = null){
		$executeResult = $this->_processSQL($SQL, $Bind);

		$Result = $this->__sth->fetchAll(\PDO::FETCH_ASSOC);

		return $Result;
	}
	
	public function selectCell($SQL, $Bind = null, $cellName = null, $rowNum = 0){
		$this->_processSQL($SQL, $Bind);
		
		$Result = $this->__sth->fetchAll(\PDO::FETCH_ASSOC);

		if ($cellName == null){
			return isset($Result[$rowNum][array_keys($Result[$rowNum])[0]]) ? $Result[$rowNum][array_keys($Result[$rowNum])[0]] : null;
		} else {
			return isset($Result[$rowNum][$cellName]) ? $Result[$rowNum][$cellName] : null;
		}

	}
	
	/*
	 * Возвращает 1 запись из результирующего набора
	 *
	 */
	public function selectRow($SQL, $Bind = null, $type='array', $rowNum = 0){
		$this->_processSQL($SQL, $Bind);
		if ($type == 'object'){
			$Result = $this->__sth->fetchAll(\PDO::FETCH_OBJ);
		} else {
			$Result = $this->__sth->fetchAll(\PDO::FETCH_ASSOC);
		}
		
		$Result = isset($Result[$rowNum]) ? $Result[$rowNum] : null;
		
		return $Result;
	}
	
	public function selectCol($SQL, $Bind = null, $colName = 1, $type='array'){
		$this->_processSQL($SQL, $Bind);
		
		$this->__sth->bindColumn($colName, $column);
		while ($Row = $this->__sth->fetch(\PDO::FETCH_BOUND)){
			$Result[] = $column;
		}
		
		return isset($Result) ? $Result : null;
	}
	
	public function insert_id(){
		return $this->__db->lastInsertId();
	}
	
	private function _processSQL($SQL, $Bind){
	
		$SQL = $this->_processParamsArrays($SQL, $Bind);

		$this->__sth = $this->__db->prepare($SQL);
		if (!empty($Bind)){
			$i = 1;
			foreach ($Bind as $val){
				$this->__sth->bindValue($i, $val);
				$i++;
			}
		}
		
		return $this->__sth->execute();
	}
	
	private function _processParamsArrays($SQL, &$Bind){
	
		preg_match_all("|\?a|", $SQL, $Params, PREG_OFFSET_CAPTURE);
		
		$i = 0;
		$increaseStr = 0;
		foreach($Params[0] as $Param){
			if ($Param[0] == '?a'){
				if (is_array($Bind[$i])){
					$newVal = implode(',', $Bind[$i]);
					$SQL = substr_replace($SQL, $newVal, $Param[1]+$increaseStr, strlen($Param[0]));
					$increaseStr += strlen($newVal)-strlen($Param[0]);
					
					unset($Bind[$i]);
				}
			}
			
			$i++;
		}

		return $SQL;
	}
}
?>