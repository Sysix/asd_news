<?php

$id = rex_request('id', 'int', 0);

if($func == 'delete') {

    $sql = new rex_sql();
    $sql->setTable($REX['TABLE_PREFIX'] . 'asd_news_category');
    $sql->setWhere('id='.$id);

    if($sql->delete()) {
        echo rex_info('Datensatz erfolgreich gelöscht');
    } else {
        echo rex_warning($sql->getErrno());
    }

    $func = '';
}

if($func == '') {

    $list = new rex_list('SELECT * FROM `' . $REX['TABLE_PREFIX'] .'asd_news_category`');

    $list->addTableColumnGroup(array(40, '*', 80, 80));

    $imgHeader = '
    <a class="rex-i-element rex-i-generic-add" href="'. $list->getUrl(array('func' => 'add')) .'">
        <span class="rex-i-element-text">add</span>
    </a>';

    $list->setColumnLabel('id', $imgHeader);
    $list->setColumnLabel('name', 'Name');

    $list->addColumn('editCol', 'Editieren', -1, array('<th colspan="2">Aktion</th>', '<td>###VALUE###</td>'));
    $list->setColumnParams('editCol', array('func'=>'edit', 'id' => '###id###'));

    $list->addColumn('delCol', 'Löschen', -1, array('', '<td>###VALUE###</td>'));
    $list->setColumnParams('delCol', array('func'=>'delete', 'id' => '###id###'));
    $list->addLinkAttribute('delCol', 'onclick', 'return confirm(\'Wirklich löschen?\');');

    $list->show();

}

if($func == 'add' || $func == 'edit') {
    $title = ($func == 'add') ? 'Hinzufügen' : 'Editieren';

    $form = new rex_form($REX['TABLE_PREFIX'] . 'asd_news_category', $title, 'id='.$id, 'post', true);

    $field = $form->addTextField('name');
    $field->setLabel('Name');

    $form->show();

}

?>