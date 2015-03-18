<?php

include_once rex_path::addon('asd_news', 'classes/rex_asd_news_config.php');
rex_asd_news_config::init(
    'asd_news',
    'asd_news', // Without Prefix
    'asd_news_category' // Without Prefix
);

$REX['ADDON']['install'][rex_asd_news_config::getName()] = 1;
$REX['ADDON']['installmsg'][rex_asd_news_config::getName()] = '';

/** @var rex_sql $sql */
$sql = rex_sql::factory();

$sql->setQuery('
CREATE TABLE IF NOT EXISTS `' . rex_asd_news_config::getTable() . '` (
  `news_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `clang` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` int(1) NOT NULL,
  `publishedAt` DATETIME NOT NULL,
  `publishedBy` int(11) NOT NULL,
  `createdAt` DATETIME NOT NULL,
  `createdBy` int(11) NOT NULL,
  `updatedAt` DATETIME NOT NULL,
  `updatedBy` int(11) NOT NULL,
  PRIMARY KEY (`news_id`),
  UNIQUE KEY (`id`, `clang`)
);');


$sql->setQuery('
CREATE TABLE IF NOT EXISTS `' . rex_asd_news_config::getTableCategory() . '` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);');

rex_asd_news_config::createDataConfigIfNotExists();

if($sql->hasError()) {
    $msg = 'MySQL-Error: ' . $sql->getErrno() . '<br />';
    $msg .= $sql->getError();

    $REX['ADDON']['install'][rex_asd_news_config::getName()] = 0;
    $REX['ADDON']['installmsg'][rex_asd_news_config::getName()] .= $msg;
}

if(!OOAddon::isAvailable('metainfo')) {
    $REX['ADDON']['install'][rex_asd_news_config::getName()] = 0;
    $REX['ADDON']['installmsg'][rex_asd_news_config::getName()] .= 'Metainfo Addon nicht installiert';

} else {
    include_once rex_path::addon(rex_asd_news_config::getName(), 'classes/metainfo/rex_asd_metainfo_install.php');

    rex_asd_metainfo_install::setProperty();
    if ($error = rex_asd_metainfo_install::addFields()) {
        $REX['ADDON']['install'][rex_asd_news_config::getName()] = 0;
        $REX['ADDON']['installmsg'][rex_asd_news_config::getName()] .= $error;
    }
}
?>