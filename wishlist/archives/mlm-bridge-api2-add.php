<?php
//*============================================
//*	Page: mojovu-bridge-ap2-add.php
//*	Last Modified: 01/18/2011 by Neal Lambert
//*	Author: Neal Lambert
//*	Description: Creates a new Mojo Video University
//* user account from the Infusionsoft contact record
//* specified. Updated to support Wishlist Member API 
//* Version 2.0.
//*
//* Params: 
//* key 	- unique key to acess script
//* Id		- Infusionsoft contact record id
//* level	- silver, silver_plus, gold, gold2  
//*
//* Example Usage:
//* Make an HTTP POST to the following URL:
//* mojovideomarketing.com/wishlist/mojovu-bridge-api2-add.php?key=298xbkmd9dfl&level=gold&Id=25
//*============================================

$key = $_GET['key'];
if ($key != "298xbkmd9dfl"){
	die("Unauthorized Access Attempt. The Administrators have been notified.");
}

$contactId = $_REQUEST['Id'];
if ($contactId == ""){
	die("No contact ID was specified.");
}

$level = $_REQUEST['level'];
if ($level == ""){
	die("No level specified.");
}

//*=================
//* INCLUDES
//*=================

	//INFUSIONSOFT
	include('includes/isdk.php');
	
	//WISHLISTMEMBER
	include('includes/wlmapiclass.php');
	
//*=================
//* CONFIGURATION
//*=================

	//INFUSIONSOFT
	$connInfo = array('main:mojo:i:679c2a2a6aef93984f48e3ec4794db26:Mojo Video');
	$infusion = new iSDK;
	$infusion->cfgCon("main");
	
	//WISHLISTMEMBER
	$wishlist = new wlmapiclass('http://www.mojovideouniversity.com/', 'LXbfqw59CFNUWaexLXbfqw59CFNUWaex');
	$wishlist->return_format = 'php'; // <- value can also be xml or json
	
	
//*===============================
//* GET USER INFORMATION FROM IS
//*===============================	

	$fields = array('FirstName', 'LastName','Email','Phone1','Company','Username','Password');
	$IS_DATA = $infusion->loadCon((int)$contactId, $fields);
	
//*==============================================
//* POST TO MOJO VIDEO UNIVERSITY
//* http://mojovideouniversity.com/
//*==============================================	
	
	//SET LEVEL
	if($level == 'silver')
	{
		$wllevels = array("1304974053"); //SILVER
		$plan = 'Silver';
	}
	else if($level == 'silver_plus')
	{
		$wllevels = array("1307486763"); //SILVER PLUS
		$plan = 'Silver Plus';
	}
	else if($level == 'gold')
	{
		$wllevels = array("1304974259"); //GOLD MODULE 1
		$plan = 'Gold Module 1';
	}
	else if($level == 'gold2')
	{
		$wllevels = array("1307569217"); //GOLD MODULE 2
		$plan = 'Gold Module 2';
	}
	else
	{
		$wllevels = array("1304974053"); //SILVER
		$plan = 'Silver';
	}
	
	//CHECK FOR INVALID USERNAME
	if ($IS_DATA['Username'] == '')
	{
		//EMAIL DEFAULT
		$IS_DATA['Username'] 	= $IS_DATA['Email']; 
		
		//UPDATE INFUSIONSOFT
		$data['Username'] 		= $IS_DATA['Email'];
	}
	
	//CHECK FOR INVALID PASSWORD
	if ($IS_DATA['Password'] == '')
	{
		//PASSWORD DEFAULT
		$IS_DATA['Password'] 	= $IS_DATA['FirstName'].'1845';
		
		//UPDATE INFUSIONSOFT
		$data['Password'] 		= $IS_DATA['FirstName'].'1845'; 
	}
	
	$wldata = array(
				'user_login' 	=> $IS_DATA['Username'],
				'user_email' 	=> $IS_DATA['Email'],
				'user_pass' 	=> $IS_DATA['Password'],
				'display_name' 	=> $IS_DATA['FirstName']." ".$IS_DATA['LastName'],
				'first_name' 	=> $IS_DATA['FirstName'],
				'last_name' 	=> $IS_DATA['LastName'],
				'Levels' 		=> $wllevels
			  );
			  
	$wlresponse = $wishlist->post('/members',$wldata);
	
	$wlresponse = unserialize($wlresponse);
	
	//print_r($wlresponse);
	
	//*===============================
	//* WISHLIST SUCCESS
	//*===============================
	if ($wlresponse['success'] == 1){
		
		echo 'SUCCESS. User has been added to the Mojo Video University on the plan: '.$plan;
		
		//*===============================
		//* UPDATE INFUSIONSOFT
		//*===============================	
			
			//STORE WISHLIST INFORMATION
			$data['_WishListId'] = $wlresponse['member'][0]['ID']; // <------------NEED TO CHECK RESPONSE--------------
			
			$data['_WishListLevels'] = implode(",", $wllevels);
			
			//print_r($data);
			
			$update = $infusion->updateCon((int)$contactId,$data);
	
	//*===============================
	//* WISHLIST FAILURE
	//*===============================		
	}else{
		echo "\n".'<br/>Error Code: ' . $wlresponse['ERROR_CODE'];
		echo "\n".'<br/>';
		echo "\n".'Error Description: ' . $wlresponse['ERROR'];
	}
	

	
?>