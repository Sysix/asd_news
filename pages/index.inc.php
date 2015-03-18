<?php

require rex_path::src('layout' . DIRECTORY_SEPARATOR . 'top.php');

rex_title($I18N->msg('asd_news'), $REX['ADDON']['pages'][rex_asd_news_config::getName()]);

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');

if (!$subpage) {
    $subpage = 'news';
}

$BaseDir = rex_asd_news_config::getBaseUrl();
$baseDirFunc = rex_asd_news_config::getBaseUrl($func);

if (!rex_asd_news_config::getConfig('article')) {
    echo rex_warning($I18N->msg('asd_news_no_article_selected'));
}

switch ($subpage) {
    case 'news':
    case 'rubric':
    case 'faq':
        $path = rex_path::addon(rex_asd_news_config::getName(), 'pages' . DIRECTORY_SEPARATOR . $subpage . '.php');
        break;
    case 'settings':
    case 'metainfo':
        if ($REX['USER']->hasPerm(rex_asd_news_config::getName() . '[' . $subpage . ']') || $REX['USER']->isAdmin()) {
            $path = rex_path::addon(rex_asd_news_config::getName(), 'pages' . DIRECTORY_SEPARATOR . $subpage . '.php');
        }
        break;
    default:
        $path = rex_path::plugin(rex_asd_news_config::getName(), $subpage, 'pages' . DIRECTORY_SEPARATOR . $subpage . '.php');
        break;
}

require $path;

require rex_path::src('layout' . DIRECTORY_SEPARATOR . 'bottom.php');
?>