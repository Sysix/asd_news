<?php

require rex_path::src('layout'.DIRECTORY_SEPARATOR.'top.php');

rex_title($I18N->msg('asd_news'), $REX['ADDON']['asd_news']['SUBPAGES']);

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string', 'news');
$func = rex_request('func', 'string');

$BaseDir = 'index.php?page='.$page.'&amp;subpage='.$subpage;
$baseDirFunc = $BaseDir.'&amp;func='.$func;


switch($subpage) {
    case 'news':
    case 'rubric':
        $path = rex_path::addon('asd_news', 'pages'.DIRECTORY_SEPARATOR.$subpage.'.php');
        break;
    case 'settings':
    case 'faq':
        if($REX['USER']->hasPerm('asd_news['.$subpage.']') || $REX['USER']->isAdmin()) {
            $path = rex_path::addon('asd_news', 'pages' . DIRECTORY_SEPARATOR . $subpage . '.php');
        }
        break;
    default:
        $path = rex_path::plugin('asd_news', $subpage, 'pages'.DIRECTORY_SEPARATOR.$subpage.'.php');
        break;
}

require $path;

require rex_path::src('layout'.DIRECTORY_SEPARATOR.'bottom.php');
?>