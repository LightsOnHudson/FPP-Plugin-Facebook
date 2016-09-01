<?php
//$DEBUG=true;
include_once "/opt/fpp/www/common.php";
include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';



$pluginName = "Facebook";


$logFile = $settings['logDirectory']."/".$pluginName.".log";



$ENABLED = urldecode(ReadSettingFromFile("ENABLED",$pluginName));
$SEPARATOR = urldecode(ReadSettingFromFile("SEPARATOR",$pluginName));
$USER = urldecode(ReadSettingFromFile("USER",$pluginName));
$APP_ID = urldecode(ReadSettingFromFile("APP_ID",$pluginName));
$APP_SECRET = urldecode(ReadSettingFromFile("APP_SECRET",$pluginName));

$ACCESS_TOKEN = urldecode(ReadSettingFromFile("ACCESS_TOKEN",$pluginName));

$LIKES = ReadSettingFromFile("LIKE_COUNT",$pluginName);

//	echo "LIKE: COUNT: ".$LIKES."<br/> \n";
$FACEBOOK_LAST_INDEX = ReadSettingFromFile("FACEBOOK_LAST",$pluginName);


$LAST_READ = urldecode(ReadSettingFromFile("LAST_READ",$pluginName));



$gitURL = "https://github.com/LightsOnHudson/FPP-Plugin-Facebook.git";

$pluginUpdateFile = $settings['pluginDirectory']."/".$pluginName."/"."pluginUpdate.inc";

logEntry("Plugin update file: ".$pluginUpdateFile);

$pageID = $USER;
$fb_base_URL = "https://graph.facebook.com/";
$fields = "likes";
$fields="";


$URL = $fb_base_URL.$pageID."?fields=".$fields."&access_token=".$ACCESS_TOKEN;

echo "URL: ".$URL."\n <br/> \n";

$fb_data = file_get_contents($URL);

echo "FB data: \n";
echo $fb_data;


$fb_array = json_decode($fb_data,true);
echo "<pre>\n";
print_r($fb_array);
echo "</pre> \n";

?>
	