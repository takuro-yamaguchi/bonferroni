<?php

require_once dirname(__DIR__) . "/vendor/autoload.php";

date_default_timezone_set('Asia/Tokyo');

$config = app\Config::load('twitter');

$twitterWrapper = new \app\twitter\TwitterApiWrapper(
    $config['consumerKey'],
    $config['consumerSecret'],
    $config['accessToken'],
    $config['accessTokenSecret']
);

$result = $twitterWrapper->search(
    "battlefes OR バトフェス OR バトルフェスティバル exclude:retweets",
    date("Y-m-d H:i:s", strtotime("- 5 minitue"))
);

$a = \app\ChatWork::post('test', \app\twitter\TwitterApiWrapper::createChatWorkText($result));
if(isset($a)) {
    print_r($a);
}


