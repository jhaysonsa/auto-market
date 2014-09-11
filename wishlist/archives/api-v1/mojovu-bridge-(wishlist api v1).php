<?php
//*============================================
//*	Page: mojovu-bridge.php
//*	Last Modified: 07/01/2011 by Neal Lambert
//*	Author: Neal Lambert
//*	Description: Creates a Mojo Video University
//* account for users who already have a Mojo 
//* Video Matrix account.
//*============================================

$key = $_GET['key'];
if ($key != "298xbkmd9dfl"){
	die("Unauthorized Access Attempt. The Administrators have been notified.");
}

$contactId = $_REQUEST['Id'];
if ($contactId == ""){
	die("No contact ID was specified.");
}

//*=================
//* INCLUDES
//*=================

	//INFUSIONSOFT
	include('includes/isdk.php');
	
//*=================
//* CONFIGURATION
//*=================

	//INFUSIONSOFT
	$connInfo = array('main:mojo:i:679c2a2a6aef93984f48e3ec4794db26:Mojo Video');
	$app = new iSDK;
	$app->cfgCon("main");
	
//*===============================
//* GET USER INFORMATION FROM IS
//*===============================	

	$fields = array('FirstName', 'LastName','Email','Phone1','Company','Username','Password');
	$IS_DATA = $app->loadCon((int)$contactId, $fields);
	
	
//*==============================================
//* POST TO MOJO VIDEO UNIVERSITY
//* http://mojovideouniversity.com/
//*==============================================	
	//KEY
	$Secret = "LXbfqw59CFNUWaex";

	//PARAMETERS
	$fxn 		= 'AddUser';
	$username	= $IS_DATA['Username'];
	$email		= $IS_DATA['Email'];
	$pass		= $IS_DATA['Password'];
	$firstname 	= $IS_DATA['FirstName'];
	$lastname 	= $IS_DATA['LastName'];

	$params = array ($username,$email,$pass,$firstname,$lastname);
	$key = md5 ($fxn.'__'.$Secret.'__'.implode('|',$params));

	$URL = "http://www.mojovideouniversity.com/?WLMAPI=$fxn/$key/$username/$email/$pass/$firstname/$lastname";

	//POST
	$ch = curl_init ($URL);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$return = curl_exec($ch);
	curl_close($ch);

	list ($status, $return) = unserialize ($return);
	
	if ($status) {
		//SUCCESS
		echo 'SUCCESS! A new Mojo Video University user account has been created. Id: '.$return.'<br/>';
		
		//LEVELS TO SUBSCRIBE TO
		$levelsarray 	= array ("1304974259");
		
		//PARAMETERS
		$fxn 			= 'AddUserLevels';
		$WLId 			= $return;
		$levels 		= implode(",",$levelsarray); //GOLD PLAN 1
		$txid 			= "";
		$autoresponder 	= false;
		
		$params = array ($WLId,$levels,$txid,$autoresponder);
		$key = md5 ($fxn.'__'.$Secret.'__'.implode('|',$params));

		$URL = "http://www.mojovideouniversity.com/?WLMAPI=$fxn/$key/$WLId/$levels/$txid/$autoresponder";

		//POST
		$ch = curl_init ($URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$return = curl_exec($ch);
		curl_close($ch);

		list ($status, $return) = unserialize ($return);
		
		if ($status) {
			//SUCCESS
			echo 'SUCCESS! A Mojo Video University user account has been setup on The Gold Plan 1<br/>';
		} else {
			//FAILURE, SHOW ERROR
			echo 'FAILURE: '.$return.'<br/>';
		}
	
	} else {
		//FAILURE, SHOW ERROR
		echo 'FAILURE: '.$return.'<br/>';
	}
	
	
//*===============================
//* UPDATE INFUSIONSOFT
//*===============================	
		
	//STORE WISHLIST INFORMATION
	if ($WLId){ $data['_WishListId'] = $WLId; }
	if ($levelsarray){ $data['_WishListLevels'] = implode(",", $levelsarray); }
	
	$update = $app->updateCon((int)$contactId,$data);
	
	
?>