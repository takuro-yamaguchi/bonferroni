<?php

namespace app;

/**
 * Class TwitterApiWrapper
 * @package app
 */
class TwitterApiWrapper
{
    private $_connection = null;

    const DEFAULT_GET_TWEET_COUNT = 100;
    const SET_DATE_TIME_TEMPLATE = "Y-m-d_H:i:s_JST";

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
     * @return Tweet[]
     */
    public function search($query)
    {
        $params = array(
            "q" => $query,
            'count' => self::DEFAULT_GET_TWEET_COUNT,
            'since' => date(self::SET_DATE_TIME_TEMPLATE, strtotime("- 5 minute")),
        );

        $apiResult = $this->exec("search/tweets", $params);

        return self::filterObject($apiResult);
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

        if (!$isRecursionGet || $apiResult->isNextResults()) {
            return $apiResult;
        }

        $result = $this->exec($endPoint, $apiResult->getNextApiParams(), $isRecursionGet);

        // 取得結果をマージ
        $apiResult = TwitterListResponse::mergeResult($apiResult, $result);

        return $apiResult;

        //////////// テスト用コード
        if (!file_exists('store.txt')) {

            file_put_contents('store.txt', serialize($apiResult));
        }
        $apiResult = unserialize(file_get_contents('store.txt'));

        return $apiResult;
        ///////////////////////////
    }

    /**
     * tweetObjectから、必要な情報だけ取り出し、Tweetインスタンス配列に変換
     *
     * @param TwitterListResponse $resultOfObject
     * @return Tweet[]
     */
    private static function filterObject($resultOfObject)
    {
        if (empty($resultOfObject)) return array();

        // ツイート情報のみ取り出し
        $objects = $resultOfObject->getStatuses();

        $result = [];
        foreach ($objects as $obj) {
            // $objからTweetオブジェクトを生成
            $result[] = new Tweet($obj);
        }

        // ツイート日時でソート
        usort($result, function ($a, $b) {
            return $a->createdTime > $b->createdTime;
        });

        return $result;
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
}
