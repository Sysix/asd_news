<?php

class rex_asd_news_utils
{

    /**
     * @param array $params
     */
    public static function addNewstoSitemap($params)
    {
        $seoSettings = rex_asd_news_config::getSeoSettings();

        $map = new ReflectionClass($seoSettings['sitemap']['class']);
        $map = $map->newInstanceWithoutConstructor();

        $freq = new ReflectionMethod($seoSettings['sitemap']['class'], 'calc_article_changefreq');
        $freq->setAccessible(true);

        $prio = new ReflectionMethod($seoSettings['sitemap']['class'], 'calc_article_priority');
        $prio->setAccessible(true);

        $mainArticle = rex_asd_news_config::getConfig('article');
        $mainArticle = new rex_article($mainArticle);

        foreach (rex_asd_news::getAllNews() as $id => $news) {
            /** @var rex_asd_news $news */

            $fragment = array(
                'loc' => $news->getUrl(),
                'lastmod' => $news->getPublishDate()->format('c'),
                'changefreq' => $freq->invokeArgs($map, array(
                    $news->getPublishDate()->getTimestamp()
                )),
                'priority' => $prio->invokeArgs($map, array(
                    $mainArticle->getValue('id'),
                    $mainArticle->getValue('clang'),
                    $mainArticle->getValue('path') . '|' . $news->getValue('id')
                ))
            );

            $params['subject'][rex_asd_news_config::getName()][] = $fragment;
        }

        return $params['subject'];
    }

    public static function isImageInUse($params)
    {
        /** @var i18n $I18N */
        global $I18N;

        $sql = new rex_sql();
        $sqlCols = rex_asd_news_config::getConfig('sql');

        $sql->setQuery('SELECT `id`, `title` FROM `' . rex_asd_news_config::getTable() . '` WHERE `' . $sqlCols['picture'] . '` = "' . $params['filename'] . '"');
        if ($sql->getRows()) {
            $message = $I18N->msg('asd_news') . '<br /><ul>';

            for ($i = 1; $i <= $sql->getRow(); $i++) {
                $message .= '
    <li>
        <a href="index.php?page=' . rex_asd_news_config::getName() . '&amp;func=edit&amp;id=' . $sql->getValue('id') . '">
            ' . $sql->getValue('title') . '
        </a>
    </li>';
            }

            $message .= '</ul>';

            $params['subject'][] = $message;
        }

        return $params['subject'];
    }

    /**
     * @param string $file
     * @return string
     */
    public static function getModulCode($file)
    {
        $file = rex_path::addon(rex_asd_news_config::getName(), 'modules/' . $file);

        return file_get_contents($file);
    }

}

?>