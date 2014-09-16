<?php

$id = rex_request('id', 'int', 0);
$clang = rex_request('clang', 'int', 0);
$sort = rex_request('sort', 'string', 'desc');

$now = new DateTime();

rex_asd_news_language($clang, '');

if($func == 'status') {

    $sql = new rex_sql();
    $sql->setTable($REX['TABLE_PREFIX'] . 'asd_news');
    $sql->setWhere('id='.$id.' AND clang = '.$clang);
    $sql->select('`status`');

    $status = ($sql->getValue('status')) ? 0 : 1;

    $sql->setTable($REX['TABLE_PREFIX'] . 'asd_news');
    $sql->setWhere('id='.$id.' AND clang = '.$clang);
    $sql->setValue('status', $status);

    if($sql->update()) {
        echo rex_info('Status erfolgreich geändert');
    } else {
        echo rex_warning($sql->getError());
    }

    $func = '';

}

if($func == 'delete') {

    $sql = new rex_sql();
    $sql->setTable($REX['TABLE_PREFIX'] . 'asd_news');
    $sql->setWhere('id='.$id.' AND clang = '.$clang);

    if($sql->delete()) {
        echo rex_info('Datensatz erfolgreich gelöscht');
    } else {
        echo rex_warning($sql->getError());
    }

    $func = '';

}

if($func == '') {

    $list = new rex_list('
    SELECT
      `id`, `title`, `publishedAt`, `status`
    FROM `' . $REX['TABLE_PREFIX'] .'asd_news`
    WHERE `clang` = '.$clang.'
    ORDER BY `publishedAt`');

    $list->addParam('clang', $clang);
    $list->addParam('subpage', $subpage);

    $imgHeader = '
    <a class="rex-i-element rex-i-generic-add" href="'. $list->getUrl(array('func' => 'add')) .'">
        <span class="rex-i-element-text">add</span>
    </a>';

    $list->setColumnLabel('id', $imgHeader);
    $list->setColumnLabel('title', 'Titel');
    $list->setColumnLabel('publishedAt', 'Veröffentlicht am');
    $list->removeColumn('status');
    $list->addTableColumnGroup(array(40, '*', 140, 50, 50, 50));

    $list->setColumnSortable('publishedAt', $sort);
    $list->setColumnSortable('title', $sort);

    $list->setColumnFormat('publishedAt', 'custom', function($params) use($list, $now, $clang) {
        $publishedAt = new DateTime($list->getValue('publishedAt'));

        $value = $publishedAt->format('d.m.Y H:i');

        if($publishedAt->getTimestamp() == -62169987600) {
            $value = '<input type="button" class="submit datepicker" value="Veröffentlichen" data-id="###id###" data-clang="'.$clang.'" data-date="'.$now->format('d/m/Y H:i').'">';
        }

        return '<span>'.$value.'</span>';
    });

    $list->addColumn('editCol', 'Editieren', -1, array('<th colspan="3">Aktion</th>', '<td>###VALUE###</td>'));
    $list->setColumnParams('editCol', array('func'=>'edit', 'id' => '###id###'));

    $list->addColumn('delCol', 'Löschen', -1, array('', '<td>###VALUE###</td>'));
    $list->setColumnParams('delCol', array('func'=>'delete', 'id' => '###id###'));
    $list->addLinkAttribute('delCol', 'onclick', 'return confirm(\'Wirklich löschen?\');');

    $list->addColumn('statusCol', 'offline', -1, array('', '<td>###VALUE###</td>'));
    $list->setColumnFormat('statusCol', 'custom', function($params) use($list) {

        $url = $list->getParsedUrl(array('func' => 'status', 'id' => '###id###'));

        if($list->getValue('status') == 1) {
            return '<a href="'.$url.'" class="rex-online">online</a>';
        } else {
            return '<a href="'.$url.'" class="rex-offline">offline</a>';
        }

    });

    $list->show();
    echo '
    <script>
    jQuery(document).ready(function($) {
        $(".datepicker").datetimepicker({
	        minDate: new Date('.$now->format('Y, m, d, H, i').'),
	        onClose: function(dateText, inst) {
	          $.post("index.php", {
                page: "asd_news",
                func: "publish",
                id: inst.input.data("id"),
                clang: inst.input.data("clang"),
                time: dateText
	          }, function(data) {
	             inst.input.parent().html(data);
	          });
	        },
	        onFocus: function(inst) {
	            console.log("hallo");
	        }
          });
        });
    </script>';

}


if($func == 'add' || $func == 'edit') {

    $title = ($func == 'add') ? 'Hinzufügen' : 'Editieren';

    $form = new rex_news_form($REX['TABLE_PREFIX'] . 'asd_news', $title, 'id='.$id.' AND clang = '.$clang);

    $field = $form->addTextField('title');
    $field->setLabel('Titel');

    $field = $form->addSelectField('category');
    $field->setLabel('Kategorie');

    $select = $field->getSelect();
    $select->addSqlOptions('SELECT `name`, `id` FROM ' . $REX['TABLE_PREFIX'] . 'asd_news_category');
    $select->setSize(1);

    $field = $form->addMediaField('picture');
    $field->setLabel('Bild');

    $field = $form->addTextAreaField('text');
    $field->setLabel('Text');
    $field->setAttribute('class', 'tinyMCEEditor');

    $form->addHiddenField('clang', (int)$clang);
    $form->addHiddenField('updatedAt', $now->format('Y-m-d H:i:s'));
    $form->addHiddenField('updatedBy', $REX['USER']->getValue('user_id'));

    $form->addParam('clang', (int)$clang);
    $form->addParam('id', (int)$id);

    if($func == 'add') {
        $id = rex_asd_news::getLastNewsId() + 1;
    }

    $form->addHiddenField('id', $id);

    if($func == 'add') {

        $form->addHiddenField('createdAt', $now->format('Y-m-d H:i:s'));
        $form->addHiddenField('createdBy', $REX['USER']->getValue('user_id'));

        rex_register_extension('REX_FORM_SAVED', function($subject) use($clang, $REX, $id) {

            $sql = $subject['sql'];
            $form = $subject['form'];

            $lang = new rex_sql();
            $lang->setQuery('SELECT `id` FROM `' . $REX['TABLE_PREFIX'] . 'clang` WHERE `id` != '.$clang);
            for($i = 1; $i <= $lang->getRows(); $i++) {

                $sql->setTable($form->getTableName());
                $sql->setValues($form->getValues());
                $sql->setValue('clang', (int)$lang->getValue('id'));
                $sql->setValue('id', $id);
                $sql->insert();

                $lang->next();
            }

        });

    }

    $form->show();

}


?>