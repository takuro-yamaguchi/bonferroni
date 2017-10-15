<?php

namespace app;

/**
 * Class SqliteUitl
 * @package app
 */
class NgWordUtil
{
    const DB_PATH = '../log/my_db.db';
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
            $dbh = self::getConnection(self::DB_PATH);

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
     * @param string $newNgWord
     * @return bool
     */
    public static function insertNgWord($newNgWord)
    {
        if (empty($newNgWord)) {
            return false;
        }

        $dbh = self::getConnection(self::DB_PATH);

        // 既存のNGワードとチェック
        if (in_array($newNgWord, self::getNgWordList())) {
            return false;
        }

        $sql = $dbh->prepare("insert into NgWordData (ngWord, createdTime) VALUES (?, ?)");

        return $sql->execute([
            $newNgWord,
            date('Y-m-d H:i:s'),
        ]);
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