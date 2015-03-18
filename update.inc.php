<?php

global $REX;

include_once rex_path::addon('asd_news', 'classes/rex_asd_news_config.php');
rex_asd_news_config::init(
    'asd_news',
    'asd_news', // Without Prefix
    'asd_news_category' // Without Prefix
);

$REX['ADDON']['update'][rex_asd_news_config::getName()] = 1;
$REX['ADDON']['updatemsg'][rex_asd_news_config::getName()] = '';

// Check if in AddonData the config file exists
rex_asd_news_config::createDataConfigIfNotExists();

// Update Config
rex_asd_news_config::saveConfig();

// Update 1.4

// check if old fields exists
$sql = new rex_sql();
$rows = $sql->showColumns(rex_asd_news_config::getTable());
$metaCols = false;
foreach ($rows as $row) {
    if ($row['name'] == 'category') {
        $metaCols = true;
        break;
    }
}

if(!OOAddon::isAvailable('metainfo')) {
    $REX['ADDON']['update'][rex_asd_news_config::getName()] = 0;
    $REX['ADDON']['updatemsg'][rex_asd_news_config::getName()] = 'Metainfo Addon nicht gefunden';
}

if (OOAddon::isAvailable('metainfo') && $metaCols) {

    include_once rex_path::addon(rex_asd_news_config::getName(), 'classes/metainfo/rex_asd_metainfo_install.php');

    rex_asd_metainfo_install::setProperty();
    if ($error = rex_asd_metainfo_install::addFields()) {
        $REX['ADDON']['update'][rex_asd_news_config::getName()] = 0;
        $REX['ADDON']['updatemsg'][rex_asd_news_config::getName()] .= $error;
    } else {
        // Einträge Übernehmen
        $sql->setQuery('UPDATE `' . rex_asd_news_config::getTable() . '` SET
        `asd_category` = `category`,
        `asd_picture` = `picture`,
        `asd_text` = `text`');

        // Alte Felder löschen
        $sql->setQuery('ALTER TABLE `' . rex_asd_news_config::getTable() . '`
            DROP `category`,
            DROP `picture`,
            DROP `text`
        ');
    }

}
?>