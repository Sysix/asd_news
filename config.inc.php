<?php

// register addon
$REX['ADDON']['name']['asd_news'] = 'News';
$REX['ADDON']['version']['asd_news'] = '0.1 Beta';
$REX['ADDON']['author']['asd_news'] = 'ArtStudioDESIGN';
$REX['ADDON']['supportpage']['asd_news'] = 'http://redaxo.org/forum/';
$REX['ADDON']['perm']['asd_news'] = 'asd_news[]';

// set permission
$REX['EXTPERM'][] = 'asd_news[publish]';
$REX['EXTPERM'][] = 'asd_news[create]';

if ($REX['REDAXO']) {
    $I18N->appendFile(rex_path::addon('asd_news', 'lang' . DIRECTORY_SEPARATOR));
}

//set pages
$REX['ADDON']['asd_news']['SUBPAGES'] = array(
    array('news', 'Neuigkeiten'),
    array('rubric', 'Rubriken'),
    array('settings', 'Einstellungen')
);

// set config
$REX['ADDON']['asd_news']['configFile'] = rex_path::addon('asd_news', 'data/config.json');
$REX['ADDON']['asd_news']['config'] = json_decode(file_get_contents($REX['ADDON']['asd_news']['configFile']), true);

// Metainfo
$page = rex_request('page', 'string', '');
rex_register_extension('PAGE_CHECKED', function ($params) use ($page, $REX, $I18N) {


    if ($page == 'metainfo') {

        $metanews = new rex_be_page('News', array('page' => $page, 'subpage' => 'asd_news'));
        $metanews->setPath(rex_path::addon('asd_news', 'pages/metainfo.php'));
        $metanews->setHref('index.php?page=' . $page . '&subpage=asd_news');

        $mainPage = $params['pages'][$page]->getPage();
        $mainPage->addSubPage($metanews);

    }
    /*

        $REX['ADDON']['prefixes'][$page][]  = 'asd_';
        $REX['ADDON']['metaTables'][$page]['asd_'] = $REX['TABLE_PREFIX'] . 'asd_news';
        $REX['ADDON']['pages'][$page][] = array('asd_news', 'News');

    */

});

require_once rex_path::addon('asd_news', 'functions/rex_asd_news_language.php');
require_once rex_path::addon('asd_news', 'functions/asd_news_jquery.php');
require_once rex_path::addon('asd_news', 'classes/rex_news_form.php');
require_once rex_path::addon('asd_news', 'classes/rex_asd_news.php');
require_once rex_path::addon('asd_news', 'classes/rex_asd_news_utils.php');

// Seo Addon setzen
foreach (array('rexseo', 'yrewrite', 'seo42') as $seoAddon) {
    if (OOAddon::isAvailable($seoAddon)) {
        rex_asd_news::$SEO_ADDON = $seoAddon;
    }
}

// url_control Plugin gesetzt?
if (!is_null(rex_asd_news::$SEO_ADDON)) {
    rex_asd_news::$SEO_URL_CONTROL = OOPlugin::isAvailable(rex_asd_news::$SEO_ADDON, 'url_control');
}

// SEO Sitemap.xml
/*
rex_register_extension('REXSEO_SITEMAP_ARRAY_CREATED', function($params) {
    rex_asd_news_utils::addNewstoSitemap($params);
});
*/


if ($REX['REDAXO']) {

    $page = rex_request('page');
    $func = rex_request('func');

    if ($page == 'asd_news') {
        rex_register_extension('PAGE_HEADER', 'asd_news_setjQueryTags');

        if ($func == 'publish') {
            $id = rex_post('id', 'int');
            $clang = rex_post('clang', 'int');
            try {
                $time = new DateTime(rex_post('time'));
            } catch (Exception $e) {
                $time = new DateTime();
            }

            $sql = new rex_sql();
            $sql->setTable($REX['TABLE_PREFIX'] . 'asd_news');
            $sql->setWhere('`id` = ' . $id . ' AND `clang` = ' . $clang);

            if ($REX['ADDON']['asd_news']['config']['published-lang'] == 'all') {
                $sql->setWhere('`id` = ' . $id);
            }

            $sql->setValue('publishedAt', $time->format('Y-m-d H:i:s'));
            $sql->setValue('publishedBy', $REX['USER']->getValue('user_id'));
            $sql->setValue('status', 1);

            $sql->update();

            echo $time->format('Y-m-d H:i') . '
        <a href="index.php?page=asd_news&subpage=news&clang=' . $clang . '&func=unpublish&id=' . $id . '">
            <img src="../' . $REX['MEDIA_ADDON_DIR'] . '/asd_news/unpublished.svg"
            width="20" height="20" style="vertical-align: middle; margin-left: 5px">
        </a>';
            exit();
        }
    }

    rex_register_extension('CLANG_ADDED', 'asd_news_addClang');
    rex_register_extension('CLANG_DELETED', 'asd_news_deleteClang');

    // autoload Plugins
    $plugins = OOPlugin::getAvailablePlugins('asd_news');
    foreach ($plugins as $name) {
        if (file_exists(rex_path::plugin('asd_news', $name, 'pages' . DIRECTORY_SEPARATOR . $name))) {

            $I18N->appendFile(rex_path::plugin('asd_news', $name, 'lang' . DIRECTORY_SEPARATOR));

            array_push($REX['ADDON']['asd_news']['SUBPAGES'], array($name, $I18N->msg('asd_news_' . $name)));
        }
    }

} else {

    if ($REX['ADDON']['asd_news']['config']['include-css'] == "true") {

        rex_register_extension('OUTPUT_FILTER', function ($params) use ($REX) {

            return str_replace(
                '</head>',
                '<link href="' . $REX['MEDIA_ADDON_DIR'] . '/asd_news/news.css" rel="stylesheet"></head>',
                $params['subject']
            );

        });

    }

}

?>