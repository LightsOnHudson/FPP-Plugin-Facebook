<?php

//$DEBUG=true;
include_once "/opt/fpp/www/common.php";
include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';
include_once "functions.inc.php";


$pluginName = "Facebook";


$logFile = $settings['logDirectory']."/".$pluginName.".log";


if(isset($_POST['submit']))
{
	

//	echo "Writring config fie <br/> \n";
	
	
	//WriteSettingToFile("ENABLED",urlencode($_POST["ENABLED"]),$pluginName);
	WriteSettingToFile("SEPARATOR",urlencode($_POST["SEPARATOR"]),$pluginName);
	WriteSettingToFile("USER",urlencode($_POST["USER"]),$pluginName);
	WriteSettingToFile("APP_ID",urlencode($_POST["APP_ID"]),$pluginName);
	WriteSettingToFile("APP_SECRET",urlencode($_POST["APP_SECRET"]),$pluginName);
	
	WriteSettingToFile("ACCESS_TOKEN",urlencode($_POST["ACCESS_TOKEN"]),$pluginName);
	
	//WriteSettingToFile("LIKE_COUNT",$_POST["LIKE_COUNT"],$pluginName);
	WriteSettingToFile("FACEBOOK_LAST",$_POST["FACEBOOK_LAST"],$pluginName);
	
	if(isset($_POST["RESET_LAST_READ"])) {
		WriteSettingToFile("LAST_READ","0",$pluginName);
	} else {
		WriteSettingToFile("LAST_READ",urlencode($_POST["LAST_READ"]),$pluginName);
	}

}

	
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

	if($SEPARATOR == "") {
		$SEPARATOR="|";
	}
	//echo "sports read: ".$SPORTS."<br/> \n";
	
	if((int)$LAST_READ == 0 || $LAST_READ == "") {
		$LAST_READ=0;
		
	}
	
	if((int)$LIKES == 0 || $LIKES == "") {
		$LIKES=0;
	}
	
	if((int)$FACEBOOK_LAST_INDEX == 0 || $FACEBOOK_LAST_INDEX == "") {
		$FACEBOOK_LAST_INDEX=0;
	}
	
	$gitURL = "https://github.com/LightsOnHudson/FPP-Plugin-Facebook.git";
	
	$pluginUpdateFile = $settings['pluginDirectory']."/".$pluginName."/"."pluginUpdate.inc";
	
	logEntry("Plugin update file: ".$pluginUpdateFile);
	
?>

<html>
<head>
</head>

<div id="<?echo $pluginName;?>" class="settings">
<fieldset>
<legend><?php echo $pluginName;?> Support Instructions</legend>

<p>Known Issues:
<ul>
<li>NONE</li>
</ul>

<p>Configuration:
<ul>
<li>Configure your username: Tokens, etc</li>
</ul>
<ul>
<li>You must enable APP support on Facebook.com</li>
</ul>



<form method="post" action="http://<? echo $_SERVER['SERVER_ADDR']?>/plugin.php?plugin=<?echo $pluginName;?>&page=plugin_setup.php">


<?
echo "<input type=\"hidden\" name=\"LAST_READ\" value=\"".$LAST_READ."\"> \n";
echo "<input type=\"hidden\" name=\"FACEBOOK_LAST\" value=\"".$FACEBOOK_LAST_INDEX."\"> \n";
echo "Like Count: ";
echo "<input disabled type=\"text\" size=\"5\" name=\"LIKE_COUNT\" value=\"".$LIKES."\"> \n";
echo "<p/>\n";
//echo "LIKE COUNT: ".$LIKES."<br/> \n";

$restart=0;
$reboot=0;

echo "ENABLE PLUGIN: ";

//if($ENABLED== 1 || $ENABLED == "on") {
//		echo "<input type=\"checkbox\" checked name=\"ENABLED\"> \n";
PrintSettingCheckbox("Facebook Plugin", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
//	} else {
//		echo "<input type=\"checkbox\"  name=\"ENABLED\"> \n";
//}

echo "<p/> \n";


if($DEBUG) {
	echo "RESET LAST READ INDEX: ";

		echo "<br/> \n";
	echo "Last read: ".$LAST_READ. ": ";
	echo "<input type=\"checkbox\"  name=\"RESET_LAST_READ\"> \n";
	//PrintSettingCheckbox("Radio Station", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");


	echo "<p/> \n";
}	

	echo "<p/> \n";
	
	echo "Facebook Page: \n";
	
	echo "<input type=\"text\" name=\"USER\" size=\"16\" value=\"".$USER."\"> \n";
	
	echo "<p/> \n";
	
	echo "APP ID: \n";
	
	echo "<input type=\"text\" name=\"APP_ID\" size=\"64\" value=\"".$APP_ID."\"> \n";
	
	
	echo "<p/> \n";
	
	echo "APP SECRET: \n";
	
	echo "<input type=\"text\" name=\"APP_SECRET\" size=\"64\" value=\"".$APP_SECRET."\"> \n";
	
	echo "<p/> \n";
	
	echo "Access Token from FB Dev Site: \n";
	
	echo "<input type=\"text\" name=\"ACCESS_TOKEN\" size=\"64\" value=\"".$ACCESS_TOKEN."\"> \n";
	

?>
<p/>
<input id="submit_button" name="submit" type="submit" class="buttons" value="Save Config">
<?
 if(file_exists($pluginUpdateFile))
 {
 	//echo "updating plugin included";
	include $pluginUpdateFile;
}
?>
</form>

<p>To report a bug, please file it against the sms Control plugin project on Git: https://github.com/LightsOnHudson/FPP-Plugin-Facebook

</fieldset>
</div>
<br />
</html>
