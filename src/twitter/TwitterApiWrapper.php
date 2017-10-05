<?php

namespace app\twitter;

use app\twitter\response\TwitterListResponse;

/**
 * Class TwitterApiWrapper
 * @package app
 */
class TwitterApiWrapper
{
    /** @var \Abraham\TwitterOAuth\TwitterOAuth|null  */
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
     * @param $sinceId
     * @return TwitterListResponse
     */
    public function searchTweets($query, $sinceId = 0)
    {
        $params = array(
            "q"        => $query,
            'count'    => self::DEFAULT_GET_TWEET_COUNT,
            'since_id' => $sinceId,
        );

        $apiResult = $this->exec("search/tweets", $params, true);

        return $apiResult;
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
