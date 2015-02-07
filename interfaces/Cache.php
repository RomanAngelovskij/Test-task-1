<?php
namespace StatTest\interfaces;
interface Cache{
	public function isConnected();
	
	public function add($partnerID, $ip);
	
	public function get($partnerID);
	
	public function clear($partnerID);
}
?>