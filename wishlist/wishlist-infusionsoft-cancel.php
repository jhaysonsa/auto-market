<?php

//* mojovideomarketing.com/wishlist/wishlist-infusionsoft-cancel.php?key=298xbkmd9dfl&level=mojofull&Id=25
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
include('../members/wp-config.php');		
include('../members/wp-includes/pluggable.php');
//*=================
	
//*=================
//* CONFIGURATION
//*=================

	//INFUSIONSOFT
	$connInfo = array('main:mojo:i:679c2a2a6aef93984f48e3ec4794db26:Mojo Video');
	$infusion = new iSDK;
	$infusion->cfgCon("main");
	
	//WISHLISTMEMBER
	$wishlist = new wlmapiclass('http://www.mojoleadmastery.com/members', 'LXbfqw59CFNUWaexLXbfqw59CFNUWaex');
	$wishlist->return_format = 'php'; // <- value can also be xml or json
	
	
//*===============================
//* GET USER INFORMATION FROM IS
//*===============================	

	$fields = array('FirstName', 'LastName','Email','Phone1','Company','Username','Password');
	$IS_DATA = $infusion->loadCon((int)$contactId, $fields);
	
//*==============================================
//* POST TO MOJO Lead Mastery
//* http://mojovideouniversity.com/
//*==============================================	
	
	//SET LEVEL
	if($level == 'mojofull')
	{
		$wllevels = array("1409152379"); //mojo full
		$member= 'Mojo Lead Mastery';
	}
	
	else if($level == 'mojoannual')
	{
		$wllevels = array("1409698826"); //mojo annual
		$member = 'Mojo Lead Mastery Annual';
	}
	else
	{
		$wllevels = array("1409152584"); //mojo trial
		$member = 'Mojo Lead Mastery Trial';
	}
	

	//PREPARE ARRAY
		$wldata = array(
					'RemoveLevels' 	=> array($level)
				);
		
		//CANCEL USER
		$wlresponse = $api->put('/levels/'.$IS_DATA['_WishListId'],$wldata);
	

	
?>