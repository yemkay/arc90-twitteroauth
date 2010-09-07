<?php
require_once("lib/Arc90/Service/Twitter.php");


$twitter = new Arc90_Service_Twitter();
//Pass consumer key, secret of Oauth app and user's access token, secret
$twitter->useOAuth('OAUTH_CONSUMER_KEY', 'OAUTH_CONSUMER_SECRET', 'USER_TOKEN', 'USER_SECRET');

//Get tweets from user's timeline
$response = $this->twitter->getFriendsTimeline('json', array('count' => 200, 'page' => $page));
echo ('HTTP code: '.$response->getHttpCode());

if (!$response->isError())
{
	$messages = $response->getJsonData();
	echo 'Found '.count($messages).' new tweets';	
	/* GO AHEAD AND PROCESS TWEETS */
}
else
{
	echo 'Error description: '.$response->getData();
}


?>