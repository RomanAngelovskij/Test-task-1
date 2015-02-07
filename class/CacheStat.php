<?php
namespace StatTest;
class CacheStat{

	/**
     * Объект класса для работы с кешем, наследующий интерфейс Cache
     *
     * @var \StatTest\interfaces\Cache
     */
	private $__cacheServer;

	public function __construct($cacheServer = 'redis'){
		$cacheClass = '\StatTest\Cache_' . $cacheServer;
		$CacheServer = new $cacheClass();
		
		$this->__registerCacheServer($CacheServer);
	}
	
	/**
	 * Добавление данных о визите в кеширующий сервер
	 *
	 * @param int $partnerID
	 * @param int|string $ip
	 *
	 * @return bool
	 */
	public function addCache($partnerID, $ip){
		if ($this->__cacheServer->isConnected() === false){
			return false;
		}
		
		$this->__cacheServer->add($partnerID, $ip);
		
		return true;
	}
	
	/**
	 * Получение данных о визитах в кеширующем сервере,
	 * для определенного пользователя
	 *
	 * @param int $partnerID
	 *
	 * @return bool|array
	 */
	public function getCache($partnerID){
		if ($this->__cacheServer->isConnected() === false){
			return false;
		}
		
		return $this->__cacheServer->get($partnerID);
	}
	
	/**
	 * Очистка кеша партнера
	 *
	 * @param int $partnerID
	 *
	 * @return bool
	 */
	public function clearCache($partnerID){
		if ($this->__cacheServer->isConnected() === false){
			return false;
		}
		
		$this->__cacheServer->clear($partnerID);
		
		return true;
	}
	
	/**
	 * Регистрация класса, работающего с кешуреющим сервером
	 *
	 * @param mixed $cacheServer Объект наследующий методы интерфейса \StatTest\interfaces\Cache
	 */
	private function __registerCacheServer(\StatTest\interfaces\Cache $cacheServer){		
		$this->__cacheServer = $cacheServer;
	}
}
?>