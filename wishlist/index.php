<?php
include('../members/wp-config.php');		
		include('../members/wp-includes/pluggable.php');
//INFUSIONSOFT
	//include('includes/isdk.php');
	
	//WISHLISTMEMBER
	//include('includes/wlmapiclass.php');
//$api = new wlmapiclass(API URL,API_KEY);
//include('wishlist-infusionsoft-bridge.php');
//$api = new wlmapiclass(API_URL);
define('API_URL','http://mojoleadmastery.com/members');
define('API_KEY','5ae8c42d96a15001cfd6b70de330b70b');
$api = new wlmapiclass(API_URL,API_KEY);
 $args = array(
          'user_login' => 'jason',
          'user_email' => 'sonahj00@gmail.com',
          'Levels' => array(1409152584)
     );
     $member = wlmapi_add_member($args);
     print_r($member);
/*
$api->return_format ='php';
$level_id = 1409152584;
$response = $api->get('/levels/'.$level_id );
$level=unserialize($response);

 
print_r($level);*/

?>