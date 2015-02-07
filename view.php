<?php
$startTime = microtime(true);
$startMemory = memory_get_usage(true);

define (APP_PATH, $_SERVER['DOCUMENT_ROOT'] . '/');

require_once APP_PATH . 'common.php';

$partnerID = (int) $_GET['partner_id'];

if ($partnerID == 0){
	exit ('Incorrect partner ID');
}

$Config = \StatTest\Config::getInstance();

$db = new \StatTest\lib\DB($Config->db['host'], $Config->db['name'], $Config->db['user'], $Config->db['password']);

$CacheStat = new \StatTest\CacheStat($Config->mainCache);

$PartnerStat = new \StatTest\PartnerStat($partnerID, $db);

$StatInCache = $CacheStat->getCache($partnerID);

// Если в кеширующем сервере есть данные о визитах, добавляем их в основную базу
if (!empty($StatInCache)){
	$PartnerStat->insertBatch($StatInCache);

	$CacheStat->clearCache($partnerID);
}

$AllowedIP = json_decode($Config->allowedIP, true);

$operatorID = 1;
foreach ($AllowedIP as $Subnets){
	$Operators[$operatorID]['name'] = $Subnets['name'];
	foreach ($Subnets['subnets'] as $range){
		list($startIP, $finishRange) = explode('/', $range);
		$finishIP = preg_replace("|\.[\d]+$|", '.' . $finishRange, $startIP);
		$Operators[$operatorID]['subnets'][] = array(ip2long($startIP), ip2long($finishIP));
	}
	$operatorID++;
}

$Subnets = (isset($_GET['operatorID']) && $_GET['operatorID'] > 0) ? $Operators[$_GET['operatorID']]['subnets'] : array();
$PartnerStat->setSubnetsSQL($Subnets);

if (isset($_GET['fltTimestamp']) && $_GET['fltTimestamp'] > 0){
	$timestamp = ceil($_GET['fltTimestamp']/1000);
	//Посещения за день
	$Visitors = $PartnerStat->visitors($timestamp, ($timestamp+86400));
	//Уникальные посещения за день
	$UniqueVisitors = $PartnerStat->uniqueVisitors($timestamp, ($timestamp+86400));
	//Посещения за день по времени
	$VisitorsByTime = $PartnerStat->fillTimeline($PartnerStat->visitorsByTime($timestamp));
	//Уникальные посещения за день по времени
	$UniqueVisitorsByTime = $PartnerStat->fillTimeline($PartnerStat->uniqueVisitorsByTime($timestamp));
} else {
	$Visitors = $PartnerStat->visitors();
	$UniqueVisitors = $PartnerStat->uniqueVisitors();
}

include APP_PATH . 'view/stat.php';

$finishTime = microtime(true);
$finishMemory = memory_get_usage(true);
echo '<hr>';
echo ' Script execution time: ' . ($finishTime-$startTime) . ' sec.<br>';
echo 'Memory usage: ' . (($finishMemory-$startMemory)/1048576) . 'MB';
?>