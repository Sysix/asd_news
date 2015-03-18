<?php
/** @var i18n $I18N */

$id = rex_request('id', 'int', 0);

if($func == 'delete') {

    $sql = new rex_sql();
    $sql->setTable(rex_asd_news_config::getTableCategory());
    $sql->setWhere('id='.$id);

    if($sql->delete()) {
        echo rex_info($I18N->msg('asd_news_rubric_saved'));
    } else {
        echo rex_warning($sql->getErrno());
    }

    $func = '';
}

if($func == '') {

    $list = new rex_list('SELECT * FROM `' . rex_asd_news_config::getTableCategory() . '`');

    $list->addTableColumnGroup(array(40, '*', 80, 80));

    $imgHeader = '
    <a class="rex-i-element rex-i-generic-add" href="'. $list->getUrl(array('func' => 'add')) .'">
        <span class="rex-i-element-text">'.$I18N->msg('asd_news_rubric_add').'</span>
    </a>';

    $list->setColumnLabel('id', $imgHeader);
    $list->setColumnLabel('name', $I18N->msg('name'));

    $list->addColumn('editCol', $I18N->msg('edit'), -1, array('<th colspan="2">'.$I18N->msg('actions').'</th>', '<td>###VALUE###</td>'));
    $list->setColumnParams('editCol', array('func'=>'edit', 'id' => '###id###'));

    $list->addColumn('delCol', $I18N->msg('delete'), -1, array('', '<td>###VALUE###</td>'));
    $list->setColumnParams('delCol', array('func'=>'delete', 'id' => '###id###'));
    $list->addLinkAttribute('delCol', 'onclick', 'return confirm(\''.$I18N->msg('asd_news_rubric_really_delete').'\');');

    $list->show();

}

if($func == 'add' || $func == 'edit') {

    $title = ($func == 'add') ? $I18N->msg('add') : $I18N->msg('edit');

    $form = new rex_form(rex_asd_news_config::getTableCategory(), ucfirst($title), 'id='.$id);

    $field = $form->addTextField('name');
    $field->setLabel($I18N->msg('name'));

    if($func == 'edit') {
        $form->addParam('id', $id);
    }

    $form->show();

}

?>