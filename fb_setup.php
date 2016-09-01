<?php
session_start();

//$DEBUG=true;
include_once "/opt/fpp/www/common.php";
include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';
include_once "functions.inc.php";

require_once 'Facebook/autoload.php';
$pluginName = "Facebook";


$logFile = $settings['logDirectory']."/".$pluginName.".log";


$ENABLED = urldecode(ReadSettingFromFile("ENABLED",$pluginName));
$SEPARATOR = urldecode(ReadSettingFromFile("SEPARATOR",$pluginName));
$USER = urldecode(ReadSettingFromFile("USER",$pluginName));
$APP_ID = urldecode(ReadSettingFromFile("APP_ID",$pluginName));
$APP_SECRET = urldecode(ReadSettingFromFile("APP_SECRET",$pluginName));


$gitURL = "https://github.com/LightsOnHudson/FPP-Plugin-Facebook.git";

$pluginUpdateFile = $settings['pluginDirectory']."/".$pluginName."/"."pluginUpdate.inc";

logEntry("Plugin update file: ".$pluginUpdateFile);

$fb = new Facebook\Facebook([
		'app_id' => $APP_ID, // Replace {app-id} with your app id
		'app_secret' => $APP_SECRET,
		'default_graph_version' => 'v2.2',
		
]);

$REDIRECT_URL = "http://65.102.234.186/plugin.php?plugin=Facebook&page=fb_callback.php";

$helper = $fb->getRedirectLoginHelper();

$permissions = ['email']; // Optional permissions
$loginUrl = $helper->getLoginUrl($REDIRECT_URL, $permissions);

echo "<a href=\"".htmlspecialchars($loginUrl) ."\">Login with facebook!</a> \n";

?>