<?php

require_once dirname(__DIR__) . "/vendor/autoload.php";

date_default_timezone_set('Asia/Tokyo');

$config = app\Config::load('twitter');

$twitterApi = new \app\twitter\TwitterApiWrapper(
    $config['consumerKey'],
    $config['consumerSecret'],
    $config['accessToken'],
    $config['accessTokenSecret']
);

$twitterListResponse = $twitterApi->searchTweets(
    "battlefes OR バトフェス OR バトルフェスティバル exclude:retweets",
    date("Y-m-d H:i:s", strtotime("- 5 minitue"))
);

$postResult = \app\ChatWork::post('test', $twitterListResponse->createChatWorkText());
echo $postResult . PHP_EOL;
