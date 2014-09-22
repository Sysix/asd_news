<?php

class rex_asd_news
{
    public static $SEO_ADDON = null;
    public static $SEO_URL_CONTROL = false;

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

        $default = '/files/' . $this->getValue('picture');
        $defaultType = 'index.php?rex_img_type=' . $imageType . '&amp;rex_img_file=' . $this->getValue('picture');

        if (self::$SEO_ADDON == 'seo42') {

            if ($imageType != null) {
                return seo42::getImageManagerFile($this->getValue('picture'), $imageType);
            }

            return seo42::getMediaFile($this->getValue('picture'));

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

        $params = array_merge(array('news-id' => $this->getValue('id')), $params);

        if (self::$SEO_URL_CONTROL) {
            return url_generate::getUrlById($REX['TABLE_PREFIX'] . 'asd_news', $this->getValue('id'));
        }

        return rex_getUrl($REX['ARTICLE_ID'], $REX['CUR_CLANG'], $params);
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
     * @return self
     */
    public function replaceSeoTags(array $tagNames)
    {
        $self = $this;
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $tagNames = rex_register_extension_point('ASD_NEWS_SEOTAGS', $tagNames);

        rex_register_extension('OUTPUT_FILTER', function ($subject) use ($tagNames, $self) {

            try {

                $document = new DOMDocument();
                $document->formatOutput = true;
                @$document->loadHTML($subject['subject']);
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

            } catch (DOMException $e) {
                echo rex_warning($e->getMessage());
            }

            return $document->saveHTML();

        });

        return $this;
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
        return self::getByWhere('`category` = ' . (int)$cat, $clang);
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
        return self::getByWhere('`id` IN (' . implode(',', (array)$newsIds) . ')', $clang);
    }

    /**
     * return an news filtered by id
     *
     * @param int $newsId
     * @param int|null $clang
     * @return array
     */
    public static function getNewsById($newsId, $clang = null)
    {
        $news = self::getByWhere('`id` = ' . (int)$newsId, $clang);

        return $news[0];
    }

    /**
     * @return array
     */
    public static function getAllNews($clang = null)
    {
        return self::getByWhere('1 = 1', $clang);
    }

    /**
     * @param DateTime $date
     * @param null|DateTime $date2
     * @return array
     */
    public static function getNewsByPublishDate(DateTime $date, $date2 = null)
    {
        if ($date2 == null) {
            return self::getByWhere('`publishedAt` <= ' . $date->format('Y-m-d H:i:s'));
        }

        return self::getByWhere('`publishedAt` BETWEEN "' . $date->format('Y-m-d H:i:s') . '"
                                                   AND "' . $date2->format('Y-m-d H:i:s') . '"');
    }

    /**
     * return an array of news filtered by a Where
     *
     * @param string $where
     * @param int|null $clang
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
        $sql->setQuery('
        SELECT *
        FROM `' . $REX['TABLE_PREFIX'] . 'asd_news`
        WHERE ' . $where . '
          AND `clang` = ' . (int)$clang . '
          AND `publishedAt` BETWEEN "0000-00-00 00:01:00" AND "' . date('Y-m-d H:i:s') . '"
        ORDER BY `publishedAt`');

        for ($i = 1; $i <= $sql->getRows(); $i++) {

            $news[] = new self(clone $sql);

            $sql->next();
        }

        return $news;
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