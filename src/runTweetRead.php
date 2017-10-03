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

$result = $twitterWrapper->search("battlefes OR バトフェス OR バトルフェスティバル exclude:retweets");

$a = \app\ChatWork::post('myChat', \app\TwitterApiWrapper::createChatWorkText($result));
print_r($a);


