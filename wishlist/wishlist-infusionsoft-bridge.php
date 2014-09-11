<?php
/**
 * Wishlist Infusionsoft Bridge
 * 
 * Creates/cancels/upgrades a wishlist member account for a contact in Infusionsoft.
 * 
 * @modified 	 	09/11/2013
 * @author 	 	 	Neal Lambert
 * @param string 	$key 			unique access key
 * @param int 		$contact_id 	Infusionsoft ContactId
 * @param string 	$membership 	the wishlist memeber service
 * @param string 	$action 		the wishlist memeber service
 * @param string 	$level 			the wishlist membership level
 * @example http://mojoleadmastery.com/wishlist/wishlist-infusionsoft-bridge.php?action=add&contact_id=~57987~&membership=mojoleadmastery&level=
 *  Free Trial Level =
 *  Monthly Level =
 *  Annual Level =
 *
 */

//*=================
//* PARAMETERS
//*=================


//$key = preg_replace("/[^A-Za-z0-9?!]/",'', $_GET['key']);
$key = '5ae8c42d96a15001cfd6b70de330b70b';
if ($key != "5ae8c42d96a15001cfd6b70de330b70b"){
	die("Unauthorized Access Attempt. The Administrators have been notified.");
}


$action = preg_replace("/[^A-Za-z0-9?!]/",'', $_GET['action']);
if ($action == "Free Trial"){
	die("No action specified.");
}

$contact_id = preg_replace("/[^0-9?!]/",'', $_POST['Id']);
$contact_id = 57987;
if ($contact_id == ""){
	die("No contact ID was specified.");
}

$membership = preg_replace("/[^A-Za-z0-9?!]/",'', $_GET['membership']);
$membership = 'mojoleadmastery';
if ($membership == ""){
	die("No membership program specified.");
}

$level = preg_replace("/[^A-Za-z0-9?!]/",'', $_GET['level']);
$level = 1409152584;
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
		$wishlist = new wlmapiclass('http://www.mojoleadmastery.com/', '5ae8c42d96a15001cfd6b70de330b70b');
	}
	
	else if(!is_object($wishlist))
		die('Invalid membership program specified.');

	$wishlist->return_format = 'php';
	
//*===============================
//* GET USER INFORMATION FROM IS
//*===============================	

	$fields = array('FirstName', 'LastName','Email','Phone1','Company','Username','Password','_WishListId','_WishListLevels');
	$IS_DATA = $infusion->loadCon((int)$contact_id, $fields);
	
//*==============================================
//* POST TO WISHLIST
//*==============================================	

	//ADD
	if($action === 'add')
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
			$data['Password'] 		= 
			
			//UPDATE INFUSIONSOFT
			$data['Password'] 		= $IS_DATA['FirstName'].'524193'; 
		}
		$password='';
		include('../members/wp-config.php');		
		include('../members/wp-includes/pluggable.php');
		/*$password=wp_hash_password($IS_DATA['Password']);*/	
		$password=wp_hash_password($IS_DATA['Password']);	
		$wldata = array(
					'user_login' 	=> $IS_DATA['Username'],
					'user_email' 	=> $IS_DATA['Email'],
					'user_pass' 	=> $password,
					'display_name' 	=> $IS_DATA['FirstName']." ".$IS_DATA['LastName'],
					'first_name' 	=> $IS_DATA['FirstName'],
					'last_name' 	=> $IS_DATA['LastName'],
					'Levels' 		=> array($level)
				  );
				
		//ADD NEW USER  
		//echo $IS_DATA['Password'].' '.$wldata['user_login'];
		//echo print_r($wldata);
		$db_connect=mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)or die(mysqli_error('cant connect to database'));
		$insert_string="INSERT INTO wp_users".
					"(user_login, user_pass, user_nicename, user_email, user_url, user_registered, user_activation_key, user_status, display_name)".
				"VALUES('".$wldata['user_login']."','".$wldata['user_pass']."','".$wldata['display_name']."', '".$wldata['user_email']."','','".date("Y-m-d h:i:s")."','',0,'".$wldata['first_name']."')";
		$insert_query=mysqli_query($db_connect,$insert_string) or die(mysqli_error($db_connect));
		$user_id=mysqli_insert_id($db_connect);
		//END ADD NEW USER  
		//ADD NEW USERMETA
		$user_meta=array($wldata['first_name'],$wldata['last_name'],$wldata['display_name'],
						'','true','false',
						'fresh','0','true',
						'a:1:{s:10:"subscriber";b:1;}','0','wp350_media,wp360_revisions,wp360_locks,wp390_widgets');
		add_user_meta_val($user_meta, $user_id);
		//END ADD NEW USERMETA
		
		
		$wlresponse = $wishlist->post('/members',$wldata);
	}
	//CANCEL
	else if($action === 'cancel')
	{

		//PREPARE ARRAY
		$wldata = array(
					'RemoveLevels' 	=> array($level)
				);
		
		//CANCEL USER
		$wlresponse = $wishlist->put('/members/'.$IS_DATA['_WishListId'],$wldata);
	}
	//UPGRADE
	else if($action === 'upgrade')
	{

		//GET LEVEL TO REMOVE FROM IS
		$wllevels_remove = explode(',', $IS_DATA['_WishListLevels']);
		
		if(empty($wllevels_remove))
			$wllevels_remove = $IS_DATA['_WishListLevels'];
		
		//PREPARE ARRAY
		$wldata = array( 'Levels' 		=> array($level));
		
		if(!empty($wllevels_remove))
			$wldata['RemoveLevels'] = $wllevels_remove;
		
		//UPGRADE USER
		$wlresponse = $wishlist->put('/members/'.$IS_DATA['_WishListId'],$wldata);
	}


	//GET RESPONSE FOR WISHLIST
	$wlresponse = unserialize($wlresponse);
	//print_r($wlresponse);
	

	//*===============================
	//* WISHLIST SUCCESS
	//*===============================
	if ($wlresponse['success'] == 1)
	{
		
		echo 'SUCCESS. Performed '.$action.' on ContactId '.$contact_id.' on membership '.$membership;
		
		//*===============================
		//* UPDATE INFUSIONSOFT
		//*===============================	
			
			//STORE WISHLIST INFORMATION
			if(!empty($wlresponse['member'][0]['ID']))
				$data['_WishListId'] = $wlresponse['member'][0]['ID'];
			
			$data['_WishListLevels'] = $level;
			
			//print_r($data);
			
			$update = $infusion->updateCon((int)$contact_id,$data);
	
	//*===============================
	//* WISHLIST FAILURE
	//*===============================		
	}
	else
	{
		echo "\n".'<br/>Error Code: ' . $wlresponse['ERROR_CODE'];
		echo "\n".'<br/>';
		echo "\n".'Error Description: ' . $wlresponse['ERROR'];
	}
function add_user_meta_val($user_meta_values=array(),$user_id)
{
	$db_connect=mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)or die(mysqli_error('cant connect to database'));
	$meta_options=array('first_name','last_name','nickname',
						'description','rich_editing','comment_shortcuts',
						'admin_color','use_ssl','show_admin_bar_front',
						'wp_capabilities','wp_user_level','dismissed_wp_pointers');
	$counter=0;
	$meta_string="INSERT INTO wp_usermeta (user_id, meta_key, meta_value) VALUES";
	while($counter < count($user_meta_values)):
		$meta_string.="(".$user_id.", '".$meta_options[$counter]."', '".$user_meta_values[$counter]."')";
		if($counter == (count($user_meta_values)-1)):
			$meta_string.=';';
		else:
			$meta_string.=',';
		endif;
		$counter++;
	endwhile;
	//echo $meta_string;
	mysqli_query($db_connect,$meta_string) or die(mysql_error($db_connect));
}

?>