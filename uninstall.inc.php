<?php

include_once rex_path::addon('asd_news', 'classes/rex_asd_news_config.php');
rex_asd_news_config::init(
    'asd_news',
    'asd_news', // Without Prefix
    'asd_news_category' // Without Prefix
);

$REX['ADDON']['install'][rex_asd_news_config::getName()] = 0;
$REX['ADDON']['installmsg'][rex_asd_news_config::getName()]  = '';

include_once rex_path::addon(rex_asd_news_config::getName(), 'classes/metainfo/rex_asd_metainfo_install.php');

rex_asd_metainfo_install::setProperty();
if($error = rex_asd_metainfo_install::delFields()) {
    $REX['ADDON']['installmsg'][rex_asd_news_config::getName()] .= $error;
    $REX['ADDON']['install'][rex_asd_news_config::getName()] = 1;
} else {
    $sql = rex_sql::factory();
    $sql->setQuery('DROP TABLE IF EXISTS `' . rex_asd_news_config::getTable() . '`');
    $sql->setQuery('DROP TABLE IF EXISTS `' . rex_asd_news_config::getTableCategory() . '`');
}

if($sql->hasError()) {
    $msg = 'MySQL-Error: ' . $sql->getErrno() . '<br />';
    $msg .= $sql->getError();

    $REX['ADDON']['install'][rex_asd_news_config::getName()] = 1;
    $REX['ADDON']['installmsg'][rex_asd_news_config::getName()] .= $msg;
}


?>