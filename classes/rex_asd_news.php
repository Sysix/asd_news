<?php

class rex_asd_news
{
    public static $SEO_ADDON = null;
    public static $SEO_URL_CONTROL = false;

    /**
     * the old Columns, needed vor getValue()
     */
    const oldSQLColumns = '|category|picture|text|';

    /** @var  rex_sql $sql */
    public $sql;

    public static $month_de = array(
        1 => 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni',
        'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'
    );

    public static $month_en = array(
        1 => 'Januar', 'Februar', 'March', 'April', 'Mai', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    );

    /**
     * initial the class
     * @param mixed $arg1 a sql Object, or int
     * @param int $arg2
     */
    public function __construct($arg1, $arg2 = null)
    {
        // SQL Object
        if (is_object($arg1) && is_null($arg2)) {

            $this->setSql($arg1);

        } else {

            $this->setVars($arg1, $arg2);

        }

    }

    /**
     * set the sql object
     * @param rex_sql $sql SQL-object
     * @return self
     */
    public function setSql(rex_sql $sql)
    {
        $this->sql = $sql;

        return $this;
    }

    /**
     * set id and clang for a sql-query
     * @param int $id
     * @param int $clang
     * @return self
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

    /**
     * @see rex_sql::getValue
     * @return mixed
     */
    public function getValue($name, $default = null)
    {
        if(strpos(self::oldSQLColumns, '|' . $name . '|')) {
            $name = 'asd_' . $name;
        }
        return $this->sql->getValue($name, $default);
    }

    /**
     * @return DateTime
     */
    public function getPublishDate()
    {
        return new DateTime($this->getValue('publishedAt'));
    }

    /**
     * get the Month as word
     *
     * @param string $lang
     * @return string
     */
    public function getMonthName($lang = 'de')
    {
        $date = $this->getPublishDate();
        $varName = 'month_' . $lang;

        $name = self::$$varName;

        return $name[$date->format('n')];
    }

    /**
     * get the Image Path
     * @param string|null $imageType imagemanager-type
     * @return string
     */
    public function getImage($imageType = null)
    {
        global $REX;

        $pictureCol = $REX['ADDON']['asd_news']['config']['sql']['picture'];
        $default = '/files/' . $this->getValue($pictureCol);
        $defaultType = 'index.php?rex_img_type=' . $imageType . '&amp;rex_img_file=' . $this->getValue($pictureCol);

        if(rex_extension_is_registered('ASD_NEWS_GETIMAGE')) {
            return rex_register_extension_point('ASD_NEWS_GETIMAGE', $pictureCol);
        }

        if (self::$SEO_ADDON == 'seo42') {

            if ($imageType != null) {
                return seo42::getImageManagerFile($this->getValue($pictureCol), $imageType);
            }

            return seo42::getMediaFile($this->getValue($pictureCol));

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

    /**
     * return the news Url
     * @param array $params add Params for the Url
     * @return string
     */
    public function getUrl(array $params = array())
    {
        global $REX;

        $params = array_merge(array(
            'news-id' => $this->getValue('id'),
            'clang' => $REX['CUR_CLANG'],
            'article-id' => $REX['ARTICLE_ID']
        ), $params);

        $params = rex_register_extension_point('ASD_NEWS_GENERATE_URL', $params);

        if (self::$SEO_URL_CONTROL) {
            return url_generate::getUrlById($REX['TABLE_PREFIX'] . 'asd_news', $this->getValue('id'));
        }

        $art_id = $params['article-id'];
        $clang = $params['clang'];

        unset($params['article-id'], $params['clang']);

        return rex_getUrl($art_id, $clang, $params);
    }

    /**
     * return string
     */
    public function getRubricName() {
        global $REX;
        static $rubrics = array();

        if(empty($rubrics)) {

            $sql = new rex_sql();
            $sql->setQuery('SELECT * FROM `' . $REX['TABLE_PREFIX'] . 'asd_news_category`');
            for($i = 1; $i <= $sql->getRows(); $i++) {
                $rubrics[$sql->getValue('id')] = $sql->getValue('name');

                $sql->next();
            }

        }

        return $rubrics[$this->getValue($REX['ADDON']['asd_news']['config']['sql']['category'])];

    }

    /**
     * is the news online?
     *
     * @return bool
     */
    public function isOnline()
    {
        return ($this->getValue('online') == 1);
    }

    /**
     * set the SEO Params (keywords, description, ...)
     * at the moment use self::replaceSeoTags()
     *
     * @return self
     */
    public function setSEOParams()
    {
        return $this;
    }

    /**
     * replace the meta keywords, description html tags
     *
     * replaceSeoTags(array(
     *   'keywords' => $news->getValue('keywords')
     *   'description' => $news->getValue('description')
     *   'og:description' => $news->getValue('facebook_description')
     * ));
     *
     * @param array $tagNames
     * @return string|bool
     */
    public function replaceSeoTags(array $tagNames)
    {
        $self = $this;

        $tagNames = rex_register_extension_point('ASD_NEWS_SEOTAGS', $tagNames);

        rex_register_extension('OUTPUT_FILTER', function ($subject) use ($tagNames, $self) {

            try {

                $document = new DOMDocument();
                $document->formatOutput = true;
                $document->encoding = 'utf-8';

                @$document->loadHTML('<?xml encoding="utf-8" ?>' . $subject['subject']);
                $metaList = $document->getElementsByTagName('meta');

                foreach ($metaList as $meta) {
                    /** @var DOMElement $meta */

                    if (!isset($tagNames[$meta->getAttribute('name')])) {

                        if (!isset($tagNames[$meta->getAttribute('property')])) {

                            continue;

                        } else {

                            $tagName = $tagNames[$meta->getAttribute('property')];

                        }

                    } else {
                        $tagName = $tagNames[$meta->getAttribute('name')];
                    }

                    $meta->setAttribute('content', $tagName);

                    unset($tagNames[$meta->getAttribute('name')]);
                }

                if (count($tagNames)) {

                    $metaLast = $metaList->item($metaList->length - 1);

                    foreach ($tagNames as $name => $content) {

                        $attribute = (substr($name, 0, 3) == 'og:') ? 'property' : 'name';

                        $element = $document->createElement('meta');
                        $element->setAttribute($attribute, $name);
                        $element->setAttribute('content', $content);

                        $metaLast->parentNode->insertBefore($element, $metaLast->nextSibling);

                    }

                }

                // get the doctype
                preg_match('/<!doctype([^>]*)>/i', $subject['subject'], $doctype);

                return $doctype[0]. $document->saveHTML($document->documentElement);

            } catch (DOMException $e) {
                echo rex_warning($e->getMessage());
            }

        });

        return false;
    }

    /**
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

    /**
     * return an array of news filtered by the category
     *
     * @param int $cat
     * @param int|null $clang
     * @return array
     */
    public static function getNewsByCategory($cat, $clang = null)
    {
        return self::getByWhere(array(
            self::$categoryColumn => '= ' . (int)$cat
        ), $clang);
    }

    /**
     * return an array of news filtered by Ids
     *
     * @param array $newsIds
     * @param int|null $clang
     * @return array
     */
    public static function getNewsByIds($newsIds, $clang = null)
    {
        return self::getByWhere(array(
            'id' => 'IN (' . implode(',', (array)$newsIds) . ')'
        ), $clang);
    }

    /**
     * return an news filtered by id
     *
     * @param int $newsId
     * @param int|null $clang
     * @return self
     */
    public static function getNewsById($newsId, $clang = null)
    {
        $news = self::getByWhere(array(
            'id' => '= ' . (int)$newsId
        ), $clang);

        return $news[0];
    }

    /**
     * @param int|null $clang
     * @return array
     */
    public static function getAllNews($clang = null)
    {
        global $REX;

        return self::getByWhere(array(
            'LIMIT' => $REX['ADDON']['asd_news']['config']['min-archive']
        ), $clang);
    }

    /**
     * @param int|null $clang
     * @return array
     */
    public static function getArchiveNews($clang = null)
    {
        global $REX;

        return self::getByWhere(array(
            'LIMIT' => 99999,
            'OFFSET' => $REX['ADDON']['asd_news']['config']['min-archive']
        ), $clang);
    }

    /**
     * @param DateTime $date
     * @param null|DateTime $date2
     * @param int|null $clang
     * @return array
     */
    public static function getNewsByPublishDate(DateTime $date, $date2 = null, $clang = null)
    {
        if ($date2 == null) {
            return self::getByWhere(array(
                'publishedAt' => '<= "' . $date->format('Y-m-d H:i:s') . '"'
            ), $clang);
        }

        return self::getByWhere(array(
            'publishedAt' => 'BETWEEN "' . $date->format('Y-m-d H:i:s') . '"
                                  AND "' . $date2->format('Y-m-d H:i:s') . '"'
        ), $clang);
    }

    /**
     * return an array of news filtered by a Where
     *
     * @param array $where
     * @param int|null $clang
     * @return array
     */
    private static function getByWhere(array $where, $clang = null)
    {
        global $REX;

        if ($clang == null) {
            $clang = $REX['CUR_CLANG'];
        }

        $where = array_merge(self::getDefaultWhere($clang), $where);

        $offset = self::getWhereSpecials('OFFSET', $where);
        $limit = self::getWhereSpecials('LIMIT', $where);

        if (isset($where))
            $where = self::generateWhere($where);

        $news = array();

        $sql = new rex_sql();
        $sql->setQuery('
        SELECT *
        FROM `' . $REX['TABLE_PREFIX'] . 'asd_news`
        ' . $where . '
        ORDER BY `publishedAt` DESC' . $limit . $offset);

        for ($i = 1; $i <= $sql->getRows(); $i++) {
            $news[] = new self(clone $sql);

            $sql->next();
        }

        return $news;
    }

    /**
     * @param int $clang
     * @return array
     */
    private static function getDefaultWhere($clang)
    {
        return array(
            'clang' => '= ' . $clang,
            'publishedAt' => 'BETWEEN "0000-00-00 00:01:00" AND "' . date('Y-m-d H:i:s') . '"'
        );
    }

    /**
     * @param array $whereArray
     * @return string
     */
    private static function generateWhere(array $whereArray)
    {
        $where = array();
        foreach ($whereArray as $name => $condition) {
            $where[] = '`' . $name . '` ' . $condition;
        }

        return 'WHERE ' . implode(' AND ' . PHP_EOL, $where);
    }

    /**
     * @param string $type
     * @param array $where
     * @return string
     */
    private static function getWhereSpecials($type, array &$where)
    {
        $return = '';
        if (isset($where[$type])) {
            $return = ' ' . $type . ' ' . $where[$type];
            unset($where[$type]);
        }

        return $return;
    }

    /**
     * return a select HTML-Tag
     *
     * @param string $name
     * @param string $value
     * @return string
     */
    public static function getCategorySelect($name, $value)
    {
        global $REX;

        $return = '<select name="' . $name . '">';

        $sql = new rex_sql();
        $sql->setQuery('SELECT * FROM `' . $REX['TABLE_PREFIX'] . 'asd_news_category` ORDER BY `id`');
        for ($i = 1; $i <= $sql->getRows(); $i++) {

            $selected = ($value == $sql->getValue('id')) ? ' selected="selected"' : '';

            $return .= '<option value="' . $sql->getValue('id') . '"' . $selected . '>' . $sql->getValue('name') . '</option>';

            $sql->next();
        }

        return $return;
    }

    /**
     * return the newest News-Id
     *
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