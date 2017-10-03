<?php

require_once dirname(__DIR__) . "/vendor/autoload.php";

date_default_timezone_set('Asia/Tokyo');

$config = app\Config::load('twitter');

$twitterWrapper = new \app\TwitterApiWrapper(
    $config['consumerKey'],
    $config['consumerSecret'],
    $config['accessToken'],
    $config['accessTokenSecret']
);

$result = $twitterWrapper->search(
    "battlefes OR バトフェス OR バトルフェスティバル exclude:retweets",
    date("Y-m-d H:i:s", strtotime("- 5 minite"))
);

$a = \app\ChatWork::post('test', \app\TwitterApiWrapper::createChatWorkText($result));
print_r($a);


