<?php
namespace StatTest;
class PartnerStat{

	/**
     * ID партнера
     *
     * @var int
     */
	private $__partnerID;
	
	/**
     * Объект класса для работы с БД
     *
     * @var \StatTest\lib\DB
     */
	private $__db;
	
	/**
     * Часть SQL выборки для поиска всего диапазона IP.
	 * Устанавливается методом setSubnetsSQL
     *
     * @var string
     */
	private $__subnetsSQL = "`ip` IS NOT NULL";
	
	public function __construct($partnerID, \StatTest\lib\DB $db ){
		$this->__partnerID = $partnerID;
		
		$this->__db = $db;
	}
	
	/**
	 * Выборка уникальных посетителей с разбивкой по дням
	 *
	 * @param int $dateFrom При выборке диапазона дат, начальное значение в timestamp
	 * @param int $dateTo	При выборке диапазона дат, конечное значение в timestamp
	 *
	 * @return array
	 */
	public function uniqueVisitors($dateFrom = 0, $dateTo = 0){
		$dateTo = $dateTo == 0 ? time() : $dateTo;
													
		$Result = $this->__db->query("SELECT `dateAdd`, COUNT(DISTINCT(ip)) AS `cnt`
												FROM `visits_log` 
												WHERE `partnerID` = ? 
												AND (`dateAdd` BETWEEN ? AND ?) 
												AND " . $this->__subnetsSQL . "
												GROUP BY DATE(FROM_UNIXTIME(`dateAdd`))", 
												array(
														$this->__partnerID,
														$dateFrom,
														$dateTo
													));

		return $Result;
	}
	
	/**
	 * Выборка количества просмотров с разбивкой по дням
	 *
	 * @param int $dateFrom При выборке диапазона дат, начальное значение в timestamp
	 * @param int $dateTo	При выборке диапазона дат, конечное значение в timestamp
	 *
	 * @return array
	 */
	public function visitors($dateFrom = 0, $dateTo = 0){
		$dateTo = $dateTo == 0 ? time() : $dateTo;
		$Result = $this->__db->query("SELECT `dateAdd`, COUNT(*) AS `cnt`
												FROM `visits_log` 
												WHERE `partnerID` = ? 
												AND (`dateAdd` BETWEEN ? AND ?)
												AND " . $this->__subnetsSQL . "
												GROUP BY  DATE(FROM_UNIXTIME(`dateAdd`))", 
												array(
														$this->__partnerID,
														$dateFrom,
														$dateTo
													));

		return $Result;
	}
	
	/**
	 * Выборка всех посетителей за день, с разбивкой по времени визита
	 *
	 * @param int $day Timestamp равная 00:00 выбераемого дня
	 *
	 * @return array
	 */
	public function visitorsByTime($day){
		$endDay = $day+86400;
		$Result = $this->__db->query("SELECT HOUR(FROM_UNIXTIME(`dateAdd`)) AS `hour`, COUNT(*) AS `cnt`
												FROM `visits_log` 
												WHERE `partnerID` = ? 
												AND (`dateAdd` BETWEEN ? AND ?)
												AND " . $this->__subnetsSQL . "
												GROUP BY  HOUR(FROM_UNIXTIME(`dateAdd`))", 
												array(
														$this->__partnerID,
														$day,
														$endDay
													));

		return $Result;
	}
	
	/**
	 * Выборка уникальных посетителей за день, с разбивкой по времени визита
	 *
	 * @param int $day Timestamp равная 00:00 выбераемого дня
	 *
	 * @return array
	 */
	public function uniqueVisitorsByTime($day){
		$endDay = $day+86400;
		$Result = $this->__db->query("SELECT HOUR(FROM_UNIXTIME(`dateAdd`)) AS `hour`, COUNT(DISTINCT(ip)) AS `cnt`
												FROM `visits_log` 
												WHERE `partnerID` = ? 
												AND (`dateAdd` BETWEEN ? AND ?)
												AND " . $this->__subnetsSQL . "
												GROUP BY HOUR(FROM_UNIXTIME(`dateAdd`))", 
												array(
														$this->__partnerID,
														$day,
														$endDay
													));

		return $Result;
	}
	
	/**
	 * Пакетная вставка в основную таблицу статистики партнера.
	 * Такой вариант на тестах показал гораздо лучшие результаты,
	 * чем подготовленные выражения. (100 тыс. записей менее 2 сек.)
	 *
	 * @param array $Data Многомерный массив в формате
	 *					  array("0" => array(
	 *								'ip' => integer IP adress,
	 *								'dateAdd' => timestamp
	 *							))
	 *
	 * @return int ID последней добавленной записи
	 */
	public function insertBatch($Data){
		if (empty($Data)){
			return false;
		}
		
		$query = 'INSERT INTO `visits_log` (`partnerID`, `ip`, `dateAdd`) VALUES ';
		foreach($Data as $Row) {
			$queryParts[] = "('" . $this->__partnerID . "', '" . (int) $Row['ip'] . "', '" . (int) $Row['dateAdd'] . "')";
		}
		$query .= implode(',', $queryParts);
		$this->__db->query($query);
		
		return $this->__db->insert_id();
	}
	
	/**
	 * Формирование условия при выборке в заданных диапазонах IP
	 *
	 * @param array $Subnets Многомерный массив в формате
	 *						 array("0" => array(
	 *								0 => start IP (integer)
	 *								1 => finish IP (integer)
	 *							))
	 *
	 * @return bool
	 */
	public function setSubnetsSQL($Subnets){
		if (empty($Subnets)){
			return false;
		}
		
		foreach ($Subnets as $Subnet){
			$SQL[] = "`ip` BETWEEN " . (int) $Subnet[0] . " AND " . (int) $Subnet[1];
		}
		
		$this->__subnetsSQL = "(" . implode(' OR ', $SQL) . ")";
		return true;
	}
	
	/**
	 * Формирование массива, где ключами являются часы (0-23), значениями кол-во посетителей
	 *
	 * @param array $Stat Многомерный массив в формате
	 *					  array("0" => array(
	 *								'hour' => 0-23
	 *								'cnt'  => кол-во посетителей
	 *							))
	 *
	 * @return array
	 */
	public function fillTimeline($Stat){
		$Timeline = array_fill_keys(range(0, 23), 0);
		
		if (!empty($Stat)){
			foreach ($Stat as $Row){
				if (isset($Row['hour'])){
					$Timeline[$Row['hour']] = (int) $Row['cnt'];
				}
			}
		}
		
		return $Timeline;
	}
}
?>