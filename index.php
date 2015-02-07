<?php
$startTime = microtime(true);
$startMemory = memory_get_usage(true);

define (APP_PATH, $_SERVER['DOCUMENT_ROOT'] . '/');

require_once APP_PATH . 'common.php';

$partnerID = (int) $_GET['partner_id'];

if ($partnerID == 0){
	exit ('Incorrect partner ID');
}

$currentIP = ip2long($_SERVER['REMOTE_ADDR']);

$Config = \StatTest\Config::getInstance();
// Читаем из конфига список операторов и их IP для логирования
$AllowedIP = json_decode($Config->allowedIP, true);

$skip = true;
foreach ($AllowedIP as $Subnets){
	foreach ($Subnets['subnets'] as $range){
		list($startIP, $finishRange) = explode('/', $range);
		$finishIP = preg_replace("|\.[\d]+$|", '.' . $finishRange, $startIP);

		// Если текущий IP попал в диапозон для логирования
		if ($currentIP >= ip2long($startIP) && $currentIP <= ip2long($finishIP)){
			$skip = false;
			break 2;
		}
	}
}

if ($skip === false){
	$CacheStat = new \StatTest\CacheStat($Config->mainCache);
	$result = $CacheStat->addCache($partnerID, $currentIP);
	// TODO: если $result === false создавать новый кеш с резервным сервером, который указан в $Config->reserveCache
}

echo '<hr>';
$finishTime = microtime(true);
$finishMemory = memory_get_usage(true);
echo ' Script execution time: ' . ($finishTime-$startTime) . ' sec.<br>';
echo 'Memory usage: ' . (($finishMemory-$startMemory)/1048576) . 'MB';
?>