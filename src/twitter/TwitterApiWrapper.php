<?php

namespace app\twitter;

/**
 * Class TwitterApiWrapper
 * @package app
 */
class TwitterApiWrapper
{
    private $_connection = null;

    const DEFAULT_GET_TWEET_COUNT = 100;
    const SET_DATE_TIME_TEMPLATE = "Y-m-d_H:i:s";

    public function __construct($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret)
    {
        $this->_connection = new \Abraham\TwitterOAuth\TwitterOAuth(
            $consumerKey,
            $consumerSecret,
            $accessToken,
            $accessTokenSecret
        );
    }

    /**
     * $queryの条件で、ツイートを取得
     *
     * @param $query
     * @param $sinceDateTime
     * @return Tweet[]
     */
    public function search($query, $sinceDateTime = null)
    {
        $params = array(
            "q"     => $query,
            'count' => self::DEFAULT_GET_TWEET_COUNT,
            'since' => self::convertApiDateTime($sinceDateTime),
        );

        $apiResult = $this->exec("search/tweets", $params, true);

        return $apiResult->getTweetList();
    }

    /**
     * $queryの条件で、ツイートを取得
     *
     * @param $query
     * @return TwitterListResponse $apiResult
     */
    private function exec($endPoint, $params, $isRecursionGet = false)
    {
        $apiResult = new TwitterListResponse($this->_connection->get($endPoint, $params));

        if (!$isRecursionGet || !$apiResult->isNextResults()) {
            return $apiResult;
        }

        $result = $this->exec($endPoint, $apiResult->getNextApiParams(), $isRecursionGet);

        // 取得結果をマージ
        $apiResult = TwitterListResponse::mergeResult($apiResult, $result);

        return $apiResult;
    }

    /**
     * ChatWork投稿用テキスト生成
     *
     * @param Tweet[] $tweetList
     * @return string $text
     */
    public static function createChatWorkText($tweetList)
    {
        $text = '';
        foreach ($tweetList as $tweet) {
            $text .= $tweet->createChatWorkText();
        }

        return $text;
    }

    /**
     * API用のdatetimeに変換する
     *
     * @param $datetime Y-m-d H:i:s
     * @return string Y-m-d_H:i:s_JST
     */
    public static function convertApiDateTime($datetime = null)
    {
        // datetimeがnullの場合、制限である7日前に設定
        if (!$datetime) {
            $timestamp = strtotime("- 7 day");
        } else {
            $timestamp = strtotime($datetime);
        }

        // timezoneを追加
        return date(self::SET_DATE_TIME_TEMPLATE, $timestamp) . "_JST";
    }
}
