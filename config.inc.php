<?php

/**
 * @var array $REX
 * @var i18n $I18N
 */


require_once rex_path::addon('asd_news', 'classes/rex_asd_news_config.php');

rex_asd_news_config::init(
    'asd_news',
    'asd_news', // Without Prefix
    'asd_news_category' // Without Prefix
);

// Abwärtskompatibilität
$REX['ADDON'][rex_asd_news_config::getName()]['configFile'] = rex_asd_news_config::$configFile;
$REX['ADDON'][rex_asd_news_config::getName()]['config'] = rex_asd_news_config::$config;

require_once rex_path::addon(rex_asd_news_config::getName(), 'functions/rex_asd_news_language.php');
require_once rex_path::addon(rex_asd_news_config::getName(), 'functions/asd_news_jquery.php');
require_once rex_path::addon(rex_asd_news_config::getName(), 'classes/rex_news_form.php');
require_once rex_path::addon(rex_asd_news_config::getName(), 'classes/rex_asd_news.php');
require_once rex_path::addon(rex_asd_news_config::getName(), 'classes/rex_asd_news_utils.php');
require_once rex_path::addon(rex_asd_news_config::getName(), 'classes/rex_asd_pager.php');
require_once rex_path::addon(rex_asd_news_config::getName(), 'classes/metainfo/rex_asd_metainfo_install.php');

// Seo Addon setzen
rex_asd_news_config::setSeoAddon('rexseo', 'yrewrite', 'seo42');

if (rex_asd_news_config::isControlPlugin()) {
    require_once rex_path::addon('asd_news', 'classes/rex_asd_news_url_control.php');
}
// SEO Sitemap.xml
$seoSettings = rex_asd_news_config::getSeoSettings();
if ($seoSettings['sitemap']['extension']) {
    rex_register_extension($seoSettings['sitemap']['extension'], function ($params) {
        return rex_asd_news_utils::addNewstoSitemap($params);
    });
}

if ($REX['REDAXO'] && is_object($REX['USER'])) {
    $I18N->appendFile(rex_path::addon(rex_asd_news_config::getName(), 'lang' . DIRECTORY_SEPARATOR));

    // register addon
    $REX['ADDON']['name'][rex_asd_news_config::getName()] = $I18N->msg('asd_news');
    $REX['ADDON']['version'][rex_asd_news_config::getName()] = '1.4.0';
    $REX['ADDON']['author'][rex_asd_news_config::getName()] = 'ArtStudioDESIGN';
    $REX['ADDON']['supportpage'][rex_asd_news_config::getName()] = 'http://redaxo.org/forum/';
    $REX['ADDON']['perm'][rex_asd_news_config::getName()] = 'asd_news[]';

    // set permission
    $REX['PERM'][] = rex_asd_news_config::getName() . '[]';
    $REX['EXTPERM'][] = rex_asd_news_config::getName() . '[settings]';
    $REX['EXTPERM'][] = rex_asd_news_config::getName() . '[metainfo]';

    //set pages
    $news = new rex_be_page($I18N->msg('asd_news_news'), array(
        'page' => rex_asd_news_config::getName(),
        'subpage' => (rex_request('subpage') == 'news') ? rex_request('subpage') : ''
    ));
    $news->setHref('index.php?page=' . rex_asd_news_config::getName() . '&subpage=news');

    $rubric = new rex_be_page($I18N->msg('asd_news_rubric'), array(
        'page' => rex_asd_news_config::getName(),
        'subpage' => 'rubric'
    ));
    $rubric->setHref('index.php?page=' . rex_asd_news_config::getName() . '&subpage=rubric');

    $faq = new rex_be_page($I18N->msg('asd_news_faq'), array(
        'page' => rex_asd_news_config::getName(),
        'subpage' => 'faq'
    ));
    $faq->setHref('index.php?page=' . rex_asd_news_config::getName() . '&subpage=faq');

    $settings = new rex_be_page($I18N->msg('asd_news_settings'), array(
        'page' => rex_asd_news_config::getName(),
        'subpage' => 'settings'
    ));
    $settings->setHref('index.php?page=' . rex_asd_news_config::getName() . '&subpage=settings');
    $settings->setRequiredPermissions(array(rex_asd_news_config::getName() . '[settings]'));


    $REX['ADDON']['pages'][rex_asd_news_config::getName()] = array($news, $rubric, $faq, $settings);

    if (OOAddon::isAvailable('metainfo')) {
        $meta = new rex_be_page($I18N->msg('asd_news_metainfo'), array(
            'page' => rex_asd_news_config::getName(),
            'subpage' => 'metainfo'
        ));
        $meta->setHref('index.php?page=' . rex_asd_news_config::getName() . '&subpage=metainfo');
        $meta->setRequiredPermissions(array(rex_asd_news_config::getName() . '[metainfo]'));

        $REX['ADDON']['pages'][rex_asd_news_config::getName()][] = $meta;

        // Meta Tables hinzufügen
        rex_register_extension('PAGE_CHECKED', 'rex_asd_metainfo_install::setProperty');
    }

    $page = rex_request('page');
    $func = rex_request('func');

    if ($page == rex_asd_news_config::getName()) {
        require_once rex_path::addon(rex_asd_news_config::getName(), 'classes/rex_asd_news_ajaxHandler.php');

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
    $plugins = OOPlugin::getAvailablePlugins(rex_asd_news_config::getName());
    foreach ($plugins as $name) {
        if (file_exists(rex_path::plugin(rex_asd_news_config::getName(), $name, 'pages' . DIRECTORY_SEPARATOR . $name))) {

            $I18N->appendFile(rex_path::plugin(rex_asd_news_config::getName(), $name, 'lang' . DIRECTORY_SEPARATOR));

            array_push(
                $REX['ADDON'][rex_asd_news_config::getName()]['SUBPAGES'],
                array(
                    $name,
                    $I18N->msg(rex_asd_news_config::getName() . '_' . $name)
                )
            );
        }
    }

}

?>