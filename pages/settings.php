<?php

/** @var i18n $I18N */

if (!$REX['USER']->hasPerm('asd_news[settings]') && !$REX['USER']->isAdmin()) {
    echo rex_warning($I18N->msg('asd_news_no_access'));
    exit();
}

define('ASD_NEWS_MODUL_1', 'ASD News - Kategorieauswahl');
define('ASD_NEWS_MODUL_2', 'ASD News - Archiv');
define('ASD_NEWS_MODUL_3', 'ASD News - Alle News');

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

    $installModul_1 = rex_request('modul_1');
    $installModul_2 = rex_request('modul_2');
    $installModul_3 = rex_request('modul_3');

    $saves = asd_filterPosts(array(
        'max-per-page' => 'int',
        'min-archive' => 'int',
        'published-lang' => 'string',
        'pagination' => 'string',
        'pagination-css-id' => 'string',
        'pager-css-id' => 'string',
        'article' => 'int'
    ));

    if ($saves['max-per-page'] < 1 || $saves['max-per-page'] > 50) {
        $saves['max-per-page'] = 50;
    }

    if ($saves['min-archive'] < 5 || $saves['min-archive'] > 9999) {
        $saves['min-archive'] = 15;
    }

    // set min 1 page full with news
    if ($saves['max-per-page'] > $saves['min-archive']) {
        $saves['min-archive'] = $saves['max-per-page'];
    }

    // Fix ID Anker
    $saves['pagination-css-id'] = str_replace('#', '', $saves['pagination-css-id']);
    $saves['pager-css-id'] = str_replace('#', '', $saves['pager-css-id']);

    if ($sendit) {

        $config = array_merge($config, $saves);

        if (file_put_contents($REX['ADDON']['asd_news']['configFile'], json_encode($config))) {
            echo rex_info($I18N->msg('asd_news_settings_saved'));
            url_generate::generatePathFile('');
        } else {
            echo rex_warning($I18N->msg('asd_news_settings_not_saved'));
        }
    }

    if ($installModul_1 || $installModul_2 || $installModul_3) {

        if ($installModul_1) {
            $eingabe = rex_asd_news_utils::getModulCode('modulEingabe_1.php');
            $ausgabe = rex_asd_news_utils::getModulCode('modulAusgabe_1.php');
            $name = ASD_NEWS_MODUL_1;
        }

        if ($installModul_2) {
            $eingabe = rex_asd_news_utils::getModulCode('modulEingabe_2.php');
            $ausgabe = rex_asd_news_utils::getModulCode('modulAusgabe_2.php');
            $name = ASD_NEWS_MODUL_2;
        }

        if ($installModul_3) {
            $eingabe = rex_asd_news_utils::getModulCode('modulEingabe_3.php');
            $ausgabe = rex_asd_news_utils::getModulCode('modulAusgabe_3.php');
            $name = ASD_NEWS_MODUL_3;
        }

        /** @var rex_sql $modul */
        $modul = rex_sql::factory();
        $modul->setTable($REX['TABLE_PREFIX'] . 'module');
        $modul->setValue('name', $name);
        $modul->setValue('eingabe', $modul->escape($eingabe));
        $modul->setValue('ausgabe', $modul->escape($ausgabe));
        $modul->addGlobalCreateFields();

        if ($modul->insert()) {
            echo rex_info($I18N->msg('asd_news_modul_added'));
        } else {
            echo rex_warning($modul->getError());
        }

    }

    $func = '';

}

$sql = new rex_sql();
$sql->setQuery('SELECT id FROM `' . $REX['TABLE_PREFIX'] . 'module` WHERE `name` = "' . ASD_NEWS_MODUL_1 . '"');
$disabledModul_1 = ($sql->getRows()) ? ' disabled="disabled"' : '';

$sql = new rex_sql();
$sql->setQuery('SELECT id FROM `' . $REX['TABLE_PREFIX'] . 'module` WHERE `name` = "' . ASD_NEWS_MODUL_2 . '"');
$disabledModul_2 = ($sql->getRows()) ? ' disabled="disabled"' : '';

$sql = new rex_sql();
$sql->setQuery('SELECT id FROM `' . $REX['TABLE_PREFIX'] . 'module` WHERE `name` = "' . ASD_NEWS_MODUL_3 . '"');
$disabledModul_3 = ($sql->getRows()) ? ' disabled="disabled"' : '';

?>
<style>
    .asd-modul-buttons input {
        margin: 4px 0;
    }
</style>
<script>
    jQuery(document).ready(function ($) {

        function asd_liveUpdate(input, output) {
            input.on('keyup mouseup change keydown', function () {
                output.html(input.val());
            });
        }

        asd_liveUpdate($('#min-archive-input'), $('#asd_news_min_archive_text'));

    });
</script>
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

                    <div class="rex-form-row">
                        <p class="rex-form-text">
                            <label><?php echo $I18N->msg('asd_news_settings_min_archive', $config['min-archive']); ?></label>
                            <input class="rex-form-text" type="number" name="min-archive" min="5" max="9999"
                                   value="<?php echo $config['min-archive'] ?>" id="min-archive-input">
                        </p>
                    </div>

                    <div class="rex-form-row">
                        <p class="rex-form-text">
                            <label>Pagination</label>
                            <select name="pagination">
                                <?php

                                foreach (array(
                                             'site-number' => $I18N->msg('asd_news_site_numbers'),
                                             'pager' => $I18N->msg('asd_news_prev_next_buttons')) as $value => $desc) {

                                    $selected = ($value == $config['pagination']) ? ' selected="selected"' : '';
                                    echo '<option value="' . $value . '"' . $selected . '>' . $desc . '</option>';

                                }
                                ?>
                            </select>
                        </p>
                    </div>

                    <div class="rex-form-row">
                        <div class="rex-form-widget">
                            <label>News Artikel</label>
                            <?php echo rex_var_link::_getLinkButton('article', 1, $config['article']); ?>
                        </div>
                    </div>
                </div>
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
                            <p class="rex-form-submit rex-form-submit-2 asd-modul-buttons">
                                <input class="rex-form-submit" type="submit" name="modul_1"
                                       value='<?php echo $I18N->msg('asd_news_install_modul', ASD_NEWS_MODUL_1); ?>'<?php echo $disabledModul_1 ?>/>

                                <input class="rex-form-submit" type="submit" name="modul_2"
                                       value='<?php echo $I18N->msg('asd_news_install_modul', ASD_NEWS_MODUL_2); ?>'<?php echo $disabledModul_2 ?>/>

                                <input class="rex-form-submit" type="submit" name="modul_3"
                                       value='<?php echo $I18N->msg('asd_news_install_modul', ASD_NEWS_MODUL_3); ?>'<?php echo $disabledModul_3 ?>/>
                            </p>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="rex-form-raw">
                    <legend></legend>
                    <div class="rex-form-wrapper">
                        <div class="rex-form-row">
                            <span class="js-toggle-button" style="
                                margin:5px;
                                cursor:pointer;
                            "><span class="rex-i-element rex-i-generic-add" style="
                                display: inline-block;
                                vertical-align: middle;
                            "></span> erweiterte Optionen</span>

                            <div class="js-toggle-content">

                                <div class="rex-form-row">
                                    <p class="rex-form-text">
                                        <label>Pagination CSS ID</label>
                                        <input class="rex-form-text" type="text" name="pagination-css-id"
                                               value="<?php echo $config['pagination-css-id'] ?>">
                                    </p>
                                </div>

                                <div class="rex-form-row">
                                    <p class="rex-form-text">
                                        <label>Pager CSS ID</label>
                                        <input class="rex-form-text" type="text" name="pager-css-id"
                                               value="<?php echo $config['pager-css-id'] ?>">
                                    </p>
                                </div>

                            </div>
                        </div>
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

<script>
    jQuery(document).ready(function ($) {
        $('.js-toggle-content').hide();

        $('.js-toggle-button').click(function () {
            var div = $(this).next('.js-toggle-content');

            if (div.is(':hidden')) {
                div.fadeIn(200);
            } else {
                div.fadeOut(200);
            }
        });
    });
</script>