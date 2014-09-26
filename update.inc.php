<?php

if(!file_exists(rex_path::addonData('asd_news', 'config.json'))) {
    rex_dir::copy(
        rex_path::addon('asd_news', 'data'),
        rex_path::addonData('asd_news')
    );
}

$REX['ADDON']['update']['asd_news'] = 1;

$configFile = rex_path::addonData('asd_news', 'config.json');
$config = json_decode(file_get_contents($configFile), true);

if(!isset($config['min-archive'])) {
    $config['min-archive'] = 15;
    file_put_contents($configFile,  json_encode($config));
}

?>