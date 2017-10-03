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

$result = $twitterWrapper->search("バトフェス OR バトルフェスティバル exclude:retweets");
//echo \app\TwitterApiWrapper::createChatWorkText($result);

$a = \app\ChatWork::post('myChat', \app\TwitterApiWrapper::createChatWorkText($result));
var_dump($a);


