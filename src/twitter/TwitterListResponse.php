<?php

namespace app\twitter;

/**
 * Class TwitterListResponse
 * @package app
 */
class TwitterListResponse
{
    /** @var array */
    private $statuses;
    /** @var SearchMetadata  */
    private $searchMetadata;

    public function __construct($responseObj)
    {
        $this->statuses       = $responseObj->statuses ?? null;
        $this->searchMetadata = new SearchMetadata($responseObj->search_metadata);
    }

    public function getStatuses()
    {
        return $this->statuses;
    }

    public function getSearchMetadata()
    {
        return $this->searchMetadata;
    }

    /**
     * next_resultsから、API用パラメータを生成
     * @return array
     */
    public function getNextApiParams()
    {
        // queryを解析し、パラメータを作成
        parse_str(
            preg_replace('/^\?/', '', $this->searchMetadata->getNextResults()),
            $nextParam
        );

        return $nextParam;
    }

    /**
     * @return bool
     */
    public function isNextResults()
    {
        return !empty($this->searchMetadata->getNextResults());
    }

    /**
     * $a,$bのstatusesをマージ
     *
     * @param  TwitterListResponse $a
     * @param  TwitterListResponse $b
     * @return TwitterListResponse
     */
    public static function mergeResult($a, $b)
    {
        $a->statuses = array_merge($a->statuses, $b->statuses);

        return $a;
    }
}

class SearchMetadata
{
    /** @var float 何秒で取得完了したか */
    private $completedIn;
    /** @var int ツイート取得数 */
    private $count;
    /** @var int  取得したツイートのなかで一番新しいID */
    private $maxId;
    /** @var string  $maxIdのstr版(intで扱えない場合はこちらを用いる) */
    private $maxIdStr;
    /** @var int 取得したツイートのなかで一番古いID */
    private $sinceId;
    /** @var string   $sinceIdのstr版(intで扱えない場合はこちらを用いる)*/
    private $sinceIdStr;
    /** @var string  検索ワード */
    private $query;
    /** @var string  同じ検索ワードでこれよりも古いツイートを取得したいときのURL */
    private $nextResults;
    /** @var string  同じ検索ワードでこれよりも新しいツイートを取得したいときのURL */
    private $refreshUrl;

    public function __construct($searchMetadataObj)
    {
        if (empty($searchMetadataObj)) return;

        $this->completedIn = $searchMetadataObj->completed_n  ?? null;
        $this->count       = $searchMetadataObj->count        ?? null;
        $this->maxId       = $searchMetadataObj->max_id       ?? null;
        $this->maxIdStr    = $searchMetadataObj->max_id_str   ?? null;
        $this->sinceId     = $searchMetadataObj->since_id     ?? null;
        $this->sinceIdStr  = $searchMetadataObj->since_id_str ?? null;
        $this->query       = $searchMetadataObj->query        ?? null;
        $this->nextResults = $searchMetadataObj->next_results ?? null;
        $this->refreshUrl  = $searchMetadataObj->refresh_url  ?? null;
    }

    public function getNextResults()
    {
        return $this->nextResults;
    }
};