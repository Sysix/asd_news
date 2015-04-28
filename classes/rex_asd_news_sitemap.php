<?php

/**
 * Class rex_asd_news_sitemap
 */
class rex_asd_news_sitemap
{

    public static function addNewstoSitemap($params)
    {
        $mainArticle = rex_asd_news_config::getConfig('article');
        $mainArticle = new rex_article($mainArticle);

        foreach (rex_asd_news::getByWhere(array(
            'clang' => null
        )) as $news) {
            /** @var rex_asd_news $news */

            if(!$url = self::getRealControlUrl($news->getValue('id'), $news->getValue('clang'))) {
                $url = $news->getUrl();
            }
            $fragment = array(
                'loc' => $url,
                'lastmod' => $news->getPublishDate()->format('c'),
                'changefreq' => self::calc_article_changefreq($news->getPublishDate()->getTimestamp()),
                'priority' => self::calc_article_priority(
                    $mainArticle->getValue('id'),
                    $mainArticle->getValue('clang'),
                    $mainArticle->getValue('path') . '|' . $news->getValue('id')
                )
            );

            $params['subject'][rex_asd_news_config::getName()][] = $fragment;
        }

        return $params['subject'];
    }

    /**
     * @param $newsId
     * @param $clang
     * @return bool|string
     */
    public static function getRealControlUrl($newsId, $clang)
    {
        if (class_exists('url_generate')) {
            $list = url_generate::$paths[rex_asd_news_config::getTable()];

            $mainArticle = rex_asd_news_config::getConfig('article');

            if (isset($list[$mainArticle]) && isset($list[$mainArticle][$clang])) {
                if (isset($list[$mainArticle][$clang][$newsId])) {
                    return $list[$mainArticle][$clang][$newsId];
                }
            }
        }

        return false;
    }

    /**
     * CALCULATE ARTICLE PRIORITY
     *
     * @param int $article_id rex_article.article_id
     * @param int $clang rex_article.clang
     * @param string $path rex_article.path
     * @return float priority
     */
    public static function calc_article_priority($article_id, $clang, $path)
    {
        global $REX;

        if ($article_id == $REX['START_ARTICLE_ID'] && $clang == $REX['START_CLANG_ID']) {
            return 1.0;
        }

        $prio = 1.0 - (0.1 * (count(explode('|', $path)) - 1));

        if ($prio >= 0) {
            return $prio;
        } else {
            return 0.0;
        }
    }


    /**
     * CALCULATE ARTICLE CHANGEFREQ
     *
     * @param int $updatedate rex_article.updatedate
     * @return string change frequency  [never|yearly|monthly|weekly|daily|hourly|always]
     */
    public static function calc_article_changefreq($updatedate)
    {
        $age = time() - $updatedate;

        switch ($age) {
            case($age < 604800):
                return 'daily';
            case($age < 2419200):
                return 'weekly';
            default:
                return 'monthly';
        }
    }

}