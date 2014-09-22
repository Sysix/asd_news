<?php

rex_dir::copy(
    rex_path::addon('asd_news', 'data'),
    rex_path::addonData('asd_news')
);

$REX['ADDON']['update']['asd_news'] = 1;
?>