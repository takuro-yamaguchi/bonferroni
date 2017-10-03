<?php

namespace app;

/**
 * Class Tweet
 * @package app
 */
class Tweet
{
    /** @var  User */
    public $user;
    public $text;
    public $externalHost;
    public $tweetUrl;
    public $createdTime;
    public $originTweetUrl;
    public $onlyRetweet;

    private $setFlag = false;

    const TWEET_URL_TEMPLATE      = "https://twitter.com/%s/status/%s";
    const CHAT_WORK_TEXT_TEMPLATE = "[info]!USER_NAME! :@!USER_SCREEN_NAME![hr]!TEXT!\n!ORIGIN_TWEET!!TWEET_TIME![hr]!TWEET_URL![/info]";

    // tweetのobjectを解析し、値にセット
    public function __construct($tweetObj)
    {
        // set
        $this->setUser($tweetObj);
        $this->setCreatedTime($tweetObj);
        $this->setUrl($tweetObj);
        $this->setTweetUrl($tweetObj);
        $this->setText($tweetObj);
        $this->setOriginTweetUrl($tweetObj);

        if ($this->user->screenName == "seina_fuku48") {
            var_dump($tweetObj);
        }
        // setFlag On
        $this->setFlag = true;
    }

    private function setUser($obj)
    {
        $this->user = new User($obj->user);
    }

    private function setCreatedTime($obj)
    {
        // datetime変換
        $this->createdTime = date("Y-m-d H:i:s", strtotime($obj->created_at));
    }

    private function setUrl($obj)
    {
        if (empty($obj->entities) || empty($obj->entities->urls)) {
            return;
        }

        // 外部リンクがある場合、ホスト名のみセット
        $url = $obj->entities->urls[0]->expanded_url;
        $this->externalHost = parse_url($url)['host'];
    }

    private function setTweetUrl($obj)
    {
        $this->tweetUrl = sprintf(self::TWEET_URL_TEMPLATE, $obj->user->screen_name, $obj->id_str);
    }

    private function setText($obj)
    {
        $text = $obj->text;

        // もしRTのみのツイートであれば、1行目のみ取り出す
        if (strpos($text, "RT @") === 0) {
            $text = substr($text, 0, strpos($text, "\n")) . "...";
            $this->onlyRetweet = true;
        }

        $this->text = str_replace(array("\r", "\n"), " ", $text); // 改行をスペースに変換
    }

    private function setOriginTweetUrl($obj)
    {
        // リツイートの場合、リツイート元のツイートURLを取得
        $this->originTweetUrl = '';
        if (!empty($obj->retweeted_status)) {
            $originTweet = new Tweet($obj->retweeted_status);
            $this->originTweetUrl = sprintf("Retweet元:%s\n", $originTweet->tweetUrl);
        }
    }

    // ChatWork投稿用のテキストを生成
    public function createChatWorkText()
    {
        if (!$this->setFlag) {
            return '';
        }

        // RTのみのツイートの場合
        if ($this->onlyRetweet) {
            return '';
        }
        $text = self::CHAT_WORK_TEXT_TEMPLATE;

        $text = str_replace("!USER_NAME!",        $this->user->name,       $text);
        $text = str_replace("!USER_SCREEN_NAME!", $this->user->screenName, $text);
        $text = str_replace("!TEXT!",             $this->text,             $text);
        $text = str_replace("!ORIGIN_TWEET!",     $this->originTweetUrl,   $text);
        $text = str_replace("!TWEET_TIME!",       $this->createdTime,      $text);
        $text = str_replace("!TWEET_URL!",        $this->tweetUrl,         $text);

        return $text . "\n";
    }
}

class User
{
    public $name;
    public $screenName;

    public function __construct($userObj)
    {
        $this->name = $userObj->name;
        $this->screenName = $userObj->screen_name;
    }
}