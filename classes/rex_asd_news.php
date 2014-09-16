<?php

class rex_asd_news
{
    public static $SEO_ADDON = null;
    public static $SEO_URL_CONTROL = false;

    public $sql;

    /*
     * initial the class
     * @param mixed $arg1 a sql Object, or int
     * @param int $arg2
     */
    public function __construct($arg1, $arg2 = null)
    {
        // SQL Object
        if (is_object($arg1) && is_null($arg2)) {

            $this->setSql($arg2);

        } else {

            $this->setVars($arg1, $arg2);

        }

    }

    /*
     * set the sql object
     * @param rex_sql $sql SQL-object
     * @return this
     */
    public function setSql(rex_sql $sql)
    {
        $this->sql = $sql;

        return $this;
    }

    /*
     * set id and clang for a sql-query
     * @param int $id
     * @param int $clang
     * @return this
     */
    public function setVars($id, $clang = null)
    {
        global $REX;

        if ($clang == null) {
            $clang = $REX['CUR_CLANG'];
        }

        $this->setSql(new rex_sql);
        $this->sql->setQuery('SELECT * FROM `' . $REX['TABLE_PREFIX'] . 'asd_news` WHERE `id` = ' . (int)$id . ' AND `clang` = ' . (int)$clang);

        return $this;
    }

    /*
     * @see rex_sql::getValue
     * @return mixed
     */
    public function getValue($name, $default = null)
    {
        return $this->sql->getValue($name, $default);
    }

    /*
     * get the Image Path
     * @param string|null $imageType imagemanager-type
     * @return string
     */
    public function getImage($imageType = null)
    {

        $default = '/files/' . $this->getValue('pictures');
        $defaultType = 'index.php?rex_img_type=' . $imageType . '&amp;rex_img_file=' . $this->getValue('pictures');

        if (self::$SEO_ADDON == 'seo42') {

            if ($imageType != null) {
                return seo42::getImageManagerFile($this->getValue('pictures'), $imageType);
            }

            return seo42::getMediaFile($this->getValue('pictures'));

        }

        /*
        if (self::$SEO_ADDON == 'yrewrite') {

            if ($imageType != null) {
                return $defaultType;
            }

            return $default;

        }

        if (self::$SEO_ADDON == 'rexseo') {

            if ($imageType != null) {
                return $defaultType;
            }

            return $default;

        }
        */

        if ($imageType != null) {
            return $defaultType;
        }

        return $default;
    }

    /*
     * return the news Url
     * @param array $params add Params for the Url
     * @return string
     */
    public function getUrl(array $params = array())
    {
        global $REX;

        $params = array_merge(array('news-id' => $this->getValue('id')), $params);

        if(self::$SEO_URL_CONTROL) {
            return url_generate::getUrlById($REX['TABLE_PREFIX'] . 'asd_news', $this->getValue('id'));
        }

        return rex_getUrl($REX['ARTICLE_ID'], $REX['CUR_CLANG'], $params);
    }

    /*
     * set the SEO Params (keywords, description, ...)
     * @return this
     */
    public function setSEOParams()
    {
        return $this;
    }

    /*
     * get the news ID for the Frontend
     * @return int|null
     */
    public static function getNewsId()
    {
        global $REX;

        $id = null;

        if (self::$SEO_URL_CONTROL) {
            $id = url_generate::getId($REX['TABLE_PREFIX'] . 'asd_news');
        }

        if ($id == null) {
            $id = (int)rex_request('news-id');
        }

        return $id;

    }

    public static function getNewsByCategory($cat, $clang = null)
    {
        return self::getByWhere('`category` = ' . (int)$cat, $clang);
    }

    /*
     * return a array of news filtered by Ids
     *
     */
    public static function getNewsByIds($newsIds, $clang = null)
    {
        return self::getByWhere('`id` IN (' . implode(',', (array)$newsIds) . ')', $clang);
    }

    /*
     * return a array of news filtered by a Where
     * @param string $where
     * @param int $clang
     * @return array
     */
    private static function getByWhere($where, $clang = null)
    {
        global $REX;

        if ($clang == null) {
            $clang = $REX['CUR_CLANG'];
        }

        $news = array();

        $sql = new rex_sql();
        $sql->setQuery('SELECT * FROM `' . $REX['TABLE_PREFIX'] . 'asd_news` WHERE ' . $where . '  AND `clang` = ' . (int)$clang);
        for ($i = 1; $i <= $sql->getRows(); $i++) {

            $news[] = new self(clone $sql);

            $sql->next();
        }

        return $news;
    }

    /*
     * return the newest News-Id
     * @return int
     */
    public static function getLastNewsId()
    {
        global $REX;

        $sql = new rex_sql();
        $sql->setQuery('SELECT `id` FROM `' . $REX['TABLE_PREFIX'] . 'asd_news` ORDER BY id DESC LIMIT 0, 1');

        if ($sql->getRows()) {
            return (int)$sql->getValue('id');
        }

        return 0;
    }

}

?>