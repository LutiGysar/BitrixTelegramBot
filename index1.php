<?php
require_once('View/View.php');
require_once('Model/ModelBitrix.php');
require_once('Controler/Controler.php');
require_once('vendor/autoload.php');
require_once ('src/crest.php');
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Keyboard\Button;

$telegram = new Api();//аппи ключ бота
$result = $telegram -> getWebhookUpdates(); 
file_put_contents('printData/dataCallBack',serialize($result));
$text = $result["message"]["text"]; //Текст сообщения
$chat_id = $result["message"]["chat"]["id"]; //Уникальный идентификатор пользователя
$name = $result["message"]["from"]["first_name"];
$lastName = $result["message"]["from"]["username"]; //Юзернейм пользователя
$phone = $result["message"]["contact"]["phone_number"];
$callback_query = $result["callback_query"];
$data = $callback_query["data"];



/*  $resultList=CRest::call('catalog.section.list',['filter'=>['iblockId'=>25],'select'=>['name','id','iblockSectionId']]);//Тут можно поменять idlockId
	  $resultList=$resultList['result']['sections'];



 echo'<pre>';
print_r($resultList);
echo'</pre>'; */
/*  if($text=='/start'){
 View::startWork($chat_id,$telegram);
 } */
 
 
 
 // Проверка на существование!!!
if($text=='/start'){
	//приходит команда /start
 	$chek=ModelBitrix::clientCheck($chat_id); 
	switch($chek){
		
		case(true):
		file_put_contents('printData/dataCallBack',serialize($data));
		View::startWork($chat_id,$telegram);
		break;
		
		case(false):
		file_put_contents('printData/dataCallBack',serialize('yes'));
		ModelBitrix::ClinentAdd($name,$lastName,$chat_id);
		View::startWork($chat_id,$telegram);
		break;
	} 
}
if($phone!=null){
		Controler::dataHendler("contactId_$idContact");
}
if($data!=null){
 Controler::dataHendler($data);	
}
?>
