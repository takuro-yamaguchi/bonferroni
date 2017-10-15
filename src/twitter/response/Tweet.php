<?php

namespace app\twitter\response;

/**
 * Class Tweet
 * @package app
 */
class Tweet
{
    /** @var  int ツイートID。新しいものが番号が古く、古いものが番号が若い。 */
    public $id;
    /** @var  string $idのstr版(intで扱えない場合はこちらを用いる) */
    public $idStr;
    /** @var  User ユーザー情報 */
    public $user;
    /** @var  string ツイート本文 */
    public $text;
    /** @var  Tweet リツイートか否か(True:リツイート/False:通常ツイート) */
    public $retweetedStatus;
    /** @var  int リツイートされた回数 */
    public $retweetCount;
    /** @var  int お気に入りされた数 */
    public $favoriteCount;
    /** @var  Entities  追加情報 */
    public $entities;
    /** @var  string ツイートを行ったアプリ・サイト等の情報 ex "<a href="https://www.showroom-live.com" rel="nofollow">SHOWROOM-LIVE</a>" */
    public $source;
    /** @var  string 言語情報 */
    public $lang;
    /** @var  string ツイート日時 */
    public $createdAt;
    /** @var  string ツイートがリプライだった時のツイート元のユーザー名 */
    public $inReplyToScreenName;
    /** @var  int ツイートがリプライだった時のツイート元のツイートID */
    public $inReplyToStatusId;
    /** @var  string 上記の文字列版 */
    public $inReplyToStatusIdStr;

    private $setFlag = false;

    const TWEET_URL_TEMPLATE      = "https://twitter.com/%s/status/%s";
    const CHAT_WORK_TEXT_TEMPLATE = "[info]!USER_NAME! :@!USER_SCREEN_NAME![hr]!TEXT!\n!ORIGIN_TWEET!!TWEET_TIME![hr]!TWEET_URL![/info]";

    // tweetのobjectを解析し、値にセット
    public function __construct($tweetObj)
    {
        // set
        $this->id                   = $tweetObj->id;
        $this->idStr                = $tweetObj->id_str;
        $this->user                 = new User($tweetObj->user);
        $this->text                 = $tweetObj->text;
        $this->retweetedStatus      = !empty($tweetObj->retweetedStatus) ? new Tweet($tweetObj->retweetedStatus) : null;
        $this->retweetCount         = $tweetObj->retweet_count;
        $this->favoriteCount        = $tweetObj->favorite_count;
        $this->entities             = !empty($tweetObj->entities) ? new Entities($tweetObj->entities) : null;
        $this->source               = $tweetObj->source;
        $this->lang                 = $tweetObj->lang;
        $this->createdAt            = $tweetObj->created_at;
        $this->inReplyToScreenName  = $tweetObj->in_reply_to_screen_name;
        $this->inReplyToStatusId    = $tweetObj->in_reply_to_status_id;
        $this->inReplyToStatusIdStr = $tweetObj->in_reply_to_status_id_str;

        // setFlag On
        $this->setFlag = true;
    }

    /**
     * RTのみのツイートか
     * @return bool
     */
    private function isOnlyRetweet ()
    {
        return strpos($this->text, "RT @") === 0;
    }

    /**
     * "Y-m-d H:i:s"のフォーマットでcreated_atを返す
     * @return string
     */
    private function getFormattedCreatedTime ()
    {
        return date("Y-m-d H:i:s", strtotime($this->createdAt));
    }

    /**
     * ChatWork用のテキストを取得
     * @return string
     */
    public function getTextForPost ()
    {
        $text = $this->text;

        // もしRTのみのツイートであれば、1行目のみ取り出す
        if ($this->isOnlyRetweet()) {
            $text = substr($text, 0, strpos($text, "\n")) . "...";
        }

        return str_replace(array("\r", "\n"), " ", $text); // 改行をスペースに変換
    }

    /**
     * ツイートのURLを取得
     * @return string
     */
    private function getTweetUrl()
    {
        return sprintf(self::TWEET_URL_TEMPLATE, $this->user->screenName, $this->idStr);
    }

    /**
     * リツイート元のツイートURLを取得
     * @return string
     */
    private function getOriginTweetUrl()
    {
        if (!empty($this->retweeted_status)) {
            return sprintf("Retweet元:%s\n", $this->retweetedStatus->getTweetUrl());
        }
        return '';
    }

    /**
     * ChatWork投稿用のテキストを生成
     * @return string
     */
    public function createChatWorkText()
    {
        if (!$this->setFlag) {
            return '';
        }

        // RTのみのツイートの場合、表示しない
        if ($this->isOnlyRetweet()) {
            return '';
        }

        $text = self::CHAT_WORK_TEXT_TEMPLATE;

        $text = str_replace("!USER_NAME!",        $this->user->name,                $text);
        $text = str_replace("!USER_SCREEN_NAME!", $this->user->screenName,          $text);
        $text = str_replace("!TEXT!",             $this->getTextForPost(),          $text);
        $text = str_replace("!ORIGIN_TWEET!",     $this->getOriginTweetUrl(),       $text);
        $text = str_replace("!TWEET_TIME!",       $this->getFormattedCreatedTime(), $text);
        $text = str_replace("!TWEET_URL!",        $this->getTweetUrl(),             $text);

        return $text . "\n";
    }
}

class User
{
    /** @var int ユーザーID */
    public $id;
    /** @var string ユーザー名 */
    public $name;
    /** @var string ユーザー名 */
    public $screenName;
    /** @var string ユーザーの説明情報 */
    public $description;
    /** @var int フォロー数 */
    public $friendsCount;
    /** @var int フォロワー数 */
    public $followersCount;
    /** @var int ツイート数(リツイート含む) */
    public $statusesCount;
    /** @var int お気に入り数 */
    public $favouritesCount;
    /** @var string 住んでいるところ */
    public $location;
    /** @var string このユーザの登録日 */
    public $createdAt;

    public function __construct($userObj)
    {
        $this->id              = $userObj->id;
        $this->name            = $userObj->name;
        $this->screenName      = $userObj->screen_name;
        $this->description     = $userObj->description;
        $this->friendsCount    = $userObj->friends_count;
        $this->followersCount  = $userObj->followers_count;
        $this->statusesCount   = $userObj->statuses_count;
        $this->favouritesCount = $userObj->favourites_count;
        $this->location        = $userObj->location;
        $this->createdAt       = $userObj->created_at;
    }
}

class Entities
{
    /** @var string */
    public $symbols;
    /** @var \stdClass[] 本文中に@で指定されたユーザー情報 */
    public $userMentions;
    /** @var string 本文に記載のあるハッシュタグ */
    public $hashTags;
    /** @var Url[] 本文に記載されたURL情報 */
    public $urls;

    public function __construct($entitiesObj)
    {
        $this->symbols      = $entitiesObj->symbols;
        $this->userMentions = $entitiesObj->user_mentions;

        if (!empty($entitiesObj->hashtags)) {
            $hashTags = [];
            foreach ($entitiesObj->hashtags as $hashTagObj) {
                $hashTags[] = new HashTag($hashTagObj);
            }
            $this->hashTags = $hashTags;
        }

        if (!empty($entitiesObj->urls)) {
            $urls = [];
            foreach ($entitiesObj->urls as $urlObj) {
                $urls[] = new Url($urlObj);
            }
            $this->urls = $urls;
        }
    }
}

class HashTag
{
    /** @var string ハッシュタグ名*/
    public $text;
    /** @var TODO */
    public $indices;

    public function __construct($obj)
    {
        $this->text         = $obj->text;
        $this->indices     = $obj->indices;
    }
}

class Url
{
    /** @var string 短縮URL*/
    public $url;
    /** @var string 完全なURL*/
    public $expandedUrl;
    /** @var string 表示用URL*/
    public $displayUrl;
    /** @var TODO */
    public $indices;

    public function __construct($obj)
    {
        $this->url         = $obj->url;
        $this->expandedUrl = $obj->expanded_url;
        $this->displayUrl  = $obj->display_url;
        $this->indices     = $obj->indices;
    }
}