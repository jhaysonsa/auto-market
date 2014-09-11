<?php


$key = preg_replace("/[^A-Za-z0-9?!]/",'', $_GET['key']);
if ($key != "54321"){
	die("Unauthorized Access Attempt. The Administrators have been notified.");
}


$action = preg_replace("/[^A-Za-z0-9?!]/",'', $_GET['action']);
$actions= preg_replace("/[^A-Za-z0-9?!]/",'', $_GET['action']);
if ($action == ""){
	die("No action specified.");
}

$contact_id = preg_replace("/[^0-9?!]/",'', $_GET['contact_id']);

if ($contact_id == ""){
	die("No contact ID was specified.");
}

$membership = preg_replace("/[^A-Za-z0-9?!]/",'', $_GET['membership']);

if ($membership == ""){
	die("No membership program specified.");
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
//* INFUSIONSOFT
//*=================

	//INFUSIONSOFT
	$connInfo = array('main:mojo:i:679c2a2a6aef93984f48e3ec4794db26:Mojo Video');
	$infusion = new iSDK;
	$infusion->cfgCon("main");

//*=================
//* WISHLIST
//*=================

	if($membership === 'mojoleadmastery')
	{
		$api= new wlmapiclass('http://www.mojoleadmastery.com/members', '5ae8c42d96a15001cfd6b70de330b70b');
	}
	
	else if(!is_object($api))
		die('Invalid membership program specified.');

	$api->return_format = 'php';
	
//*===============================
//* GET USER INFORMATION FROM IS
//*===============================	

	$fields = array('FirstName', 'LastName','Email','Phone1','Company','Username','Password','_WishListId','_WishListLevels');
	$IS_DATA = $infusion->loadCon((int)$contact_id, $fields);

//*==============================================
//* POST TO WISHLIST
//*==============================================	

	//ADD
if($actions ===  'add')
{
		
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
							$IS_DATA['Password'] 	= $IS_DATA['FirstName'].'524193';
							
									
							//UPDATE INFUSIONSOFT
							$data['Password'] 		= $IS_DATA['FirstName'].'524193'; 
						}




					  $password	= $IS_DATA['FirstName'].'524193';
					  $args = array(
							  'user_login'   => $IS_DATA['Email'],
							  'user_email'   => $IS_DATA['Email'],
							  'user_pass'    => $password,
							  'display_name' => $IS_DATA['FirstName']." ".$IS_DATA['LastName'],
							  'first_name' 	 => $IS_DATA['FirstName'],
							  'last_name' 	 => $IS_DATA['LastName'],
							  'Levels'       => array($level)
						 );
						 
						$wlresponse  = wlmapi_add_member($args);
					//*===============================
					//* WISHLIST SUCCESS
					//*===============================
						if ($wlresponse['success'] == 1)
						{
							
							echo 'SUCCESS. Performed '.$action.' on ContactId '.$contact_id.' on membership '.$membership;
							
							 echo ' username: '.$IS_DATA['Email'];
							 echo ' password: '.$password;
							//*===============================
							//* UPDATE INFUSIONSOFT
							//*===============================	
								
								//STORE WISHLIST INFORMATION
								if(!empty($wlresponse['member'][0]['ID']))
								{
									$data['_WishListId'] = $wlresponse['member'][0]['ID'];
								
								$data['_WishListLevels'] = $level;
								
								//print_r($data);
								
								$update = $infusion->updateCon((int)$contact_id,$data);
								 }
						//*===============================
						//* WISHLIST FAILURE
						//*===============================		
						}			
						else
						{
							echo "\n".'<br/>Error Code: ' . $wlresponse['ERROR_CODE'] .$IS_DATA['Email'].$password;
							echo "\n".'<br/>';
							echo "\n".'Error Description: ' . $wlresponse['ERROR'];
						}

}
	
	
	
else if($actions === 'cancel')
	{

		//PREPARE ARRAY
		$wldata = array(
					'RemoveLevels' 	=> array($level)
				);
		
		//CANCEL USER
		$wlresponse = $api->put('/levels/'.$IS_DATA['_WishListId'],$wldata);
	}
	
	
	
	
	
//UPGRADE
else if($actions ===  'update')
	{
//GET LEVEL TO REMOVE FROM IS
		
   
	
		$args = array(
					'Levels' 	=> array($level)
					
				);
		 
		
		
	 
     $wlresponse= wlmapi_update_member($IS_DATA['_WishListId'],$args);

	 
	

	 }

?>