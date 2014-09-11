<?php
//*============================================
//*	Page: mojovu-cancel.php
//*	Last Modified: 09/18/2011 by Neal Lambert
//*	Author: Neal Lambert
//*	Description: Cancels a clients Mojo Video Matrix
//* University account.
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

	$fields = array('FirstName', 'LastName','Email','Phone1','Company','_PlanCode','_UserId','_WishListId','_WishListLevels');
	$IS_DATA = $app->loadCon((int)$contactId, $fields);
	
//*==============================================
//* CANCEL MOJO VIDEO UNIVERSITY ACCOUNT
//* http://mojovideouniversity.com/
//*==============================================	
	//KEY
	$Secret = "LXbfqw59CFNUWaex";

	//PARAMETERS
	$fxn 			= 'DeleteUserLevels';
	$WishListId		= $IS_DATA['_WishListId'];
	$levels 		= $IS_DATA['_WishListLevels'];
	$autoresponder 	= false;
	
	$params = array ($WishListId,$levels,$autoresponder);
	$key = md5 ($fxn.'__'.$Secret.'__'.implode('|',$params));

	$URL = "http://www.mojovideouniversity.com/?WLMAPI=$fxn/$key/$WishListId/$levels/$autoresponder";

	//POST
	$ch = curl_init ($URL);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$return = curl_exec($ch);
	curl_close($ch);

	list ($status, $return) = unserialize ($return);
	
	if ($status) {
		//SUCCESS
		echo 'SUCCESS! The clients Mojo Video University user account has been canceled. <br/>';
	} else {
		//FAILURE, SHOW ERROR
		echo 'FAILURE: '.$return.'<br/>';
	}
	
?>