<?php

$func = rex_request('func', 'string');

$config = $REX['ADDON']['asd_news']['config'];

if(!function_exists('asd_filterPosts')) {

    function asd_filterPosts(array $names) {

        $return = array();
        foreach($names as $key => $cast) {
            $return[$key] = rex_request($key, $cast);
        }

        return $return;
    }
}

if($func == 'update') {

    $saves = asd_filterPosts(array(
        'max-per-page' => 'int',
        'published-lang' => 'string'
    ));

    if($saves['max-per-page'] < 1 ||$saves['max-per-page'] > 50) {
        $saves['max-per-page'] = 50;
    }

    $config = array_merge($config, $saves);

    file_put_contents($REX['ADDON']['asd_news']['configFile'], json_encode($saves));

}
?>
<div class="rex-addon-output">
    <div class="rex-form">
        <form action="index.php" method="post">
            <input type="hidden" name="page" value="asd_news"/>
            <input type="hidden" name="subpage" value="<?php echo $subpage; ?>"/>
            <input type="hidden" name="func" value="update"/>
            <fieldset class="rex-form-col-1">
                <legend>Allgemeine Einstellungen</legend>
                <div class="rex-form-wrapper">
                    <div class="rex-form-row">
                        <p class="rex-form-text">
                            <label>Maximale Anzahl der News</label>
                            <input class="rex-form-text" type="number" name="max-per-page" min="1" max="50" value="<?php echo $config['max-per-page'] ?>">
                        </p>
                    </div>
                </div>
            </fieldset>
            <fieldset class="rex-form-col-1">
                <legend>Veröffentlichen der News bei</legend>
                <div class="rex-form-wrapper">
                    <?php

                    foreach(array(
                        'single' => 'der ausgewählten Sprache',
                        'all' => 'für alle Sprachen'
                    ) as $value => $description) {

                        $checked = ($value == $config['published-lang']) ? ' checked="checked"' : '';

                    ?>
                    <div class="rex-form-row">
                        <p class="rex-form-radio rex-form-label-right">
                            <input class="rex-form-radio" type="radio" name="published-lang" value="<?php echo $value ?>"<?php echo $checked ?>>
                            <label><?php echo $description ?></label>
                        </p>
                    </div>
                    <?php
                    }
                    ?>
                </div>
            </fieldset>
            <fieldset class="rex-form-col-1">
                <div class="rex-form-wrapper">
                    <div class="rex-form-row rex-form-element-v2">
                        <p class="rex-form-submit">
                            <input class="rex-form-submit" type="submit" id="sendit" name="sendit"
                                   value="<?php echo $I18N->msg('submit'); ?>"/>
                        </p>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>