<?php

require_once dirname(__DIR__) . "/vendor/autoload.php";

date_default_timezone_set('Asia/Tokyo');

$response = \app\ChatWork::getRoomMessage('notice');

/** @var  \app\Message[] $addNgWordMessages */
$addNgWordMessages = [];

foreach ($response->getMessageList() as $message) {
    // bot向けメッセージでない場合はスルー
    if (strpos($message->body, '[To:1719891]') === false) {
        continue;
    }

    $str = explode('exclude_message', $message->body);
    // NGワード登録用メッセージでない場合はスルー
    if (count($str) < 2) {
        continue;
    }

    $addNgWordMessages[] = $message;
}

// NGワード登録
$postText = '';
foreach ($addNgWordMessages as $message) {
    // ""で囲まれたワードを、NGワードとして取り出す
    if (!preg_match('/"(.*?)"/', $message->body, $ngWord)) {
        continue;
    }

    $ngWord = trim($ngWord[1]);

    if (\app\NgWordUtil::insertNgWord($ngWord)) {
        $postText .= sprintf("[To:%s]\n\"%s\"をNGワードに登録しました!\n", $message->account->accountId, $ngWord);
    }
}

if (!empty($postText)) {
    $postResult = \app\ChatWork::post('notice', $postText);
    echo $postResult . PHP_EOL;
}