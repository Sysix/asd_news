<?php

class rex_asd_news_utils
{

    public static function addNewstoSitemap($params) {

        foreach(rex_asd_news::getAllNews() as $id => $news) {
            /** @var rex_asd_news $news */

            $params['subject'][rexseo_parse_article_name($news->getValue('title'))][$news->getValue('clang')] = array(
                'loc' => $news->getUrl(),
                'lastmod' => $news->getPublishDate()->format('c'),
                'changefreq' => 'monthly',
                'priority' => '0,7',
                'noindex' => ''
            );

        }

        die();

    }

}

?>