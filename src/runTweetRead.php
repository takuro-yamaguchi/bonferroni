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

// maxId取得
$maxIdTxtPath = "../log/maxId.txt";
$maxId = file_exists($maxIdTxtPath) ? file_get_contents($maxIdTxtPath) : 0;

// ツイート検索
$twitterListResponse = $twitterApi->searchTweets(
    "battlefes OR バトフェス OR バトルフェスティバル exclude:retweets",
    $maxId
);

// maxIdをローカルに保存
file_put_contents("$maxIdTxtPath", $twitterListResponse->getSearchMetadata()->getMaxId());

// ChatWork用のテキスト作成
$text = '';
foreach ($twitterListResponse->getTweetList() as $tweet) {
    if (\app\NgWordUtil::isIncludeNgWord($tweet->getTextForPost())){
        continue;
    }
    echo $tweet->getTextForPost() . PHP_EOL;
    $text .= $tweet->createChatWorkText();
}

// ChatWork投稿
$postResult = \app\ChatWork::post('test', $text);
echo $postResult . PHP_EOL;
