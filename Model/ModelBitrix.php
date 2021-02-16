<?php
require_once ('src/crest.php');
require_once ('core/settingsBot.php');
class ModelBitrix{
	
	public static function mainList(){
	  $resultList=CRest::call('catalog.section.list',['filter'=>['iblockId'=>BITRIX_PRODUCTS_CATALOG_ID],'select'=>['name','id','iblockSectionId']]);
	  $resultList=$resultList['result']['sections'];
		foreach($resultList as $key=>$value){	
		  if($value['iblockSectionId']==null){
			  $result[]=$value;
		}
	  }
	  return $result;
	}
	
	public function dateRestFilter(){
		$rezult=self::getResourceFreeTable('2020-09-11T10:47:42+03:00',9);
		file_put_contents('printData/dataRegist',serialize($rezult));
	}
	public static function productFilter($id){
	  $resultList=CRest::call('crm.product.list',['filter'=>['SECTION_ID'=>$id]]);
		if($resultList['result']==null){
			return false;
		}else{
			return true;
		}
	}
	public static function nestedList($id){
	  $resultList=CRest::call('catalog.section.list',['filter'=>['iblockId'=>BITRIX_PRODUCTS_CATALOG_ID],'select'=>['name','id','iblockSectionId']]);
	  $resultList=$resultList['result']['sections'];
		foreach($resultList as $key=>$value){	
		  if($value['iblockSectionId']==$id){
			  $result[]=$value;
		}
	  }
	  return $result;
	}
	
	private function gifProductTable(){
		$productData=CRest::call('crm.product.list',['select'=>['ID','NAME','PRICE','SECTION_ID']]);
		return $productData;
	}
	
	public static function allProductInfo($idList){
		$resultProduct=CRest::call('crm.product.list',['filter'=>['SECTION_ID'=>$idList],'select'=>['ID','NAME','PRICE','SECTION_ID']]);
		$resultProduct=$resultProduct['result'];
		foreach($resultProduct as $key){
				if($key['SECTION_ID']==$idList){
				$step++;
				$productList[$step]=[$key['NAME'].'('.$key['PRICE'].'Р)',$key['ID']];
			}
		}
		return $productList;
	}
	
	public static function ProductGet($id){
		$step=0;
		$productData=CRest::call('crm.product.get',['id'=>$id]);
		$resultProduct=$productData['result'];
		$productList[0]=$resultProduct['NAME'];
		$productList[1]=$resultProduct['PRICE'];
		return $productList;
	}
	public function profGive($id){
		 $resultList=CRest::call('catalog.section.list',['filter'=>['iblockId'=>BITRIX_PRODUCTS_CATALOG_ID],'select'=>['name','id','iblockSectionId']]);
	  $resultList=$resultList['result']['sections'];
	  foreach($resultList as $key){
		  if($key['id']==$id){
				return $key['name'];
			}
		}
	}
	public function getUsersTable($prof){
		//получает массив с работниками
		$resultData=CRest::call('user.search',['UF_DEPARTMENT_NAME'=>BITRIX_DEPARTMENT_NAME]);
		$resultData=$resultData['result'];
		foreach($resultData as $key =>$value){
			if($prof==$value['WORK_POSITION']){
				$returnData[]=[$value['NAME'],$value['ID']];
			}
		}
		return $returnData;
	}
	
	public function getNameUsers($id){
		//получает массив с именами работников
		$resultData=CRest::call('user.search',['UF_DEPARTMENT_NAME'=>BITRIX_DEPARTMENT_NAME]);
		$resultData=$resultData['result'];
		foreach($resultData as $key =>$value){
			if($value['ID']==$id){
				return $value['NAME'];
			}
		}
	}
	
	public function getResourceFreeTable($date,$id){
		//получает массив с временем
		$result=CRest::call('calendar.accessibility.get',[
			'users'=>[$id],
			'from'=>$date,
			'to'=>$date,]);
		$result=$result['result'][$id];
			return $result;
	}
	
	public function clientReport(){
	//отпровляет жалаобу на мастера
	return;
	}
	
	public static function ClinentAdd($name,$lastName,$id){
			$result=CRest::call('crm.contact.add',['fields'=>[
			'NAME'=>$name,
			'IM'=>[['VALUE_TYPE'=>'TELEGRAM',
            'VALUE'=>"https://t.me/$lastName"]],
			BITRIX_TELEGRAM_FIELD_ID=>$id,  // Заменить при переустановки
		]]);
	}
	
	//Функция получения данных о всех пользователях Bitrix
	public function allUsersInfo(){
		$resultUsers=self::usersDataGive();
		foreach($resultUsers as $key => $value){
			if(($value['NAME']!=null)&&($value['WORK_POSITION']!=null)&&($value['UF_DEPARTMENT']!=null)){
				$step++;
				$allUsersName[$step]=[$value['NAME'].' '.$value['LAST_NAME'],$value['ID']];
				//$allUsersJobs[$step]=$value['WORK_POSITION'];
			}
		}
		return $allUsersName;
	}
	
	private function usersDataGive(){
		$usersDataSerch=CRest::call('user.search');
		$resultUsers=$usersDataSerch['result'];
		return $resultUsers;
	}
	
	public function clientCheck($id){
		//проверяет если такой клиент возвращает try false
		$resultData=CRest::call('crm.contact.list',['select'=>[BITRIX_TELEGRAM_FIELD_ID]]); // Заменить при переустановки
		$resultData=$resultData['result'];
			foreach($resultData as $key){
			if($key[BITRIX_TELEGRAM_FIELD_ID]==$id){
				return $key['ID'];
			}
		}
	}
	
	public function timeWork($id){
		$time=CRest::call('timeman.settings',['id'=>$id]);
		return $time;
	}
	
	public function Accept($date,$time,$product,$user,$name,$contactId){
		$dateBegin=$date.$time[0];
		$dateBegin=date_create($dateBegin);
		$dateBegin=date_format($dateBegin,'d-m-Y H:i:s');
		$dateEnd=$date.' '.$time[1];
		$productName=$product[0];
		$productPrice=$product[1];
		$test=CRest::call('crm.deal.add',[
				'fields'=>[
				'CONTACT_IDS'=>[$contactId],
				'TITLE'=>"Услуга: $productName Заказчик: $name",
				'RESPONSIBLE_ID'=>1,
				'CURRENCY_ID'=> "RUB", 
                'OPPORTUNITY'=>$productPrice,
				BITRIX_BOOKING_ID=>[
				"user|$user|$dateBegin|1800|$productName"]]]); //UF_CRM_1599122992658 поменять на другое поле ПРИ УСТАНОВКИ НА ДРУГОЙ БИТРИКС
		if($test[result]!=null){
			return true;
		}
	}
	
	public function updateContact($idContact,$phone){
	$rez=CRest::call('crm.contact.update',['id'=>$idContact,'fields'=>['PHONE'=>[['VALUE'=>$phone]]]]);
		file_put_contents('printData/DataPick',serialize($phone));
	}
	
	public function DateCheck($date,$dateEnd,$dateBegin,$id){
		$dateBegin=date_create($dateBegin);
		$dateBegin=date_format($dateBegin,'d.m.Y H:i:s');
		$dateEnd=date_create($dateEnd);
		$dateEnd=date_format($dateEnd,'d.m.Y H:i:s');
		$rezult=self::getResourceFreeTable($date,$id);
		foreach($rezult as $key=>$value){
				if(($value['DATE_FROM']==$dateBegin)&&($value['DATE_TO']==$dateEnd)){
					/* file_put_contents('data',
						"Дата начала Битрикс24: ".$value['DATE_FROM']."\n".
						"Выбранная пользователем дата начала: ". $dateBegin."\n".
						"Дата конца Битикс24: ".$value['DATE_TO']."\n".
						"Выранная пользователем дата конца: ".$dateEnd
						); */
					 $check=false;
				}

			}
			//file_put_contents('data',serialize($check));
				if($check==false){
					return true;
				}else{
					return false;
				}
		}
		
}
?>