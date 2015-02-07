<?php
namespace StatTest;
class Config{
	protected static $_instance;
	
	private $__configFile;
	
	private function __construct(){
		$this->__configFile = $_SERVER['DOCUMENT_ROOT'] . '/config.php';
		$this->_Config = $this->__readConfigFile();
	}
	
	private function __clone(){
    }
	
	public function __get($key){
		return isset($this->_Config[$key]) ? $this->_Config[$key] : null;
	}
	
	public static function getInstance() {
		if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
	
	private function __readConfigFile(){
		if (!file_exists($this->__configFile)){
			throw new Exception('Config file not found');
		}
		
		require_once $this->__configFile;
		
		return $Config;
	}
}
?>