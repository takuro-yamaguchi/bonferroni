<?php

namespace app;

/**
 * Class TwitterApiWrapper
 * @package app
 */
class TwitterApiWrapper
{

    private $_connection = null;

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
        if (!file_exists('store.txt')) {
            $apiResult = $this->_connection->get("search/tweets", array("q" => $query, 'count' => 100));
            file_put_contents('store.txt', serialize($apiResult));
        }
        $apiResult = unserialize(file_get_contents('store.txt'));

        return self::filterObject($apiResult);
    }

    /**
     * tweetObjectから、必要な情報だけ取り出し、配列に変換
     *
     * @param $resultOfObject
     * @return Tweet[]
     */
    private static function filterObject($resultOfObject)
    {
        if (empty($resultOfObject)) return array();

        // ツイート情報のみ取り出し
        $objects = $resultOfObject->statuses;

        $result = [];
        foreach ($objects as $obj) {
            // $objからTweetオブジェクトを生成
            $result[] = new Tweet($obj);
        }

        return $result;
    }

    /**
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