<?php

class rex_asd_news
{
    /**
     * @deprecated $SEO_ADDON
     * @since v1.4
     */
    public static $SEO_ADDON = null;
    /**
     * @deprecated $SEO_URL_CONTROL
     * @since v1.4
     */
    public static $SEO_URL_CONTROL = false;

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
     * @param int|null $arg2
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
        $this->sql->setQuery('SELECT * FROM `' . rex_asd_news_config::getTable() . '` WHERE `id` = ' . (int)$id . ' AND `clang` = ' . (int)$clang);

        return $this;
    }

    /**
     * @see rex_sql::getValue
     * @param string $name
     * @param mixed $default
     * @return string
     */
    public function getValue($name, $default = null)
    {
        if (strpos(rex_asd_news_config::OLD_SQL_COLUMNS, '|' . $name . '|') !== false) {
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
     * @return string
     */
    public function getRubric()
    {
        $sqlCols = rex_asd_news_config::getConfig('sql');

        return $this->getValue($sqlCols['category']);
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
        $sqlCols = rex_asd_news_config::getConfig('sql');
        $pictureCol = $sqlCols['picture'];

        if (rex_extension_is_registered('ASD_NEWS_GETIMAGE')) {
            return rex_register_extension_point('ASD_NEWS_GETIMAGE', $pictureCol);
        }

        $seoSettings = rex_asd_news_config::getSeoSettings();

        // Use SeoMethod
        if (isset($seoSettings['image']) && $seoSettings['image']) {

            if ($imageType != null && isset($seoSettings['image']['manager']) && $seoSettings['image']['manager']) {
                return call_user_func($seoSettings['image']['manager'], $this->getValue($pictureCol), $imageType);
            }

            if (isset($seoSettings['image']['default']) && $seoSettings['image']['default']) {
                return call_user_func($seoSettings['image']['default'], $this->getValue($pictureCol));
            }
        }

        if ($imageType != null) {
            return 'index.php?rex_img_type=' . $imageType . '&amp;rex_img_file=' . $this->getValue($pictureCol);
        }

        return '/files/' . $this->getValue($pictureCol);
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
            'clang' => $this->getValue('clang'),
            'article-id' => rex_asd_news_config::getConfig('article', $REX['ARTICLE_ID'])
        ), $params);

        $params = rex_register_extension_point('ASD_NEWS_GENERATE_URL', $params);

        if (rex_asd_news_config::isControlPlugin()) {
            return $this->getRealControlUrl();
        }

        $art_id = $params['article-id'];
        $clang = $params['clang'];

        unset($params['article-id'], $params['clang']);

        return rex_getUrl($art_id, $clang, $params);
    }

    /**
     * return the real generated url
     * @return bool|string
     */
    public function getRealControlUrl()
    {
        if (class_exists('url_generate')) {
            $mainArticle = rex_asd_news_config::getConfig('article');
            $list = url_generate::$paths[rex_asd_news_config::getTable()][$mainArticle];

            if (isset($list[$this->getValue('clang')]) &&
                isset($list[$this->getValue('clang')][$this->getValue('id')])
            ) {
                return url_generate::getCleanUrl($list[$this->getValue('clang')][$this->getValue('id')]);
            }
        }

        return false;
    }

    /**
     * return string
     */
    public function getRubricName()
    {
        static $rubrics = array();

        if (empty($rubrics)) {
            $sql = new rex_sql();
            $sql->setQuery('SELECT * FROM `' . rex_asd_news_config::getTableCategory() . '`');
            for ($i = 1; $i <= $sql->getRows(); $i++) {
                $rubrics[$sql->getValue('id')] = $sql->getValue('name');

                $sql->next();
            }
        }

        return $rubrics[$this->getRubric()];
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
     * @deprecated
     * @since v1.4.4
     * @param array $tagNames
     * @return mixed
     */
    public static function replaceSeoTags(array $tagNames)
    {
        return static::replaceMetaTags($tagNames);
    }

    /**
     * replace the meta keywords, description html tags
     *
     * replaceMetaTags(array(
     *   'keywords' => $news->getValue('keywords')
     *   'description' => $news->getValue('description')
     *   'og:description' => $news->getValue('facebook_description')
     * ));
     *
     * @param array $tagNames
     * @return string|bool
     */
    public static function replaceMetaTags(array $tagNames)
    {
        $tagNames = rex_register_extension_point('ASD_NEWS_SEOTAGS', $tagNames);

        rex_register_extension('OUTPUT_FILTER', function ($subject) use ($tagNames) {

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

                return $doctype[0] . $document->saveHTML($document->documentElement);

            } catch (DOMException $e) {
                echo rex_warning($e->getMessage());
            }

            return $subject['subject'];
        });

        return false;
    }

    /**
     * get the news ID for the Frontend
     * @return int|null
     */
    public static function getNewsId()
    {
        $id = null;

        if (rex_asd_news_config::isControlPlugin()) {
            $id = url_generate::getId(rex_asd_news_config::getTable());
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
        $sqlCols = rex_asd_news_config::getConfig('sql');

        return self::getByWhere(array(
            $sqlCols['category'] => '= ' . (int)$cat
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
        return self::getByWhere(array(
            'LIMIT' => rex_asd_news_config::getConfig('min-archive')
        ), $clang);
    }

    /**
     * @param int|null $clang
     * @return array
     */
    public static function getArchiveNews($clang = null)
    {
        return self::getByWhere(array(
            'LIMIT' => 99999,
            'OFFSET' => rex_asd_news_config::getConfig('min-archive')
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
    public static function getByWhere(array $where, $clang = null)
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
        FROM `' . rex_asd_news_config::getTable() . '`
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
            if ($condition === null) {
                continue;
            }
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
        $return = '<select name="' . $name . '">';

        $sql = new rex_sql();
        $sql->setQuery('SELECT * FROM `' . rex_asd_news_config::getTableCategory() . '` ORDER BY `id`');
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
        $sql = new rex_sql();
        $sql->setQuery('SELECT `id` FROM `' . rex_asd_news_config::getTable() . '` ORDER BY id DESC LIMIT 0, 1');

        if ($sql->getRows()) {
            return (int)$sql->getValue('id');
        }

        return 0;
    }

}

?>