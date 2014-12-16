<?php
$REX['ADDON']['install']['asd_news'] = 0;
$REX['ADDON']['installmsg']['asd_news']  = '';


include_once rex_path::addon('asd_news', 'classes/metainfo/rex_asd_metainfo_install.php');

rex_asd_metainfo_install::setProperty();
if($error = rex_asd_metainfo_install::delFields()) {
    $REX['ADDON']['installmsg']['asd_news'] .= $error;
    $REX['ADDON']['install']['asd_news'] = 1;
} else {
    $sql = rex_sql::factory();
    $sql->setQuery('DROP TABLE IF EXISTS `' . $REX['TABLE_PREFIX'] . 'asd_news`');
    $sql->setQuery('DROP TABLE IF EXISTS `' . $REX['TABLE_PREFIX'] . 'asd_news_category`');
}

if($sql->hasError()) {
    $msg = 'MySQL-Error: ' . $sql->getErrno() . '<br />';
    $msg .= $sql->getError();

    $REX['ADDON']['install']['asd_news'] = 1;
    $REX['ADDON']['installmsg']['asd_news'] .= $msg;
}


?>