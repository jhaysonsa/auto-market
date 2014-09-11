
<?php
/*************************************************************************************************************
 * This script belongs to Mojo Video Marketing for Mojo Lead Mastery                                         *
 * Last Update: 9/7/2014                                                                                     *
 * It creates a new user account from the Infusionsoft contact record                                        *
 * specified.                                                                                                *
 *                                                                                                           * 
 * Parameters:                                                                                               *
 * Key - Unique Key to Access Wishlist-Infusionsoft-add.php                                                  *
 * Id  - Infusionsoft Contact Id                                                                             *
 * Mojo Lead Mastery Level:                                                                                  *
 * Mojo Lead Mastery Trial - Product# 1409152584                                                             *
 * Mojo Lead Mastery Monthly - Product# 1409152379                                                           *   
 * Mojo Lead Mastery Yearly - Product# 1409698826                                                            * 
 *                                                                                                           *
 * To run script:                                                                                            *
 * Make an HTTP Post to the following URL:                                                                   *
 * mojoleadmastery.com/wishlist/wishlist-infusionsoft-add.php?key=298xbkmd9dfl&level=1409152584              * 
 *                                                                                                           *
 *                                                                                                           *
 *************************************************************************************************************/                                                                                                       

$key = preg_replace("/[^A-Za-z0-9?!]/",'', $_GET['key']);                                                                       
if ($key != "298xbkmd9dfl"){
    die("Unauthorized Access Attempt. The Administrators have been notified.");
}


$order_id = preg_replace("/[^0-9?!]/",'', $_GET['orderId']);
if ($order_id == ""){
    die("No order ID was specified.");
}

$level = preg_replace("/[^A-Za-z0-9?!]/",'', $_GET['level']);
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
	
//*=================
//* WISHLIST
//*=================

	//WISHLISTMEMBER
	$wishlist = new wlmapiclass('http://www.mojoleadmastery.com/members', 'LXbfqw59CFNUWaexLXbfqw59CFNUWaex');
	$wishlist->return_format = 'php'; // <- value can also be xml or json
	
	
//*===============================
//* GET USER INFORMATION FROM IS
//*===============================	
	$returnFields = array('contactId');
	$query = array('Id' => $order_id);
	$contacts = $infusion->dsQuery("Job",1,0,$query,$returnFields); 

	$fields = array('FirstName', 'LastName','Email','Phone1','Company','Username','Password');
	$IS_DATA = $infusion->loadCon($contacts[0]['contactId'], $fields);
	
//*==============================================
//* POST TO MOJO Lead Mastery
//* http://mojoleadmastery.com/
//*==============================================	
	
	//SET LEVEL
	//if($level == 'MLMMonthly')
	//{
	//	$wllevels = array("1409152379"); //mojo full
	//	$member= 'Mojo Lead Mastery';
	//}
	//
	//else if($level == 'MLMYearly')
	//{
	//	$wllevels = array("1409698826"); //mojo annual
	//	$member = 'Mojo Lead Mastery Annual';
	//}
	//else
	//{
	//	$wllevels = array("1409152584"); //mojo trial
	//	$member = 'Mojo Lead Mastery Trial';
	//}
	
	//CHECK FOR INVALID USERNAME
	if ($IS_DATA['Username'] == '')
	{
		//EMAIL DEFAULT
		$IS_DATA['Username'] 	= $IS_DATA['Email']; 
		
		//UPDATE INFUSIONSOFT
		$data['Username'] 	= $IS_DATA['Email'];
	}
	
	//CHECK FOR INVALID PASSWORD
	if ($IS_DATA['Password'] == '')
	{
		//PASSWORD DEFAULT
		$IS_DATA['Password'] 	= $IS_DATA['FirstName'].'524193';
		
		//UPDATE INFUSIONSOFT
		$data['Password'] 		= $IS_DATA['FirstName'].'524193'; 
	}
	
        $password	= $IS_DATA['FirstName'].'524193';
	$wldata = array(
				'user_login' 	=> $IS_DATA['Username'],
				'user_email' 	=> $IS_DATA['Email'],
				'user_pass' 	=> $password,
				'display_name' 	=> $IS_DATA['FirstName']." ".$IS_DATA['LastName'],
				'first_name' 	=> $IS_DATA['FirstName'],
				'last_name' 	=> $IS_DATA['LastName'],
				'Levels' 		=> array($level)
			  );
			  
		$wlresponse  = wlmapi_add_member($wldata);
	
	
	//print wlresponse
	print_r($wlresponse);
	
	//*===============================
	//* WISHLIST SUCCESS
	//*===============================
	if ($wlresponse['success'] == 1){
		
		echo 'SUCCESS. User has been added to the Mojo lead Mastery: '.$member;
		
		//*===============================
		//* UPDATE INFUSIONSOFT
		//*===============================	
			
			//STORE WISHLIST INFORMATION
			$data['_WishListId'] = $wlresponse['member'][0]['ID']; // <------------NEED TO CHECK RESPONSE--------------
			
			$data['_WishListLevels'] = $level;
			
			//print_r($data);
			
			$update = $infusion->updateCon((int)$order_id,$data);
	
	//*===============================
	//* WISHLIST FAILURE
	//*===============================		
	}else{
		echo "\n".'<br/>Error Code: ' . $wlresponse['ERROR_CODE'];
		echo "\n".'<br/>';
		echo "\n".'Error Description: ' . $wlresponse['ERROR'];
	}
	

	
?>
Displaying wishlist-infusionsoft-add.php.