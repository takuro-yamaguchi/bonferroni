<?php

require_once dirname(__DIR__) . "/vendor/autoload.php";
$config = app\Config::load('twitter');

$twitterWrapper = new \app\TwitterApiWrapper(
    $config['consumerKey'],
    $config['consumerSecret'],
    $config['accessToken'],
    $config['accessTokenSecret']
);

$result = $twitterWrapper->search("バトフェス OR バトルフェスティバル exclude:retweets");
var_dump(\app\TwitterApiWrapper::createChatWorkText($result));

//$a = \app\ChatWork::post('myChat', \app\TwitterApiWrapper::createChatWorkText($result));
//var_dump($a);


