<?php
namespace StatTest;
class Cache_redis implements \StatTest\interfaces\Cache{
	
	/**
     * Префикс ключа для сохранения в кеше
     * 
     * @var mixed
     */
	private $__keyPrefix = 'statistic';
	
	/**
     * Объект класса из библиотеки для работы с Redis
     * 
     * @var mixed
     */
	private $__Server;
	
	/**
     * Массив параметров для подключения и работы с Redis
     * 
     * @var array
     */
	private $__Options;
	
	public function __construct(){
		$this->__Options = array(
			'servers'   => array(
				array('host' => '127.0.0.1', 'port' => 6379)
			)
		);

		require_once APP_PATH . '/lib/Rediska.php';
		$this->__Server = new \Rediska($this->__Options);
	}
	
	/**
	 * Добавление записи в кеш Редиса для определенного партнера
	 *
	 * @param int $partnerID
	 * @param int $ip
	 *
	 * @return bool
	 */
	public function add($partnerID, $ip){
		$key = $this->__keyPrefix . ':' . $partnerID;
		
		$Key = new \Rediska_Key($key);
		
		// Получаем старое значение
		$Stat = $Key->getValue();
		
		// К старому значению добавляем новое
		$Stat[] = array('ip' => $ip, 'dateAdd' => time());
		
		$Key->setValue($Stat);
		// TTL 30 дней. Данные из кэша добавляются в основную БД либо при заходе юзера
		// и по крону. Если прошло больше 30 дней - что-то пошло не так и их следует удалить
		$Key->expire(60*60*24*30);
		
		return true;
	}
	
	/**
	 * Получение статистики их кеша Редиса для определенного партнера
	 *
	 * @param int $partnerID
	 *
	 * @return array
	 */
	public function get($partnerID){
		$Data = array();
		$key = $this->__keyPrefix . ':' . $partnerID;
		$Key = new \Rediska_Key($key);

		return $Key->getValue();
	}
	
	/**
	 * Очистка кеша партнера
	 *
	 * @param int $partnerID
	 *
	 * @return bool
	 */
	public function clear($partnerID){
		$key = $this->__keyPrefix . ':' . $partnerID;
		$Key = new \Rediska_Key($key);
		$result = $Key->delete();

		return true;
	}
	
	/**
	 * Проверка работает ли Редис
	 *
	 * @return bool
	 */
	public function isConnected(){
		try {
			@$this->__Server->ping();
			return true;
		} catch (\Rediska_Exception $e) {
			// Можно добавлять в лог $e->getMessage();
			return false;
		}
    }
}
?>