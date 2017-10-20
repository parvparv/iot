<?php

require_once 'Applications/ard_iot/Gateway_c.php';

 
use GatewayClient\Gateway;

/**
 *====这个步骤是必须的====
 *这里填写Register服务的ip（通常是运行GatewayWorker的服务器ip）和端口
 *注意Register服务端口在start_register.php中可以找到（chat默认是1236）
 *这里假设GatewayClient和Register服务都在一台服务器上，ip填写127.0.0.1
 **/
 Gateway::$registerAddress = '127.0.0.1:57698';


// 以下是调用示例，接口与GatewayWorker环境的接口一致
// 注意除了不支持sendToCurrentClient和closeCurrentClient方法
// 其它方法都支持

$uid=  $_GET['uid'];
$msg=  $_GET['msg'];
 
 
 if(Gateway::isUidOnline($uid)){
 	
	 $client = Gateway::getClientIdByUid($uid); 
	 Gateway::sendToClient($client[0],$msg."\n");
	 
 }else{
 	
	 echo $uid.'不在线';
	
 }
 


/*
Gateway::sendToClient($client_id, $data);
Gateway::closeClient($client_id);
Gateway::isOnline($client_id);
Gateway::bindUid($client_id, $uid);
Gateway::isUidOnline($uid);
Gateway::getClientIdByUid($client_id);
Gateway::unbindUid($client_id, $uid);
Gateway::sendToUid($uid, $dat);
Gateway::joinGroup($client_id, $group);
Gateway::sendToGroup($group, $data);
Gateway::leaveGroup($client_id, $group);
Gateway::getClientCountByGroup($group);
Gateway::getClientSessionsByGroup($group);
Gateway::getAllClientCount();
Gateway::getAllClientSessions();
Gateway::setSession($client_id, $session);
Gateway::updateSession($client_id, $session);
Gateway::getSession($client_id);
*/
	
	?>