#!/usr/bin/php
<?
error_reporting(0);

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
define('LOCK_SUFFIX', '.lock');

define('FACEBOOK_SDK_V4_SRC_DIR', 'src/Facebook/');
require ('autoload.php');

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
use Facebook\FacebookRedirectLoginHelper;


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



$ENABLED = urldecode(ReadSettingFromFile("ENABLED",$pluginName));

//echo "ENABLED: ".$ENABLED."\n";

if(($pid = lockHelper::lock()) === FALSE) {
	exit(0);
}

	if($ENABLED != "on" && $ENABLED != "1") {
		logEntry("Plugin Status: DISABLED Please enable in Plugin Setup to use & Restart FPPD Daemon");
		lockHelper::unlock();
		exit(0);
	}
	
	$USER = urldecode(ReadSettingFromFile("USER",$pluginName));
	$CLIENT_ID = urldecode(ReadSettingFromFile("APP_ID",$pluginName));
	$CLIENT_SECRET = urldecode(ReadSettingFromFile("APP_SECRET",$pluginName));
	$FACEBOOK_LAST_INDEX = ReadSettingFromFile("FACEBOOK_LAST",$pluginName);
	
	
	$LAST_READ = urldecode(ReadSettingFromFile("LAST_READ",$pluginName));
	
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


//echo "token: ".$token."\n";



FacebookSession::setDefaultApplication($CLIENT_ID,$CLIENT_SECRET);


//$accessToken = $token;

sleep(2);



$session = new FacebookSession( $accessToken );


// see if we have a session
if ( isset( $session ) ) {


//$URL = '/johnsonlightshow/feed?metadata=1';
$URL = "/".$USER."/feed";


if($METADATA) {
	$URL .= "?metadata=1";
}

$newMessages = array();

//$URL .= "?metadata=1";
//$URL = "/742926142453427/statuses?access_token=".$accessToken;  //938883646129465%7CJsYHSQGa7jQsvwv0vgb4bEJaCfg
	// graph api request for public post data
	//$response = (new FacebookRequest( $session, 'GET', '/2169877918817?metadata=1') )->execute()->getGraphObject()->asArray();
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
	//$URL .= "?metadata=1";
	//$URL = "/742926142453427/statuses?access_token=".$accessToken;  //938883646129465%7CJsYHSQGa7jQsvwv0vgb4bEJaCfg
	// graph api request for public post data
	//$response = (new FacebookRequest( $session, 'GET', '/2169877918817?metadata=1') )->execute()->getGraphObject()->asArray();
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
