<?php

require_once dirname(__DIR__) . "/vendor/autoload.php";

date_default_timezone_set('Asia/Tokyo');

$response = \app\ChatWork::getRoomMessage('notice');

$ngWordList = [];

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

    // ""で囲まれたワードを、NGワードとして取り出す
    if (!preg_match('/"(.*?)"/', $message->body, $ngWord)) {
        continue;
    }

    $ngWordList[] = trim($ngWord[1]);
}

foreach ($ngWordList as $ngWord) {
    \app\NgWordUtil::insertNgWord($ngWord);
}

print_r(\app\NgWordUtil::getNgWordList());