<?php

/**
 * @var array $REX
 * @var i18n $I18N
 */

if ($REX['REDAXO'] && is_object($REX['USER'])) {
    $I18N->appendFile(rex_path::addon('asd_news', 'lang' . DIRECTORY_SEPARATOR));

    // register addon
    $REX['ADDON']['name']['asd_news'] = $I18N->msg('asd_news');
    $REX['ADDON']['version']['asd_news'] = '1.4.0 DEV';
    $REX['ADDON']['author']['asd_news'] = 'ArtStudioDESIGN';
    $REX['ADDON']['supportpage']['asd_news'] = 'http://redaxo.org/forum/';
    $REX['ADDON']['perm']['asd_news'] = 'asd_news[]';

    // set permission
    $REX['PERM'][] = 'asd_news[]';
    $REX['EXTPERM'][] = 'asd_news[settings]';
    $REX['EXTPERM'][] = 'asd_news[metainfo]';

    //set pages
    $news = new rex_be_page($I18N->msg('asd_news_news'), array(
        'page' => 'asd_news',
        'subpage' => (rex_request('subpage') == 'news') ? rex_request('subpage') : ''
    ));
    $news->setHref('index.php?page=asd_news&subpage=news');

    $rubric = new rex_be_page($I18N->msg('asd_news_rubric'), array(
        'page' => 'asd_news',
        'subpage' => 'rubric'
    ));
    $rubric->setHref('index.php?page=asd_news&subpage=rubric');

    $faq = new rex_be_page($I18N->msg('asd_news_faq'), array(
        'page' => 'asd_news',
        'subpage' => 'faq'
    ));
    $faq->setHref('index.php?page=asd_news&subpage=faq');

    $settings = new rex_be_page($I18N->msg('asd_news_settings'), array(
        'page' => 'asd_news',
        'subpage' => 'settings'
    ));
    $settings->setHref('index.php?page=asd_news&subpage=settings');
    $settings->setRequiredPermissions(array('asd_news[settings]'));


    $REX['ADDON']['pages']['asd_news'] = array($news, $rubric, $faq, $settings);

    if(OOAddon::isAvailable('metainfo')) {
        $meta = new rex_be_page($I18N->msg('asd_news_metainfo'), array(
            'page' => 'asd_news',
            'subpage' => 'metainfo'
        ));
        $meta->setHref('index.php?page=asd_news&subpage=metainfo');
        $meta->setRequiredPermissions(array('asd_news[metainfo]'));

        $REX['ADDON']['pages']['asd_news'][] = $meta;

        // Meta Tables hinzufügen
        rex_register_extension('PAGE_CHECKED', 'rex_asd_metainfo_install::setProperty');
    }
}


// set config
$REX['ADDON']['asd_news']['configFile'] = rex_path::addonData('asd_news', 'config.json');
$REX['ADDON']['asd_news']['config'] = json_decode(file_get_contents($REX['ADDON']['asd_news']['configFile']), true);

require_once rex_path::addon('asd_news', 'functions/rex_asd_news_language.php');
require_once rex_path::addon('asd_news', 'functions/asd_news_jquery.php');
require_once rex_path::addon('asd_news', 'classes/rex_news_form.php');
require_once rex_path::addon('asd_news', 'classes/rex_asd_news.php');
require_once rex_path::addon('asd_news', 'classes/rex_asd_news_utils.php');
require_once rex_path::addon('asd_news', 'classes/rex_asd_pager.php');
require_once rex_path::addon('asd_news', 'classes/metainfo/rex_asd_metainfo_install.php');

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


if ($REX['REDAXO'] && is_object($REX['USER'])) {

    $page = rex_request('page');
    $func = rex_request('func');

    if ($page == 'asd_news') {
        require_once rex_path::addon('asd_news', 'classes/rex_asd_news_ajaxHandler.php');

        rex_register_extension('PAGE_HEADER', 'asd_news_setjQueryTags');
        // Ajax Publish
        if ($func == 'publish') {
            $id = rex_post('id', 'int');
            $clang = rex_post('clang', 'int');
            try {
                $time = new DateTime(rex_post('time'));
            } catch (Exception $e) {
                $time = new DateTime();
            }

            echo rex_asd_news_ajaxHandler::publishNews($id, $clang, $time);

            exit();
        }
    }

    // add / remove News if lang added or removed
    rex_register_extension('CLANG_ADDED', 'asd_news_addClang');
    rex_register_extension('CLANG_DELETED', 'asd_news_deleteClang');
    // check if image in use
    rex_register_extension('OOMEDIA_IS_IN_USE', 'rex_asd_news_utils::isImageInUse');

    // autoload Plugins
    $plugins = OOPlugin::getAvailablePlugins('asd_news');
    foreach ($plugins as $name) {
        if (file_exists(rex_path::plugin('asd_news', $name, 'pages' . DIRECTORY_SEPARATOR . $name))) {

            $I18N->appendFile(rex_path::plugin('asd_news', $name, 'lang' . DIRECTORY_SEPARATOR));

            array_push($REX['ADDON']['asd_news']['SUBPAGES'], array($name, $I18N->msg('asd_news_' . $name)));
        }
    }

}

?>