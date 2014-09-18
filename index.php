<?php

set_time_limit(0);
ini_set('default_socket_timeout', 300);
session_start();

/*--------------- Instagram API Keys ---------------*/
/*----------- To be changed accordingly ------------*/

define("clientID", '2117435213f14f5c8ac916835100bb29');
define("clientSecret", '531ae82aa2da4ba4b8e2d4651c86c7bd');
define("redirectURI", 'https://github.com/hizamkamis');
define("imageDirectory", 'pics/'); 

/*--------------------------------------------------*/


//Connect direct to Instagram
function connectToInstagram($url){
	$ch = curl_init();						
	
	curl_setopt_array($ch, array(			
		CURLOPT_URL => $url,				
		CURLOPT_RETURNTRANSFER => true,		
		CURLOPT_SSL_VERIFYPEER => false,	
		CURLOPT_SSL_VERIFYHOST => 2			
	));

	$result = curl_exec($ch);				
	curl_close($ch);						
	return $result;							
}


//Get Instagram userID
function getUserID($userName){
	$url = 'https://api.instagram.com/v1/users/search?q='. $userName .'&client_id='. clientID;
	$instagramInfo = connectToInstagram($url);
	$results = json_decode($instagramInfo, true); 	

	return $results['data'][0]['id'];				
}


//Save picture
function savePicture($image_url){
	echo $image_url . '<br />';
	$filename = basename($image_url);
	echo $filename . '<br />';
	//SELECT * FROM pics WHERE filename=$filename ---- if no matches, continue
	$destination = imageDirectory.$filename;
	file_put_contents($destination, file_get_contents($image_url));
}


//Print out the images
function printImages($userID){
	$url = 'https://api.instagram.com/v1/users/'. $userID .'/media/recent?client_id='. clientID .'&count=5';
	$instagramInfo = connectToInstagram($url);
	$results = json_decode($instagramInfo, true);
	
	//parse through results
	foreach($results['data'] as $item){
		$image_url = $item['images']['low_resolution']['url'];
		echo '<img src="'.$image_url.'" /> <br/>';
		savePicture($image_url);
	}
}


//Get user code and save info to session variables
if($_GET['code']){
	$code = $_GET['code'];
	$url = "https://api.instagram.com/oauth/access_token";
	$access_token_settings = array(
			'client_id'                =>     clientID,
			'client_secret'            =>     clientSecret,
			'grant_type'               =>     'authorization_code',
			'redirect_uri'             =>     redirectURI,
			'code'                     =>     $code
	);
	$curl = curl_init($url);    									
	curl_setopt($curl,CURLOPT_POST,true);   						
	curl_setopt($curl,CURLOPT_POSTFIELDS,$access_token_settings);   
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);   				
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);   			
	$result = curl_exec($curl);   									
	curl_close($curl);   											

	$results = json_decode($result,true);
	
	$userName = $results['user']['username']; 
	$userID = getUserID($userName);
	printImages($userID);
	
}

else( ?> 

<!doctype html>
<html>
<body>
	<a href="https://api.instagram.com/oauth/authorize/?client_id=<?php echo clientID; ?>&redirect_uri=<?php echo redirectURI; ?>&response_type=code">Login</a>
</body>
</html>

<?php

?>

 