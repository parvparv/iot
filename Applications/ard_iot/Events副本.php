<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;
use Workerman\Lib\Timer;
/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
  private static function jiamisuanfa($mi,$uid){//加密算法计算 
		//
			Gateway::sendToCurrentClient('jiami:'.$mi.'_'.$uid);
		if(!$mi){
			Gateway::sendToCurrentClient('null mi');
		}
		if(!$uid){
			Gateway::sendToCurrentClient('null uid');
		}
 
		
		for($i=0;$i<8;$i++){
			if(!isset($mi{$i})){
				Gateway::sendToCurrentClient('no mi'.$i);
			} 
		}
		
		for($i=0;$i<8;$i++){
			if(!isset($uid{$i})){
				Gateway::sendToCurrentClient('no uid'.$i);
			} 
		}
		
		 
	 	if(!$mi || !isset($mi{5})) return false;
	 	if(!$uid ||   ( $uid<100 && !isset($uid{2} ) )  ) return false;
		 
		Gateway::sendToCurrentClient('chenggong mima');
		return true;
	}
	  
	  
   public static function onConnect($client_id)
      {
          // 连接到来后，定时30秒关闭这个链接，需要30秒内发认证并删除定时器阻止关闭连接的执行
          $_SESSION['auth_timer_id'] = Timer::add(30, function($client_id){
            
	  	  	  Gateway::sendToClient($client_id,'not good time, kick out you' );
			
				//  Gateway::closeClient($client_id);
          }, array($client_id), false);
      }
		
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message) {
	  // 向所有人发送 Gateway::sendToAll("$client_id said $message");
	  Gateway::sendToAll("$client_id said $message");
	 
 		//消息 json 格式
		// "type" 1 登录  2 登出  3 心跳 4 普通消息 
		//  "mi"  加密算法密码,文本,
		//  "msg" 消息内容
		//  "uid"  用户id 数字类型 100 起步
		  $msg_json = json_decode($message, true);
		  
	     if(json_last_error() != JSON_ERROR_NONE){
	     	//Gateway::closeCurrentClient();  //非正常数据 ,踢出 
	  	  Gateway::sendToClient($client_id,'not good json, kick out you' );
			return ;
		  }else if(!isset($msg_json['type']) ){
  	     	//Gateway::closeCurrentClient();  //非正常数据 ,踢出 
  	  	  Gateway::sendToClient($client_id,'not good json, kick out you' );
  			return ;
		  }
		  
		   
		  //判断当前发信息的人 是否 已经有了 uid 
		  
		$getc_by_uid =  Gateway::getClientIdByUid($_SESSION['uid']);
  Gateway::sendToClient($client_id,'uid:'.$_SESSION['uid'].'_'. $getc_by_uid[0]);		  
		   if (
			isset($_SESSION['uid']) && 
			$_SESSION['uid']== $msg_json['uid'] 			
			) {
		   	//已经被认证过了!  或是 发 状态信息 或是 退出  
 	  	Gateway::sendToClient($client_id,'have uid!' );
			 
	  		 if($msg_json['type'] == 3){ //心跳   
		  	  Gateway::sendToClient($client_id,'shoudao xintiao' );
	
	  		 } else if($msg_json['type'] == 4){ //普通消息
 
	  	 Gateway::sendToClient($client_id,'putong xiaoxi:'.$msg_json['msg'] );
			 
	  		 }else if($msg_json['type'] == 5){ // uid 在线询问
				 //{"type":5,"mima":"7c93Def","touid":101}
				 //询问某uid是否在线
 
				 
				 
	  	 Gateway::sendToClient($client_id,'putong xiaoxi:'.$msg_json['msg'] );
			 
	  		 }  else if($msg_json['type'] == 6){ //网页推送消息
				 //{"type":6,"mima":"7c93Def","touid":101,"msg":5}
				 //类型:5 密码,向哪个uid,发送内容
				 
				 //向某 cid uid 转发某消息!.....! 
				 //多久未回复,说明 错误............
				 //发送重要消息前,收费前,发送探寻消息,探寻成功,方可付费
				 //仅url 为本机的  才允许继续
	 Gateway::sendToAll("$client_id  type5 : addr: ".$_SERVER['REMOTE_ADDR'].' fuwuqiaddr:' .$_SERVER['GATEWAY_ADDR'] );
	 //if($_SERVER['REMOTE_ADDR']==$_SERVER['GATEWAY_ADDR']){
		 //允许 操作
		 //}
				 
				 
	  	 Gateway::sendToClient($client_id,'putong xiaoxi:'.$msg_json['msg'] );
			 
	  		 }  else if($msg_json['type'] == 2){ //退出
		  	  	Gateway::sendToClient($client_id,'tuichu' );
		
 if(isset($msg_json['mi']) &&  isset($msg_json['uid']) && self::jiamisuanfa($msg_json['mi'],$msg_json['uid'])){//加密算法 通过 
				 	  	Gateway::sendToClient($client_id,'tuichu ok' );
		
	    Gateway::sendToClient($client_id,'good jiami, log out you' );
		
	  					}else{ 
	  		  			//Gateway::closeClient($client_id);
		 Gateway::sendToClient($client_id,'not good jiami, kick out you' );
			
	  					} 
	  		 }else{ //类型不在册,踢出
						 	//Gateway::closeClient($client_id);
       Gateway::sendToClient($client_id,'not good type, kick out you' );
			
					} 
				
				
				
		   }else{
		   	//没被认证过的,只允许发认证消息
		 	  	Gateway::sendToClient($client_id,'have no uid' );
				
				  if($msg_json['type'] != 1){ //认证消息
				 	//Gateway::closeClient($client_id); 
		Gateway::sendToClient($client_id,'not uid and no renzheng, kick out you' );
			 	
				  }else{
				  	
  	  		 	  	Gateway::sendToClient($client_id,'no uid renzheng' );
			
			
		 if(isset($msg_json['mi']) &&  isset($msg_json['uid']) && self::jiamisuanfa($msg_json['mi'],$msg_json['uid'])){//加密算法 通过 
				
								
  						 Gateway::sendToClient($client_id,'renzheng ok' );
			
  							Gateway::bindUid($client_id, $msg_json['uid']);
  							$_SESSION['uid']=$msg_json['uid'] ;
  							Timer::del($_SESSION['auth_timer_id']);
							
							
			$arr=Gateway::getClientIdByUid($msg_json['uid']);
			$arrsize=count($arr) ;	
			for($i=0;$i<$arrsize;$i++){
				$sendt.=$arr($i);
			}
						
		  	  Gateway::sendToClient($client_id,'uid have:'.$sendt );
			
							
							
  							}else{ 
  						  	//Gateway::closeClient($client_id);
  				 	  	  Gateway::sendToClient($client_id,'not good jiami, kick out you' );
  							} 
					
					
					
				  }
				  
				  
	  		 	  
					
				
		   }
		  
		   
		  
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id) {
       // 向所有人发送 
       GateWay::sendToAll("$client_id logout");
		 
       //Gateway::unbindUid($client_id,$_SESSION['uid']); 
		 //unset($_SESSION['uid']); 
	
   }
	
	
	

}
