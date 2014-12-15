<?

global $REX;

if(!file_exists(rex_path::addonData('asd_news', 'config.json'))) {
    rex_dir::copy(
        rex_path::addon('asd_news', 'data'),
        rex_path::addonData('asd_news')
    );
}

$REX['ADDON']['update']['asd_news'] = 1;

$configFile = rex_path::addonData('asd_news', 'config.json');
$config = json_decode(file_get_contents($configFile), true);

unset($config['include-css']);

if(!isset($config['min-archive'])) {
    $config['min-archive'] = 15;
    file_put_contents($configFile,  json_encode($config));
}

if(!isset($config['pagination'])) {
    $config['pagination'] = 'pager';
    file_put_contents($configFile,  json_encode($config));
}

if(!isset($config['pagination-css-id'])) {
    $config['pagination-css-id'] = 'asd-pagination';
    file_put_contents($configFile,  json_encode($config));
}

if(!isset($config['pager-css-id'])) {
    $config['pager-css-id'] = 'asd-pager';
    file_put_contents($configFile,  json_encode($config));
}

// Update 1.4

// check if Metafields exists, example asd_title
$sql = new rex_sql();
$rows = $sql->showColumns($REX['TABLE_PREFIX'] . 'asd_news');
$metaCols = false;
foreach($rows as $row) {
    if($row['name'] == 'asd_category') {
        $metaCols = true;
        break;
    }
}

if(OOAddon::isAvailable('metainfo') && !$metaCols) {

    include_once rex_path::addon('asd_news', 'classes/metainfo/rex_asd_metainfo_install.php');

    rex_asd_metainfo_install::setProperty();
    if($error = rex_asd_metainfo_install::addFields()) {
        $REX['ADDON']['install']['asd_news'] = 0;
        $REX['ADDON']['updatemsg']['asd_news'] .= $error;
    }
    // Einträge Übernehmen
    $sql->setQuery('UPDATE `'. $REX['TABLE_PREFIX'] . 'asd_news` SET
        `asd_category` = `category`,
        `asd_picture` = `picture`,
        `asd_text` = `text`');

    // Alte Felder löschen
    $sql->setQuery('ALTER TABLE `'. $REX['TABLE_PREFIX'] . 'asd_news`
        DROP `category`,
        DROP `picture`,
        DROP `text`
    ');

}
?>