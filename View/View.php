<?php
//Функция MainMenu принимает в себя 2 зачения, и выполняет функцию своеобразного 
//$nameMenu-наименование требуемого меню (Обязательный параметр!)
//$dataNeed - массив с данными для определенного меню. Где данные с битрикса необходимы для работы (Неообязательный параметр!)

use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Keyboard\Button;

Class View{

public static function Menu($nameMenu,$dataNeed){
		$telegram = new Api();
		$result=$telegram-> getWebhookUpdates(); 
		$callback_query = $result["callback_query"];
		$chat_id = $result["message"]["chat"]["id"];
		$inline_id=$callback_query["from"]["id"];
		$inline_message_id=$callback_query["message"]["message_id"];
	switch($nameMenu){
		
		case'':
		break;
		
		case('backHomeInline'):
		self::startWorkInline($chat_id,$dataNeed,$telegram);
		break;
		
		case('backHome'):
		self::startWorkInline($inline_id,$inline_message_id,$telegram);
		break;
		
		case('finish'):
		$text="🎉Вы забранировали🎉:
		Услугу: $dataNeed[1] ✨
		У мастера: $dataNeed[4] 👸
		На сумму: $dataNeed[0] руб💸
		C: $dataNeed[3] 🕛
		По: $dataNeed[2] 🕛";  
		$telegram->sendMessage(['chat_id'=>$chat_id, 'text'=>$text, 'reply_markup'=>Keyboard::remove()]);
		$arr=[];
		file_put_contents("Sessions/$chat_id".'_step',serialize($arr));
		break;
		
		//Выбор Категории
		case('service'):
		$text='💁Выберете категорию:';
		self::filterDataReverse("messageId_$inline_message_id",$inline_id);
		self::PutStepFile($inline_id,0,'backHome');
		$back=self::inPutStepFile($inline_id,0);
		self::PutStepFile($inline_id,1,'service');
		$nameId='id';
		$name='name';
		self::listMessageCreate($inline_id,$text,$inline_message_id,$telegram,$dataNeed,$nameMenu,$nameId,$name,$back);
		break;
		
		case(strpos($nameMenu,'servicelist')):
		$text='💁Выберете категорию:';
		self::filterDataReverse($nameMenu,$inline_id);
		self::PutStepFile($inline_id,2,$nameMenu);
		$back=self::inPutStepFile($inline_id,1);
		$nameMenu='servicelist';
		$nameId='id';
		$name='name';
		self::listMessageCreate($inline_id,$text,$inline_message_id,$telegram,$dataNeed,$nameMenu,$nameId,$name,$back);
		break;
		
		//Выбор услуги
		case(strpos($nameMenu,'serviceNested')):
		$text='💅Выберете услугу💇:';
		$back='serviceNested';
		$back=self::inPutStepFile($inline_id,2);
		self::PutStepFile($inline_id,3,$nameMenu);
		$nameMenu='product';
		$nameId='1';
		$name='0';
		self::listMessageCreate($inline_id,$text,$inline_message_id,$telegram,$dataNeed,$nameMenu,$nameId,$name,$back);
		break;
		
		//Выбор работника
		case(strpos($nameMenu,'product')):
		self::filterDataReverse($nameMenu,$inline_id);
		$text='Выберете мастера👸:';
		$back=self::inPutStepFile($inline_id,3);
		self::PutStepFile($inline_id,4,$nameMenu);
		$nameMenu='master';
		$nameId='1';
		$name='0';
		self::listMessageCreate($inline_id,$text,$inline_message_id,$telegram,$dataNeed,$nameMenu,$nameId,$name,$back);
		break;
		
		case('>'):
		$text='Выберете дату📅:';
		$back=self::inPutStepFile($inline_id,4);
		self::PutStepFile($inline_id,5,$nameMenu);
		$nameMenu='Date';
		$today=1;
		$mouthStep=2;
		$mouthStepDate=1;
		$dataNeed=self::Calendar($mouthStep,$today,$nameMenu);
		self::calendarMessageCreate($inline_id,$text,$inline_message_id,$telegram,$dataNeed,$mouthStepDate,$back);
		break;
		
		case(strpos($nameMenu,'master')):
		self::filterDataReverse($nameMenu,$inline_id);
		$back=self::inPutStepFile($inline_id,4);
		self::PutStepFile($inline_id,5,$nameMenu);
		$text='Выберете дату📅:';
		$back='product';
		$nameMenu='Date';
		$today=date('d');
		$mouthStepDate=0;
		$mouthStep=1;
		$dataNeed=self::Calendar($mouthStep,$today,$nameMenu);
		self::calendarMessageCreate($inline_id,$text,$inline_message_id,$telegram,$dataNeed,$mouthStepDate,$back);
		break;
		
		case('<'):
		$text='Выберете дату📅:';
		$back=self::inPutStepFile($inline_id,4);
		$back='product';
		$nameMenu='Date';
		$today=date('d');
		$mouthStepDate=0;
		$mouthStep=1;
		$dataNeed=self::Calendar($mouthStep,$today,$nameMenu);
		self::calendarMessageCreate($inline_id,$text,$inline_message_id,$telegram,$dataNeed,$mouthStepDate,$back);
		break;
		
		case(strpos($nameMenu,'Date')):
		self::filterDataReverse($nameMenu,$inline_id);
		$back=self::inPutStepFile($inline_id,5);
		self::PutStepFile($inline_id,6,$nameMenu);
		$text='Выберете время🕗:';
		$nameMenu='Time';
		$timeAll=self::timeExceptions('30 min','09:00:00','20:00:00',$dataNeed);
		$text='Выберете удобное время';
		self::timeWin($timeAll,$inline_id,$text,$inline_message_id,$telegram,$back);
		break;
		
		case(strpos($nameMenu,'Time')):
		self::filterDataReverse($nameMenu,$inline_id);
		$back=self::inPutStepFile($inline_id,6);
		$text='Для создания бронирования предоставьте🙏 телефон📱:';
		$nameMenu='pickPhone';
		$DataNeed[]=['Предоставить'];
		self::simpleMessageCreate($inline_id,$text,$inline_message_id,$telegram,$DataNeed,$nameMenu,$back);
		break;
		
		case('pickPhone'):
		$textButton='✅Предоставить
		номер телефона📲';
		self::pickNumberPhone($inline_id,$telegram,$textButton);
		break;
		
		case('startWorkCallBack'):
		file_put_contents('',serialize());
		$textButton='/start';
		$textMessage='Нажмите /start для начала работы🌺';
		file_put_contents('printData/DatalistMessageCreate',serialize($chat_id));
		self::simpleKeyboardCallBack($chat_id,$telegram,$textButton,$textMessage);
		$arr=[];
		file_put_contents("Sessions/$chat_id".'_step',serialize($arr));
		break;
	}
}
	private function PutStepFile($inline_id,$step,$nameMenu){
		$arr=unserialize(file_get_contents("Sessions/$inline_id".'_step'));
		$check=strpos($nameMenu,'servicelist');
		if($check===false){
			$arr[$step]=$nameMenu;
		}else{
			$arr[$step][]=$nameMenu;
		}
		file_put_contents("Sessions/$inline_id".'_step',serialize($arr));
	}
	
	private function inPutStepFile($inline_id,$step){
		$stepData=unserialize(file_get_contents("Sessions/$inline_id".'_step'));
		if($step==2){
			$index=count($stepData[$step])-1;
			$index=$stepData[$step][$index];
			$return=$index;
			unset($stepData[$step][$value]);
			file_put_contents("Sessions/$inline_id".'_step',serialize($stepData));
			return $return;
		}else{
		return $stepData[$step];
		}
	}
	
	public static function calendarMessageCreate($inline_id,$text,$inline_message_id,$telegram,$dataNeed,$monthStep,$back){
		$months = array( 1 => 'Январь' , 'Февраль' , 'Март' , 'Апрель' , 'Май' , 'Июнь' , 'Июль' ,
		'Август' , 'Сентябрь' , 'Октябрь' , 'Ноябрь' , 'Декабрь' );
		$button4 = Keyboard::inlineButton(['text'=>date($months[date( 'n' )+$monthStep] . ' Y' ), 'callback_data'=>' ']);
		$firstDay=strftime('%u',strtotime(date("Y-m-1", strtotime("+$monthStep month"))));
		$firstDay=$firstDay-1;
		$lastDay=strftime('%u',strtotime(date("Y-m-t", strtotime("+$monthStep month"))));
		$lastDay=7-$lastDay;
		for($step=1;$step<=$firstDay;$step++){
			$button=Keyboard::inlineButton(['text'=>' ', 'callback_data'=>' ']);
			$sdasddsad[]=$button;
			array_unshift($dataNeed[array_key_first($dataNeed)],$button);
		}
		file_put_contents('printData/dataProduct',serialize($lastDay));
		for($step=1;$step<=$lastDay;$step++){
			$button=Keyboard::inlineButton(['text'=>' ', 'callback_data'=>' ']);
			$sdasd[]=$button;
			array_push($dataNeed[array_key_last($dataNeed)],$button);
		}
		file_put_contents('printData/dataProduct',serialize($dataNeed));
		$dataNeed = call_user_func_array('array_merge', $dataNeed);
		$dataNeed=array_chunk($dataNeed,7);
		$keyboardNameDays[0][]=$button4;
		$reply_markup = new Keyboard();
		$reply_markup = $reply_markup->inline();
		$dayName=['Пн','Вт','Ср','Чт','Пт','Сб','Вс'];
		foreach($dayName as $value){
			$button=Keyboard::inlineButton(['text'=>$value, 'callback_data'=>' ']);
			$keyboardNameDays[1][]=$button;
		} 
		$button1 = Keyboard::inlineButton(['text'=>'>>', 'callback_data'=>'>']);
		$button2 = Keyboard::inlineButton(['text'=>'<<', 'callback_data'=>'<']);
		$button3 = Keyboard::inlineButton(['text'=>' ', 'callback_data'=>' ']);
		$buttonOne=Keyboard::inlineButton(['text'=>'На главную🏠', 'callback_data'=>'backHome']);
		$buttonTwo=Keyboard::inlineButton(['text'=>'Назад↩️', 'callback_data'=>$back]);
		$keyboardFooter=[[$button2,$button3,$button1],[$buttonTwo,$buttonOne]];
		//file_put_contents('printData/dataProduct',serialize($keyboardNameDays));
		$reply_markup = $reply_markup->rowCustomEnd($keyboardNameDays)->rowCustomEnd($dataNeed)->rowCustomEnd($keyboardFooter);
		$telegram->editMessageText(['chat_id'=>$inline_id,'text'=>$text,'message_id'=>$inline_message_id,'reply_markup'=>$reply_markup,'parse_mode'=>'MarkdownV2']);
	}
	
	
	public static function listMessageCreate($inline_id,$text,$inline_message_id,$telegram,$dataNeed,$data,$nameId,$name,$back){
		$reply_markup = new Keyboard();
		$reply_markup = $reply_markup->inline();
		foreach($dataNeed as $key=>$value){
				$button=Keyboard::inlineButton(['text'=>$value[$name], 'callback_data'=>$data.'list_'.$value[$nameId]]);
				$reply_markup = $reply_markup -> row($button);
		}
		$buttonOne=Keyboard::inlineButton(['text'=>'На главную🏠', 'callback_data'=>'backHome']);
		$buttonTwo=Keyboard::inlineButton(['text'=>'Назад↩️', 'callback_data'=>$back]);
		$reply_markup = $reply_markup -> row($buttonTwo)->row($buttonOne);
		$telegram->editMessageText(['chat_id'=>$inline_id,'text'=>$text,'message_id'=>$inline_message_id,'reply_markup'=>$reply_markup]);
	}
	
	public static function simpleMessageCreate($inline_id,$text,$inline_message_id,$telegram,$dataNeed,$data,$back){
		$step=0;
		$reply_markup = new Keyboard();
		$reply_markup = $reply_markup->inline();
		foreach($dataNeed as $key=>$value){
				$button=Keyboard::inlineButton(['text'=>$value[$step], 'callback_data'=>$data]);
				$reply_markup = $reply_markup -> row($button);
				$step++;
		}
		$buttonOne=Keyboard::inlineButton(['text'=>'На главную🏠', 'callback_data'=>'backHome']);
		$buttonTwo=Keyboard::inlineButton(['text'=>'Назад↩️', 'callback_data'=>$back]);
		$reply_markup = $reply_markup -> row($buttonTwo)->row($buttonOne);
		$telegram->editMessageText(['chat_id'=>$inline_id,'text'=>$text,'message_id'=>$inline_message_id,'reply_markup'=>$reply_markup]);
	}

	public static function startWork($id,$telegram){
		$button1 = Keyboard::inlineButton(['text'=>'Записаться', 'callback_data'=>'service']);
		/* $button2 = Keyboard::inlineButton(['text'=>'Оставить отзыв', 'callback_data'=>'review ']);
		$button3 = Keyboard::inlineButton(['text'=>'Заказать Обратный звонок', 'callback_data'=>'call']);
		$button4 = Keyboard::inlineButton(['text'=>'Где находимся', 'callback_data'=>'place']); */
			$reply = "Здраствуйте11🙋 Бот компании
✨Bellezza✨ привествует вас!
💮Для того чтобы записаться на прием, 🔰нажмите🔰";
			$reply_markup = new Keyboard();
			$reply_markup = $reply_markup->inline();
			$reply_markup = $reply_markup->row($button1);
			$telegram->sendMessage([ 'chat_id' =>$id, 'text' =>$reply, 'reply_markup' =>$reply_markup ]);
	}
	public static function startWorkInline($inline_id,$inline_message_id,$telegram){
		$button1 = Keyboard::inlineButton(['text'=>'Записаться', 'callback_data'=>'service']);
/* 		$button2 = Keyboard::inlineButton(['text'=>'Оставить отзыв', 'callback_data'=>'review ']);
		$button3 = Keyboard::inlineButton(['text'=>'Заказать Обратный звонок', 'callback_data'=>'call']);
		$button4 = Keyboard::inlineButton(['text'=>'Где находимся', 'callback_data'=>'place']); */
			$reply = "Здраствуйте22🙋 Бот компании Bellezza✨ привествует вас.:
💮Для того чтобы записаться на прием, 🔰нажмите🔰 на соответствующую кнопку💮";
			$reply_markup = new Keyboard();
			$reply_markup = $reply_markup->inline();
			$reply_markup = $reply_markup -> row($button1);
			$telegram->editMessageText(['chat_id'=>$inline_id,'text'=>$reply,'message_id'=>$inline_message_id,'reply_markup'=>$reply_markup]);
	}

	public function Calendar($mouthStep,$today,$data){
		//формирует кнопки календаря сотрудника
		$timeExceptions=mktime(0, 0, 0, date("m")+$mouthStep, 0, date('Y'));
		$mouth=strftime("%m %Y",$timeExceptions);
		if($today!=1){
			$massExceptions=range(1,$today-1);
			foreach($massExceptions as $key => $numder){
				settype($numder, 'string');
				$dateTime=strftime("%Y-%m-%d",mktime(0, 0, 0, date("m")+0,$numder, date('Y')));
				$keyDate=strtotime($dateTime);
				$dateTimeWeek=strftime("%V",$keyDate);
				$button=Keyboard::inlineButton(['text'=>'❌', 'callback_data'=>' ']);
				$massDate[$dateTimeWeek][$keyDate]= $button;
			}
			$keyboardDateFull=self::DataGive($data);
			$dataDate=array_replace_recursive($keyboardDateFull[$mouth],$massDate);
		}else{
			$keyboardDateFull=self::DataGive($data);
			$dataDate=$keyboardDateFull[$mouth];
		}
		return $dataDate;
	}
	
	public function DataGive($data){
		for($step=1;$step<=367;$step++){
			$date=mktime(0,0,0,date("m"),$step,date("Y"));
			$dateTimeCall=strftime("%Y-%m-%d",$date);
			$dateTimeText=strftime("%e",$date);
			$dateTimeKey=strftime("%m %Y",$date);
			$dateTimeWeek=strftime("%V",$date);
			$dateTimeIndex=strtotime($dateTimeCall);
			$button=Keyboard::inlineButton(['text'=>$dateTimeText, 'callback_data'=>$data.'list_'.$dateTimeCall]);
			$keyboardDateFull[$dateTimeKey][$dateTimeWeek][$dateTimeIndex]=$button;
		}
		return $keyboardDateFull;
	}

	public static function filterDataReverse($data,$inline_id){
				if(file_exists("Sessions/$inline_id")){
					$id=substr($data,strrpos($data, '_')+1);
					$arr=unserialize(file_get_contents("Sessions/$inline_id"));
					$data=substr($data, 0,strrpos($data, '_'));
					$arr['booking'][$data]=$id;
					file_put_contents("Sessions/$inline_id",serialize($arr));
				}else{
					$id=substr($data,strrpos($data, '_')+1);
					$data=substr($data, 0,strrpos($data, '_'));
					$arr['booking'][$data]=$id;
					file_put_contents("Sessions/$inline_id",serialize($arr));
				}
	}	
			
	public static function allDataGive($chose){
		$telegram = new Api();
		$result=$telegram-> getWebhookUpdates();
		switch($chose){
			
			case('Inline'):
			$callback_query = $result["callback_query"];
			$inline_id=$callback_query["from"]["id"];
			$arr=unserialize(file_get_contents("Sessions/$inline_id"));
			return $arr;
			break;
			
			case('noInline'):
			$chat_id = $result["message"]["chat"]["id"];
			$arr=unserialize(file_get_contents("Sessions/$chat_id")); 
			return $arr;
			break;
		}
	}
	
	public static function timeExceptions($step,$timeStart,$timeEnd,$data){
		$differenceTime=round((strtotime($timeEnd) - strtotime($timeStart))/3600, 1);
		$differenceTime=(($differenceTime*60)/30);
		$differenceTime--;
		  $timeStart=date_create($timeStart);
           for($stepFor=0;$stepFor<=$differenceTime;$stepFor++){
			   if($stepFor==0){
				$timeStartMod=date_format($timeStart,'G:i');
				$dateMass[]=$timeStartMod;
				$timeStepMod=date_format(date_modify($timeStart,$step), 'G:i');
			   }else{
				$timeStartMod=date_format($timeStart,'G:i');
				$dateMass[]=$timeStartMod;
				$timeStepMod=date_format(date_modify($timeStart,$step), 'G:i');
				$dateMass[]=$timeStartMod;
			   }
			   if($stepFor==$differenceTime){
				  $timeStartMod=date_format($timeStart,'G:i');
				  $dateMass[]=$timeStartMod;
			   }
		}
		
		$importData=self::timeExportExceptions($data);
		$dateMass=array_chunk($dateMass,2);
		foreach($dateMass as $key=>$value){
			$dateMassImport[]=$value[0].'-'.$value[1];
		}
		if($importData!=null){
			$result=array_diff($dateMassImport,$importData);
			return $result;
		}elseif(($importData==null)&&($data[0]!=null)){
			return $importData;
		}elseif(($importData==null)&&($data[0]==null)){
			return $dateMassImport;
		}
	}
	
	public static function timeExportExceptions($data){
			 foreach($data as $key=>$value){
						$differenceTime=round((strtotime($value['DATE_TO']) - strtotime($value['DATE_FROM']))/3600, 1);
					if($differenceTime<=10.5){
						$differenceTime=(($differenceTime*60)/30);
						$timeStart=date_create($value['DATE_FROM']);
							for($stepFor=0;$stepFor<=$differenceTime;$stepFor++){
								$timeStartMod=date_format($timeStart,'G:i');
								$dateMass[]=$timeStartMod;
								$timeStepMod=date_format(date_modify($timeStart,'30 min'), 'G:i');
							}
						$dateMass=array_chunk($dateMass,2);
						foreach($dateMass as $key=>$value){
							$dateMassExport[]=$value[0].'-'.$value[1];
						}
					}else{
						$dateMassExport=null;
					} 
			 }
		return $dateMassExport;
	}
	public static function timeWin($data,$inline_id,$text,$inline_message_id,$telegram,$back){
		$reply_markup = new Keyboard();
		$reply_markup = $reply_markup->inline();
		foreach($data as $key){
			$button=Keyboard::inlineButton(['text'=>$key, 'callback_data'=>'Time_'.$key]);
			$step++;
			$reply_markup = $reply_markup -> row($button);
		}
		$buttonOne=Keyboard::inlineButton(['text'=>'На главную🏠', 'callback_data'=>'backHome']);
		$buttonTwo=Keyboard::inlineButton(['text'=>'Назад↩️', 'callback_data'=>$back]);
		$reply_markup = $reply_markup -> row($buttonTwo)->row($buttonOne);
		$telegram->editMessageText(['chat_id'=>$inline_id,'text'=>$text,'message_id'=>$inline_message_id,'reply_markup'=>$reply_markup]);
	}
	
	public static function pickNumberPhone($chat_id,$telegram,$text){
			$reply_markup = new Keyboard([]);
			$button=Keyboard::button(['text'=>$text,'request_contact'=>True,'request_location'=>False,'resize_keyboard' => true]);
			$keyboard=$reply_markup->row($button);
			$telegram->sendMessage([ 'chat_id' =>$chat_id,'text'=>'Подтвердите действие:','reply_markup' =>$keyboard ]);
	}
	public function simpleKeyboardCallBack($chat_id,$telegram,$textButton,$textMessage){
		$reply_markup = new Keyboard(['resize_keyboard' => true,'one_time_keyboard'=>true]);
		$button=Keyboard::button(['text'=>$textButton,'request_contact'=>False,'request_location'=>False,'resize_keyboard' => true]);
		$keyboard=$reply_markup->row($button);
		$telegram->sendMessage([ 'chat_id' =>$chat_id,'text'=>$textMessage,'reply_markup' =>$keyboard ]);
	}
	
	
	/*
	*  Возможно пригодиться. Функция создания итогового сообщения бронирования с инлайн кнопкой
	*/
/* 	public static function finishMessageCreate($inline_id,$text,$inline_message_id,$telegram,$dataNeed,$data,$back){
		$step=0;
		$reply_markup = new Keyboard();
		$reply_markup = $reply_markup->inline();
		foreach($dataNeed as $key=>$value){
				$button=Keyboard::inlineButton(['text'=>$value[$step], 'callback_data'=>$data]);
				$reply_markup = $reply_markup -> row($button);
				$step++;
		}
		$telegram->sendMessage(['chat_id'=>$inline_id,'text'=>$text,'reply_markup'=>$reply_markup]);
	} */

}