<?php
namespace app;

// ChatWorkのAPIを叩くクラス
class ChatWork
{
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
            return '$contents is empty!';
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
            CURLOPT_URL        => $postUrl,
            CURLOPT_HTTPHEADER => array('X-ChatWorkToken: ' . $chatWorkConfig['apiKey']), // APIキー
            CURLOPT_RETURNTRANSFER => true, // 文字列で返却
            CURLOPT_SSL_VERIFYPEER => false, // 証明書の検証をしない
            CURLOPT_POST       => true, // POST設定
            CURLOPT_POSTFIELDS => http_build_query($params, '', '&'), // POST内容
            CURLOPT_HEADER => true
        );

        // APIを叩く
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $res = curl_exec($ch);
        var_dump($res);
        curl_close($ch);

        // エラー判定
        $res = json_decode($res, true);
        if (isset($res["errors"])) {
            $message = "Failure :" . array_shift($res["errors"]);
        } else {
            $message = "Success";
        }

        // APIの結果を返す
        return $message . "\n";
    }
}
