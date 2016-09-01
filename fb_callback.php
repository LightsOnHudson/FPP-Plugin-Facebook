<?php

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

if(isset($_GET['access_token'])) {
	$accessToken = $_GET['access_token'];
	echo "Access token = ".$accessToken;

	logEntry("Access token: ".$accessToken);


}
$fb = new Facebook\Facebook([
		'app_id' => $APP_ID, // Replace {app-id} with your app id
		'app_secret' => $APP_SECRET,
		'default_graph_version' => 'v2.2',
]);

$helper = $fb->getRedirectLoginHelper();

try {
	$accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
	// When Graph returns an error
	echo 'Graph returned an error: ' . $e->getMessage();
	exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
	// When validation fails or other local issues
	echo 'Facebook SDK returned an error: ' . $e->getMessage();
	exit;
}

if (! isset($accessToken)) {
	if ($helper->getError()) {
		header('HTTP/1.0 401 Unauthorized');
		echo "Error: " . $helper->getError() . "\n";
		echo "Error Code: " . $helper->getErrorCode() . "\n";
		echo "Error Reason: " . $helper->getErrorReason() . "\n";
		echo "Error Description: " . $helper->getErrorDescription() . "\n";
	} else {
		header('HTTP/1.0 400 Bad Request');
		echo 'Bad request';
	}
	exit;
}

// Logged in
echo '<h3>Access Token</h3>';
var_dump($accessToken->getValue());

// The OAuth 2.0 client handler helps us manage access tokens
$oAuth2Client = $fb->getOAuth2Client();

// Get the access token metadata from /debug_token
$tokenMetadata = $oAuth2Client->debugToken($accessToken);
echo '<h3>Metadata</h3>';
var_dump($tokenMetadata);

// Validation (these will throw FacebookSDKException's when they fail)
$tokenMetadata->validateAppId($APP_ID); // Replace {app-id} with your app id
// If you know the user ID this access token belongs to, you can validate it here
//$tokenMetadata->validateUserId('123');
$tokenMetadata->validateExpiration();

if (! $accessToken->isLongLived()) {
	// Exchanges a short-lived access token for a long-lived one
	try {
		$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
	} catch (Facebook\Exceptions\FacebookSDKException $e) {
		echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
		exit;
	}

	echo '<h3>Long-lived</h3>';
	var_dump($accessToken->getValue());
}

$_SESSION['fb_access_token'] = (string) $accessToken;

echo (string) $accessToken;
// User is logged in with a long-lived access token.
// You can redirect them to a members-only page.
//header('Location: https://example.com/members.php');