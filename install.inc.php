<?php

$REX['ADDON']['install']['asd_news'] = 1;

if(!OOAddon::isAvailable('metainfo')) {
    $REX['ADDON']['install']['asd_news'] = 0;
    $REX['ADDON']['installmsg']['asd_news'] = 'Bitte installieren & aktivieren Sie das Addon "metainfo"';
}

$sql = rex_sql::factory();
$sql->setQuery('
CREATE TABLE IF NOT EXISTS `' . $REX['TABLE_PREFIX'] . 'asd_news` (
  `news_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `clang` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` int(11) NOT NULL,
  `picture` varchar(255) NOT NULL,
  `text` TEXT NOT NULL,
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
CREATE TABLE IF NOT EXISTS `' . $REX['TABLE_PREFIX'] . 'asd_news_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);');

if($sql->hasError()) {
    $msg = 'MySQL-Error: ' . $sql->getErrno() . '<br />';
    $msg .= $sql->getError();

    $REX['ADDON']['install']['asd_news'] = 0;
    $REX['ADDON']['installmsg']['asd_news'] = $msg;
}

?>