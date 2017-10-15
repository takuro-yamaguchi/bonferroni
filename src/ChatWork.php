<?php

namespace app;

// ChatWorkのAPIを叩くクラス
class ChatWork
{
    const LOG_FILE_PATH = __DIR__ . "/../log/chatWork.log";

    /**
     * $roomNameに設定されているチャットに$contentsを投稿する
     *
     * @param string $roomName
     * @param string $contents
     * @return string $message
     */
    public static function post($roomName, $contents)
    {
        if (empty($contents)) {
            $errorTxt = date("Y-m-d H:i:s") . " \$contents is empty!\n";
            file_put_contents(self::LOG_FILE_PATH, $errorTxt, FILE_APPEND);
            return $errorTxt;
        }

        // 送信パラメーター
        $params = array(
            'body' => $contents // メッセージ内容
        );

        // config読み込み
        $chatWorkConfig = Config::load('chatWork');

        $roomConfig = $chatWorkConfig[$roomName];

        // post用URL作成
        $postUrl = sprintf('%s/%s/%s', $chatWorkConfig['baseUrl'], $roomConfig['roomId'], $roomConfig['endPoint']);

        // cURLオプション設定
        $options = array(
            CURLOPT_URL            => $postUrl,
            CURLOPT_HTTPHEADER     => array('X-ChatWorkToken: ' . $chatWorkConfig['apiKey']), // APIキー
            CURLOPT_RETURNTRANSFER => true, // 文字列で返却
            CURLOPT_SSL_VERIFYPEER => false, // 証明書の検証をしない
            CURLOPT_POST           => true, // POST設定
            CURLOPT_POSTFIELDS     => http_build_query($params, '', '&'), // POST内容
            CURLOPT_HEADER         => true
        );

        // APIを叩く
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $res = curl_exec($ch);
        curl_close($ch);

        // エラー判定
        $res = json_decode($res, true);
        if (isset($res["errors"])) {
            $message = "Failure :" . array_shift($res["errors"]);
        } else {
            $message = "Success";
        }

        // LOG
        file_put_contents(self::LOG_FILE_PATH, date("Y-m-d H:i:s") . "  " . $message . "\n", FILE_APPEND);

        // APIの結果を返す
        return $message . "\n";
    }

    /**
     * $roomNameに設定されているチャットのメッセージを取得
     *
     * @param string $roomName
     * @return RoomMessagesResponse
     */
    public static function getRoomMessage($roomName)
    {
        // config読み込み
        $chatWorkConfig = Config::load('chatWork');

        $roomConfig = $chatWorkConfig[$roomName];

        // get用URL作成
        $getUrl = sprintf('%s/%s/%s?force=1', $chatWorkConfig['baseUrl'], $roomConfig['roomId'], $roomConfig['endPoint']);

        // cURLオプション設定
        $options = array(
            CURLOPT_URL            => $getUrl,
            CURLOPT_HTTPHEADER     => array('X-ChatWorkToken: ' . $chatWorkConfig['apiKey']), // APIキー
            CURLOPT_RETURNTRANSFER => true, // 文字列で返却
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_SSL_VERIFYPEER => false, // 証明書の検証をしない
            CURLOPT_ENCODING       => "gzip" //gzipで圧縮されるのを防ぐ
        );

        // APIを叩く
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $res = curl_exec($ch);
        curl_close($ch);

        if (empty($res)) {
            return null;
        }

        return new RoomMessagesResponse(json_decode($res));
    }
}

class RoomMessagesResponse
{
    /** @var Message[]  */
    private $messageList;

    public function __construct($responseObj)
    {
        $this->messageList = [];
        foreach ($responseObj as $obj) {
            $this->messageList[] = new Message($obj);
        }
    }

    public function getMessageList()
    {
        return $this->messageList;
    }
}

class Message
{
    public $messageId;
    public $account;
    public $body;
    public $sendTime;
    public $updatedTime;

    public function __construct($obj)
    {
        $this->messageId   = $obj->message_id;
        $this->account     = new Account($obj->account);
        $this->body        = $obj->body;
        $this->sendTime    = $obj->send_time;
        $this->updatedTime = $obj->update_time;
    }

    public function isSendMessageByAccountId($accountId)
    {
        return $accountId == $this->account->accountId;
    }
}

class Account
{
    public $accountId;
    public $name;
    public $avatarImageUrl;

    public function __construct($accountObj)
    {
        $this->accountId      = $accountObj->account_id;
        $this->name           = $accountObj->name;
        $this->avatarImageUrl = $accountObj->avatar_image_url;
    }
}
