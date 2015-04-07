<?php

function rex_asd_news_language($curClang, $urlParam)
{
    global $REX;
    global $I18N;

    reset($REX['CLANG']);
    $num_clang = count($REX['CLANG']);

    if ($num_clang > 1) {
        echo '
<div id="rex-clang" class="rex-toolbar">
    <div class="rex-toolbar-content">
        <ul>
            <li>' . $I18N->msg('languages') . ' : </li>';

        $stop = false;
        $i = 1;
        foreach ($REX['CLANG'] as $key => $val) {
            if ($i == 1) {
                echo '<li class="rex-navi-first rex-navi-clang-' . $key . '">';
            } else {
                echo '<li class="rex-navi-clang-' . $key . '">';
            }

            $val = rex_translate($val);

            if (!$REX['USER']->isAdmin() && !$REX['USER']->hasPerm('clang[all]') && !$REX['USER']->hasPerm('clang[' . $key . ']')) {
                echo '<span class="rex-strike">' . $val . '</span>';

                if ($curClang == $key) {
                    $stop = true;
                }
            } else {
                $class = '';
                if ($key == $curClang) {
                    $class = ' class="rex-active"';
                }
                echo '<a' . $class . ' href="index.php?page=' . $REX['PAGE'] . '&amp;clang=' . $key . $urlParam . '"' . rex_tabindex() . '>' . $val . '</a>';
            }

            echo '</li>';
            $i++;
        }

        echo '
        </ul>
    </div>
</div>';

        if ($stop) {
            echo rex_warning('You have no permission to this area');
            require $REX['INCLUDE_PATH'] . '/layout/bottom.php';
            exit;
        }
    }

}

function asd_news_deleteClang($params)
{

    $id = $params['id'];
    $name = $params['name'];

    $sql = new rex_sql();
    $sql->setTable(rex_asd_news_config::getTable());
    $sql->setWhere('`clang`= ' . $id);

    if ($sql->delete()) {
        echo rex_info('ASD News: Neuigkeiten wurden gelöscht');
    } else {
        echo rex_warning('<b>ASD News Fehler:</b> Neuigkeiten aus der Sprache "' . $name . '" konnte nicht gelöscht werden');
    }
}

function asd_news_addClang($params)
{
    global $REX;

    $id = $params['id'];
    $name = $params['name'];

    $now = new DateTime();
    $error = false;

    $sql = new rex_sql();
    $sql->setQuery('SELECT * FROM `' . rex_asd_news_config::getTable() . '` WHERE `clang` = ' . $REX['START_CLANG_ID']);
    for ($i = 1; $i <= $sql->getRows(); $i++) {

        $save = new rex_sql();
        $save->setTable(rex_asd_news_config::getTable());
        $save->setValues($sql->getRow());
        $save->setValue('clang', $id);
        $save->setValue('createdAt', $now->format('Y-m-d H:i:s'));
        $save->setValue('updatedAt', $now->format('Y-m-d H:i:s'));
        $save->setValue('publishedAt', '0000-00-00 00:00:00');
        $save->setValue('createdBy', $REX['USER']->getValue('user_id'));
        $save->setValue('updatedBy', $REX['USER']->getValue('user_id'));
        $save->setValue('publishedBy', 0);
        $save->setValue('status', 0);

        unset($save->values['news_id']);

        if (!$save->insert()) {
            $error = $save->getError();
        }

    }

    if ($error) {
        echo rex_warning('ASD News: Neuigkeiten in der Sprache "' . $name . '" konnten nicht angelegt werden<br />' . $error);
    } else {
        echo rex_info('ASD News: Neuigkeiten in der Sprache "' . $name . '" wurden angelegt');
    }
}

?>