#!/usr/bin/php
<?
//error_reporting(0);

$pluginName ="Facebook";
$myPid = getmypid();

$messageQueue_Plugin = "MessageQueue";
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;

$DEBUG=false;

$skipJSsettings = 1;
include_once("/opt/fpp/www/config.php");
include_once("/opt/fpp/www/common.php");
include_once("functions.inc.php");

require ("lock.helper.php");
include_once("ACCESS_TOKEN_LINK.inc.php");

define('LOCK_DIR', '/tmp/');
define('LOCK_SUFFIX', $pluginName.'.lock');


require_once 'Facebook/autoload.php';
session_start();



$logFile = $settings['logDirectory']."/".$pluginName.".log";

$messageQueuePluginPath = $pluginDirectory."/".$messageQueue_Plugin."/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));

if(file_exists($messageQueuePluginPath."functions.inc.php"))
	{
		include $messageQueuePluginPath."functions.inc.php";
		$MESSAGE_QUEUE_PLUGIN_ENABLED=true;

	} else {
		logEntry("Message Queue Plugin not installed, some features will be disabled");
	}	



//echo "ENABLED: ".$ENABLED."\n";

if(($pid = lockHelper::lock()) === FALSE) {
	exit(0);
}

	
	
	$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
	
	logEntry("Plugin config file: ".$pluginConfigFile);
	
	if (file_exists($pluginConfigFile))
		$pluginSettings = parse_ini_file($pluginConfigFile);
	
	$USER = urldecode($pluginSettings['USER']);
	$CLIENT_ID = urldecode($pluginSettings['APP_ID']);
	$CLIENT_SECRET = urldecode($pluginSettings['APP_SECRET']);
	$FACEBOOK_LAST_INDEX = urldecode($pluginSettings['FACEBOOK_LAST']);
	$ENABLED = urldecode($pluginSettings['ENABLED']);
	
//	$USER = urldecode(ReadSettingFromFile("USER",$pluginName));
//	$CLIENT_ID = urldecode(ReadSettingFromFile("APP_ID",$pluginName));
//	$CLIENT_SECRET = urldecode(ReadSettingFromFile("APP_SECRET",$pluginName));
//	$FACEBOOK_LAST_INDEX = ReadSettingFromFile("FACEBOOK_LAST",$pluginName);

	
	$GRAPH_VERSION = "v2.7";
	
	if($ENABLED != "ON") {
		logEntry("Plugin Status: DISABLED Please enable in Plugin Setup to use & Restart FPPD Daemon");
		lockHelper::unlock();
		exit(0);
	}
	
	 $fbLogin = FacebookLogin("", "");
	 echo $fbLogin."\n";
	 
	$token = FacebookToken();
	
	echo "oken: ".$token;
	
	function FacebookLogin($email, $password) {
		$cookies= 'cookie_file.txt';
		$user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.94 Safari/537.36';
	
		$data = array('charset_test' => htmlspecialchars("&euro;,&acute;,â‚¬,Â´,æ°´,Ð”,Ð„"),
				'lsd' => 'OsC-Z',
				'locale' => 'en_US',
				'email' => $email,
				'pass' => $password,
				'persistent' => 1,
				'default_persistent'=> 0);
		$post = http_build_query($data);
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://www.facebook.com/login.php');
		curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
		curl_setopt($ch, CURLOPT_REFERER, 'https://www.facebook.com/');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_FILETIME, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
		curl_exec($ch);
			
		$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		 
		return $http;
	}
	

	// Grab the access token from the FB API
	function FacebookToken() {
		global $CLIENT_ID;
		$cookies= 'cookie_file.txt';
		$user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.94 Safari/537.36';
	
		// You need to sniff out the client ID below with Charles and switch it for the app that you're targetting
		$client_id = $CLIENT_ID;
		// Sniff out the permissions that the app is requesting with Charles too. They should be comma separated
		$scope = 'email,user_birthday';
		$uri = 'https://www.facebook.com/connect/login_success.html';
		$url = 'https://www.facebook.com/dialog/oauth?client_id='.$client_id.'&redirect_uri='.urlencode($uri).'&scope='.$scope.'&response_type=token';
			
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
		$data = curl_exec($ch);
		$curl_info = curl_getinfo($ch);
	
		// Get the headers and then the HTTP code
		$headers = substr($data, 0, $curl_info['header_size']);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
		// Make sure that the HTTP redirects to a location that has an access token in the URL
		if($code == 302) {
			preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!", $headers, $matches);
			$break = explode("access_token=", $matches[1]);
	
			if(count($break) == 2) {
				// Split the URL once more to get the access token value
				$exp = explode("&", $break[1]);
				$token = $exp[0];
			}  else {
				$token = 'Failed';
			}
		} else {
			$token = 'Failed';
		}
			
		return $token;
	}
print "<a href=\"index.php\">RELOAD</a>";
//	print_r($plainOldArray);
	
	lockHelper::unlock();
	exit(0);
	
	if($SEPARATOR == "") {
		$SEPARATOR="|";
	}
	//echo "sports read: ".$SPORTS."<br/> \n";
	
	if((int)$LAST_READ == 0 || $LAST_READ == "") {
		$LAST_READ=0;
	}
	
	if((int)$FACEBOOK_LAST_INDEX == 0 || $FACEBOOK_LAST_INDEX == "") {
		$FACEBOOK_LAST_INDEX=0;
	}


	$METADATA=true;
	


// Skip these two lines if you're using Composer

$url = $ACCESS_TOKEN_URL.$CLIENT_ID."&secret=".$CLIENT_SECRET;


$accessToken = getFPPAccessToken($url);

if($DEBUG)
echo "token: ".$token."\n";



FacebookSession::setDefaultApplication($CLIENT_ID,$CLIENT_SECRET);


//$accessToken = $token;

sleep(2);



$session = new FacebookSession( $accessToken );

if($DEBUG)
	echo print_r($session);


// see if we have a session
if ( isset( $session ) ) {


//$URL = '/johnsonlightshow/feed?metadata=1';
$URL = "/".$USER."/feed";


if($METADATA) {
	$URL .= "?metadata=1";
}

$newMessages = array();

	$request = new FacebookRequest(
			$session,
			'GET',
			$URL

	);
	$response = $request->execute();
	$graphObject = $response->getGraphObject();
	$fbarray = $graphObject->asArray();
	
	
//	echo '<pre>' . print_r( $fbarray, 1 ) . '</pre>';
	
	$newestPost = $fbarray['data'][0]->created_time;
	$newestMessage = $fbarray['data'][0]->message;
	$newestType = $fbarray['data'][0]->type;
	
	//echo "newest post: ".$newestPost." Unix time: ".strtotime($newestPost)."\n";
//	echo "newest message: ".$newestMessage."\n";
//	echo "newest type: ".$newestType."\n";
	
	logEntry("Writing high water mark for Facebook: ".strtotime($newestPost));
	WriteSettingToFile("FACEBOOK_LAST",strtotime($newestPost),$pluginName);
	
	//check to see if these posts are older... becuase the latest post is always at the top.
	
	$totalMessageCount = count($fbarray['data']);
	logEntry("total messages to process: ".$totalMessageCount);
	
	//$FACEBOOK_LAST_INDEX=0;
	$fbMessageIndex =0;
	for($fbMessageIndex=0;$fbMessageIndex<=$totalMessageCount-1;$fbMessageIndex++ ) {
		
		$postTime = $fbarray['data'][$fbMessageIndex]->created_time;
		$messageText = $fbarray['data'][$fbMessageIndex]->message;
		$messageType = $fbarray['data'][$fbMessageIndex]->type;
		
		//do not include blank messages for some reason they may get in there
		if(trim($messageText)=="") {
			//do not include
			continue;
			
		}
	
	if($FACEBOOK_LAST_INDEX < strtotime($postTime) && $messageType == "status"){
		logEntry("new post: ".$postTime." Mesage: ".$messageText);
		$newMessages[] = array($messageText,$fbarray['data'][$fbMessageIndex]->created_time);
	//	addNewMessage($messageText,$pluginName,$fbarray['data'][$fbMessageIndex]->created_time);
	} else {
		if($DEBUG){
			logEntry("NOT NEW message : ".$messageText);
		}
	}
	
	}
	// print data

	krsort($newMessages);
	//print_r($newMessages);
	
	
	
	//output the messages in oldest to newest so they appear on the matrix as they were 'coming in'
	$sortedMessagesCount = count($newMessages);
	
	for($sortedMessageIndex=count($newMessages)-1;$sortedMessageIndex>=0;$sortedMessageIndex--) {
		addNewMessage($newMessages[$sortedMessageIndex][0],$pluginName,$newMessages[$sortedMessageIndex][1]);
		
	}
	//number of lines for latest post...
	
	//id =10204056386742286;

	$status_count = count($fbarray['data']);
	
	//echo "status count: ".$status_count."\n";
	
	//echo " id of latest status: ".$fbarray['data'][0]->id."\n";
	
	$like_count =0;
	
	//echo "count of likes: ".count($fbarray['data'][0]->likes->data)."\n";
	
	
	//
	
	
	//$URL = '/johnsonlightshow?metadata=1';
	
	$URL = "/".$USER;//
	//."/feed";
	
	
	if($METADATA) {
		$URL .= "?metadata=1";
	}
		$request = new FacebookRequest(
			$session,
			'GET',
			$URL
	
	);
	$response = $request->execute();
	$graphObject = $response->getGraphObject();
	$fbarray = $graphObject->asArray();
	
	
	// print data
	//echo '<pre>' . print_r( $fbarray, 1 ) . '</pre>';
	
	//number of lines for latest post...
	
	//id =10204056386742286;
	
	$status_count = count($fbarray['data']);
	
	//echo "status count: ".$status_count."\n";
	
	//echo " id of latest status: ".$fbarray['data'][0]->id."\n";
	
	$like_count =0;
	
	//echo "count of likes: ".count($fbarray['data'][0]->likes->data)."\n";
	
	$mainLikes = $fbarray['likes'];
	logEntry("Main like count: ".$mainLikes);
	WriteSettingToFile("LIKE_COUNT",$mainLikes,$pluginName);
	//echo "Main like count: ".$mainLikes."\n";
} else {
	logEntry("No success in connecting to facebook api");
	echo" No session from FB \n";
}

logEntry("New message count: ".count($newMessages));
lockHelper::unlock();
exit(0);
?>
