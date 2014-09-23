<?php

/** @var i18n $I18N */

if(!$REX['USER']->hasPerm('asd_news[settings]') && !$REX['USER']->isAdmin()) {
    echo rex_warning($I18N->msg('asd_news_no_access'));
    exit();
}

define('ASD_NEWS_MODUL_1', 'ASD News - Kategorieauswahl');

$func = rex_request('func', 'string');

$config = $REX['ADDON']['asd_news']['config'];

if (!function_exists('asd_filterPosts')) {

    function asd_filterPosts(array $names)
    {

        $return = array();
        foreach ($names as $key => $cast) {
            $return[$key] = rex_request($key, $cast);
        }

        return $return;
    }
}

if ($func == 'update') {

    $sendit = rex_request('sendit');
    $installModul = rex_request('modul');

    $saves = asd_filterPosts(array(
        'max-per-page' => 'int',
        'published-lang' => 'string',
        'include-css' => 'string'
    ));

    if ($saves['max-per-page'] < 1 || $saves['max-per-page'] > 50) {
        $saves['max-per-page'] = 50;
    }

    if ($sendit) {

        $config = array_merge($config, $saves);

        file_put_contents($REX['ADDON']['asd_news']['configFile'], json_encode($saves));

    }

    if ($installModul) {

        $eingabe = rex_asd_news_utils::getModulCode('modulEingabe.php');
        $ausgabe = rex_asd_news_utils::getModulCode('modulAusgabe.php');

        /** @var rex_sql $modul */
        $modul = rex_sql::factory();
        $modul->setTable($REX['TABLE_PREFIX'] . 'module');
        $modul->setValue('name', ASD_NEWS_MODUL_1);
        $modul->setValue('eingabe', $modul->escape($eingabe));
        $modul->setValue('ausgabe', $modul->escape($ausgabe));
        $modul->addGlobalCreateFields();

        if ($modul->insert()) {
            echo rex_info($I18N->msg('asd_news_modul_added'));
        } else {
            echo rex_warning($modul->getError());
        }

        $func = '';


    }

}

$sql = new rex_sql();
$sql->setQuery('SELECT id FROM `' . $REX['TABLE_PREFIX'] . 'module` WHERE `name` = "'.ASD_NEWS_MODUL_1.'"');

$disabledModul_1 =  ($sql->getRows()) ? ' disabled="disabled' : '';
?>
<div class="rex-addon-output">
    <div class="rex-form">
        <form action="index.php" method="post">
            <input type="hidden" name="page" value="asd_news"/>
            <input type="hidden" name="subpage" value="<?php echo $subpage; ?>"/>
            <input type="hidden" name="func" value="update"/>
            <fieldset class="rex-form-col-1">
                <legend><?php echo $I18N->msg('asd_news_settings_global'); ?></legend>
                <div class="rex-form-wrapper">
                    <div class="rex-form-row">
                        <p class="rex-form-text">
                            <label><?php echo $I18N->msg('asd_news_settings_max_per_page'); ?></label>
                            <input class="rex-form-text" type="number" name="max-per-page" min="1" max="50"
                                   value="<?php echo $config['max-per-page'] ?>">
                        </p>
                    </div>
                </div>
                <legend><?php echo $I18N->msg('asd_news_include_css'); ?></legend>
                <div class="rex-form-wrapper">
                    <?php

                    foreach (array(
                                 'false' => $I18N->msg('no'),
                                 'true' => $I18N->msg('yes')
                             ) as $value => $description) {

                        $checked = ($value == $config['include-css']) ? ' checked="checked"' : '';

                        ?>
                        <div class="rex-form-row">
                            <p class="rex-form-radio rex-form-label-right">
                                <input class="rex-form-radio" type="radio" name="include-css"
                                       value="<?php echo $value ?>"<?php echo $checked ?>>
                                <label><?php echo $description ?></label>
                            </p>
                        </div>
                    <?php
                    }
                    ?>
            </fieldset>
            <fieldset class="rex-form-col-1">
                <legend><?php echo $I18N->msg('asd_news_settings_published_by'); ?></legend>
                <div class="rex-form-wrapper">
                    <?php

                    foreach (array(
                                 'single' => $I18N->msg('asd_news_current_lang'),
                                 'all' => $I18N->msg('asd_news_all_lang')
                             ) as $value => $description) {

                        $checked = ($value == $config['published-lang']) ? ' checked="checked"' : '';

                        ?>
                        <div class="rex-form-row">
                            <p class="rex-form-radio rex-form-label-right">
                                <input class="rex-form-radio" type="radio" name="published-lang"
                                       value="<?php echo $value ?>"<?php echo $checked ?>>
                                <label><?php echo $description ?></label>
                            </p>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </fieldset>
            <fieldset class="rex-form-col-1">
                <legend><?php echo $I18N->msg('modules'); ?></legend>
                <div class="rex-form-wrapper">
                    <div class="rex-form-row">
                        <p class="rex-form-submit rex-form-submit-2">
                            <input class="rex-form-submit" type="submit" id="modul" name="modul"
                                   value="<?php echo $I18N->msg('asd_news_install_modul'); ?>"<?php echo $disabledModul_1 ?>/>
                        </p>
                    </div>
                </div>
            </fieldset>
            <fieldset class="rex-form-raw">
                <legend></legend>
                <div class="rex-form-wrapper">
                    <div class="rex-form-row">
                        <p class="rex-form-submit rex-form-submit-2">

                            <input class="rex-form-submit" type="submit" id="sendit" name="sendit"
                                   value="<?php echo $I18N->msg('submit'); ?>"/>
                        </p>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>