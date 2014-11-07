<?php

/** @var i18n $I18N */

if ($REX['REDAXO'] && is_object($REX['USER'])) {
    $I18N->appendFile(rex_path::addon('asd_news', 'lang' . DIRECTORY_SEPARATOR));

    // register addon
    $REX['ADDON']['name']['asd_news'] = $I18N->msg('asd_news');
    $REX['ADDON']['version']['asd_news'] = '1.3.1 DEV';
    $REX['ADDON']['author']['asd_news'] = 'ArtStudioDESIGN';
    $REX['ADDON']['supportpage']['asd_news'] = 'http://redaxo.org/forum/';
    $REX['ADDON']['perm']['asd_news'] = 'asd_news[]';

    // set permission
    $REX['PERM'][] = 'asd_news[]';
    $REX['EXTPERM'][] = 'asd_news[settings]';
    $REX['EXTPERM'][] = 'asd_news[faq]';

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

    $settings = new rex_be_page($I18N->msg('asd_news_settings'), array(
        'page' => 'asd_news',
        'subpage' => 'settings'
    ));
    $settings->setHref('index.php?page=asd_news&subpage=settings');
    $settings->setRequiredPermissions(array('asd_news[settings]'));

    $faq = new rex_be_page($I18N->msg('asd_news_faq'), array(
        'page' => 'asd_news',
        'subpage' => 'faq'
    ));
    $faq->setHref('index.php?page=asd_news&subpage=faq');
    $faq->setRequiredPermissions(array('asd_news[faq]'));

    $REX['ADDON']['pages']['asd_news'] = array($news, $rubric, $settings, $faq);

    // TODO: Metainfo intergration
    /*
        if(OOAddon::isAvailable('metainfo')) {
            $meta = new rex_be_page('Felder', array(
                'page' => 'asd_news',
                'subpage' => 'metainfo'
            ));
            $meta->setHref('index.php?page=asd_news&subpage=metainfo');
            $meta->setPath(rex_path::addon('metainfo', 'pages/field.inc.php'));

            $REX['ADDON']['pages']['asd_news'][] = $meta;
        }
    */
}


// set config
$REX['ADDON']['asd_news']['configFile'] = rex_path::addonData('asd_news', 'config.json');
$REX['ADDON']['asd_news']['config'] = json_decode(file_get_contents($REX['ADDON']['asd_news']['configFile']), true);

// Metainfo
$page = rex_request('page', 'string', '');


require_once rex_path::addon('asd_news', 'functions/rex_asd_news_language.php');
require_once rex_path::addon('asd_news', 'functions/asd_news_jquery.php');
require_once rex_path::addon('asd_news', 'classes/rex_news_form.php');
require_once rex_path::addon('asd_news', 'classes/rex_asd_news.php');
require_once rex_path::addon('asd_news', 'classes/rex_asd_news_utils.php');
require_once rex_path::addon('asd_news', 'classes/rex_asd_pager.php');

require_once rex_path::addon('asd_news', 'classes/rex_form_element_asd_news_textarea.php');

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

            $urlBase = 'index.php?list=232cc606fc1a5fb5cf5badfc8e360ae0&amp;page=asd_news&amp;subpage=news&amp;clang=' . $clang . '&amp;func=';


            $sql->setQuery('SELECT * FROM `' . $REX['TABLE_PREFIX'] . 'asd_news` WHERE `id` = ' . $id . ' AND `clang` = ' . $clang);

            echo '
        <td>' . $id . '</td>
        <td>' . $sql->getValue('title') . '</td>
        <td><span>' . $time->format('Y-m-d H:i') . '</span></td>
        <td><a href="' . $urlBase . 'unpublish&amp;id=' . $id . '" class="rex-offline" onclick="return confirm(\'' . $I18N->msg('asd_news_really_unpublish') . '\');">' . $I18N->msg('asd_news_unpublish') . '</a></td>
        <td><a href="' . $urlBase . 'edit&amp;id=' . $id . '">' . $I18N->msg('edit') . '</a></td>
        <td><a href="' . $urlBase . 'delete&amp;id=' . $id . '" onclick="return confirm(\'' . $I18N->msg('asd_news_really_delete') . '\');">' . $I18N->msg('delete') . '</a></td>
        <td><a href="' . $urlBase . 'status&amp;id=' . $id . '" class="rex-online">' . $I18N->msg('status_online') . '</a></td>

            ';

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