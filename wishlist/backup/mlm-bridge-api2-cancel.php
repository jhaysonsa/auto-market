<?php
//*============================================
//*	Page: mojovu-bridge-api2-cancel.php
//*	Last Modified: 01/18/2011 by Neal Lambert
//*	Author: Neal Lambert
//*	Description: Cancels a Mojo Video University (Wishlist Member)
//* user account. Updated to support Wishlist Member API Version 2.0
//*
//* Params: 
//* key 	- unique key to acess script
//* Id		- Infusionsoft contact record id
//*
//* Example Usage:
//* Make an HTTP POST to the following URL:
//* mojovideomarketing.com/wishlist/mojovu-bridge-api2-cancel.php?key=298xbkmd9dfl&Id=25
//*============================================

$key = $_GET['key'];
if ($key != "298xbkmd9dfl"){
	die("Unauthorized Access Attempt. The Administrators have been notified.");
}

$contactId = $_REQUEST['Id'];
if ($contactId == ""){
	die("No wishlist member ID was specified.");
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

	$fields = array('FirstName', 'LastName','Email','Phone1','Company','Username','Password','_WishListId','_WishListLevels');
	$IS_DATA = $infusion->loadCon((int)$contactId, $fields);
	
//*==============================================
//* POST TO MOJO VIDEO UNIVERSITY
//* http://mojovideouniversity.com/
//*==============================================	
	//GET LEVEL TO REMOVE FROM
	
	$wllevels_remove = explode(',',$IS_DATA['_WishListLevels']);
	
	if(!$wllevels_remove)
		$wllevels_remove = $IS_DATA['_WishListLevels'];
	
	//PREPARE ARRAY
	
	$wldata = array(
				'RemoveLevels' 	=> $wllevels_remove
			);
	
	//UPDATE USER
	
	$wlresponse = $wishlist->put('/members/'.$IS_DATA['_WishListId'],$wldata);
	
	$wlresponse = unserialize($wlresponse);
	
	//print_r($wlresponse);
	
	//*===============================
	//* WISHLIST SUCCESS
	//*===============================
	if ($wlresponse['success'] == 1){
		
		echo 'SUCCESS. Users Mojo Video University account has been canceled.';
		
		//*===============================
		//* UPDATE INFUSIONSOFT
		//*===============================	
			
			$data['_WishListLevels'] = '';
			
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