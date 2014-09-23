<?php
$sql = rex_sql::factory();
$sql->setQuery('DROP TABLE IF EXISTS `' . $REX['TABLE_PREFIX'] . 'asd_news`');
$sql->setQuery('DROP TABLE IF EXISTS `' . $REX['TABLE_PREFIX'] . 'asd_news_category`');
$REX['ADDON']['install']['asd_news'] = 0;

?>