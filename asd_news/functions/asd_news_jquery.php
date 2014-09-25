<?php

function asd_news_setjQueryTags($params) {
    global $REX;

    $myAddon = 'asd_news';

    $insert = '<!-- ASD News Addon - BEGIN -->';
    $insert .= '<link href="../'.$REX['MEDIA_ADDON_DIR'].'/'.$myAddon.'/jquery-ui.min.css" rel="stylesheet">';
    $insert .= '<script src="../'.$REX['MEDIA_ADDON_DIR'].'/'.$myAddon.'/jquery-ui.min.js"></script>';
    $insert .= '<script src="../'.$REX['MEDIA_ADDON_DIR'].'/'.$myAddon.'/jquery-ui-timepicker.js"></script>';
    $insert .= '<!-- ASD News Addon - END -->';

    return $params['subject'].PHP_EOL.$insert;

}

?>