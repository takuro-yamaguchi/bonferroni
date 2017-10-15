<?php

namespace app;

/**
 * Class SqliteUitl
 * @package app
 */
class NgWordUtil
{
    private static $ngWordList = null;

    /**
     *
     * @return \PDO
     */
    public static function getConnection($sqliteDbPath)
    {
        // 接続
        try {
            $pdo = new \PDO('sqlite:' . $sqliteDbPath);
            // SQL実行時にもエラーの代わりに例外を投げるように設定
            // (毎回if文を書く必要がなくなる)
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            // デフォルトのフェッチモードを連想配列形式に設定
            // (毎回PDO::FETCH_ASSOCを指定する必要が無くなる)
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo $e->getMessage() . PHP_EOL;
            exit();
        }

        return self::init($pdo);
    }

    /**
     * 初期化処理
     * $param PDO $dbh
     * @return \PDO
     */
    private static function init($dbh)
    {
        $dbh->exec("CREATE TABLE IF NOT EXISTS NgWordData(
        ngWordDataId  integer primary key autoincrement,
        ngWord VARCHAR(255),
        createdTime timestamp 
    )");

        return $dbh;
    }

    /**
     * NGワードリストを取得
     * @return string[]
     */
    public static function getNgWordList()
    {
        if (empty(self::$ngWordList)) {
            $dbh = self::getConnection('../log/akb_twitter.db');

            $res = $dbh->query('SELECT * FROM NgWordData')->fetchAll();
            if (empty($res)) {
                self::$ngWordList = array();
            } else {
                self::$ngWordList = array_column($res, 'ngWord');
            }
        }

        return self::$ngWordList;
    }

    /**
     * NGワード追加
     * @param string[] $ngWordList
     * @return bool
     */
    public static function insertNgWord($ngWordList)
    {
        var_dump($ngWordList);
        if (!is_array($ngWordList)) {
            return false;
        }

        $dbh = self::getConnection('../log/akb_twitter.db');

        $sql = $dbh->prepare("insert into NgWordData (ngWord, createdTime) VALUES (?, ?)");

        foreach ($ngWordList as $word) {
            $sql->execute([
                $word,
                date('Y-m-d H:i:s'),
            ]);
        }

        return true;
    }

    /**
     * NGワードチェック
     * @param string $text
     * @return bool
     */
    public static function isIncludeNgWord($text)
    {
        $ngWordList = self::getNgWordList();
        foreach ($ngWordList as $ngWord) {
            if (strpos($text, $ngWord) !== false) {
                return true;
            }
        }

        return false;
    }
}