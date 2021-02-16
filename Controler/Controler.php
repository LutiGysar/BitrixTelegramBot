<?php
require_once ('Model/ModelBitrix.php');
require_once ('View/View.php');
require_once('vendor/autoload.php');
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Keyboard\Button;
use Telegram\Bot\Methods;


Class Controler{
	public static function dataHendler($data){
		//Принимает API->calback_query->data;
		$telegram = new Api();
		$result=$telegram-> getWebhookUpdates(); 
		$callback_query = $result["callback_query"];
		$inline_id=$callback_query["from"]["id"];
		$chat_id = $result["message"]["chat"]["id"]; //Уникальный идентификатор пользователя
		$name = $result["message"]["from"]["first_name"];
		$lastName = $result["message"]["from"]["username"]; //Юзернейм пользователя
		$phone = $result["message"]["contact"]["phone_number"];
		$MainName="$name $lastName";
		switch($data){
			
			case('service'):
			$ListData=ModelBitrix::mainList();
			View::Menu($data,$ListData);
			break;
			
			case(strpos($data,'servicelist')):
			$idNestedList=self::filterData($data);
			$checkData=ModelBitrix::productFilter($idNestedList);
			if($checkData===false){
				$nestedListData=ModelBitrix::nestedList($idNestedList);
				View::Menu($data,$nestedListData);
			}else{
				self::dataHendler("serviceNested_$idNestedList");
			}
			break;
			
			case(strpos($data,'serviceNested')):
			$idList=self::filterData($data);
			$productData=ModelBitrix::allProductInfo($idList);
			View::Menu($data,$productData);
			break;
			
			case(strpos($data,'product')):
			$dataAll=View::allDataGive('Inline');
			$nameWorker=ModelBitrix::profGive($dataAll['booking']['servicelist']);
			 file_put_contents('printData/dataPush',serialize($nameWorker));
			$productData=ModelBitrix::getUsersTable($nameWorker);
			View::Menu($data,$productData);
			break;
			
			case((strpos($data,'master'))):
			View::Menu($data,null);
			break;
			
			case('<'):
			View::Menu($data,null);
			break;
			
			case('>'):
			View::Menu($data,null);
			break;
			
			case(strpos($data,'Datelist')):
			$date=self::filterData($data);
			$dataAll=View::allDataGive('Inline');
			$id=$dataAll['booking']['masterlist'];
			$productData=ModelBitrix::getResourceFreeTable($date,$id);
			View::Menu($data,$productData);
			break;
			
			case(strpos($data,'Time')):
			View::Menu($data,null);
			break;
			
			case(strpos($data,'contactId')):
			$idContact=ModelBitrix::clientCheck($chat_id);
			ModelBitrix::updateContact($idContact,$phone);
			$dataAll=View::allDataGive('noInline');
			$timeALL=self::filterTime($dataAll['booking']['Time']);
			$productAll=ModelBitrix::ProductGet($dataAll['booking']['productlist']);
			$date=$dataAll['booking']['Datelist'];
			$dateEnd=$date.' '.$timeALL[0].':00';
			$dateBegin=$date.' '.$timeALL[1].':00';
			$return=ModelBitrix::DateCheck($date,$dateBegin,$dateEnd,$dataAll['booking']['masterlist']);
			$name=$MainName;
			$result=ModelBitrix::Accept($dataAll['booking']['Datelist'],$timeALL,$productAll,$dataAll['booking']['masterlist'],$name,$idContact);
			$nameMaster=ModelBitrix::getNameUsers($dataAll['booking']['masterlist']);
			$dateEnd='📅'.$date.' c: '.$timeALL[0].'⏱';
			$dateBegin='📅'.$date.' по: '.$timeALL[1].'⏱';
			$finishData=[$productAll[1],$productAll[0],$dateBegin,$dateEnd,$nameMaster];
			if($return==true){
				$telegram->deleteMessage(['chat_id'=> $chat_id,
				'message_id'=>$dataAll['booking']['messageId']]);
				View::Menu('finish',$finishData);
				View::Menu('startWorkCallBack',null);
			}
			break;
			
			case('backHome'):
			View::Menu('backHome',null);
			break;
			
			case('pickPhone'):
			View::Menu('pickPhone',null);
			break;
		}
	}

	public static function filterData($data){
		$idList=substr($data,strrpos($data,'_')+1);
		return $idList;
	}
	
	public static function filterTime($time){
		$timeBegin=substr($time, 0,strrpos($time, '-'));
		$timeEnd=substr($time,strrpos($time,'-')+1);
		$timeAll=[$timeBegin,$timeEnd];
		return $timeAll;
	}
}
	
?>