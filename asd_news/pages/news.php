<?php
/** @var i18n $I18N */

$id = rex_request('id', 'int', 0);
$clang = rex_request('clang', 'int', 0);
$sort = rex_request('sort', 'string', 'desc');

$now = new DateTime();

rex_asd_news_language($clang, '');

if ($func == 'status') {

    $sql = new rex_sql();
    $sql->setTable($REX['TABLE_PREFIX'] . 'asd_news');
    $sql->setWhere('id=' . $id . ' AND clang = ' . $clang);
    $sql->select('`status`');

    $status = ($sql->getValue('status')) ? 0 : 1;

    $sql->setTable($REX['TABLE_PREFIX'] . 'asd_news');
    $sql->setWhere('id=' . $id . ' AND clang = ' . $clang);
    $sql->setValue('status', $status);

    if ($sql->update()) {
        echo rex_info($I18N->msg('asd_news_status_saved'));
    } else {
        echo rex_warning($sql->getError());
    }

    $func = '';

}

if ($func == 'delete') {

    $sql = new rex_sql();
    $sql->setTable($REX['TABLE_PREFIX'] . 'asd_news');
    $sql->setWhere('id=' . $id . ' AND clang = ' . $clang);

    if ($sql->delete()) {
        echo rex_info($I18N->msg('asd_news_deleted'));
    } else {
        echo rex_warning($sql->getError());
    }

    $func = '';

}

if ($func == 'unpublish') {

    $sql = new rex_sql();
    $sql->setTable($REX['TABLE_PREFIX'] . 'asd_news');
    $sql->setWhere('id=' . $id . ' AND clang = ' . $clang);
    $sql->setValue('publishedAt', '0000-00-00 00:00:00');
    $sql->setValue('publishedBy', 0);

    $successMessage = $I18N->msg('asd_news_unpublished_s');

    if ($REX['ADDON']['asd_news']['config']['published-lang'] == 'all') {
        $sql->setWhere('`id` = ' . $id);
        $successMessage = $I18N->msg('asd_news_unpublished_m');
    }

    if ($sql->update()) {
        echo rex_info($successMessage);
    } else {
        echo rex_warning($sql->getError());
    }

    $func = '';

}

if ($func == '') {

    $list = new rex_list('
    SELECT
      `id`, `title`, `publishedAt`, `status`
    FROM `' . $REX['TABLE_PREFIX'] . 'asd_news`
    WHERE `clang` = ' . $clang . '
    ORDER BY `publishedAt`');

    $list->addParam('clang', $clang);
    $list->addParam('subpage', $subpage);

    $imgHeader = '
    <a class="rex-i-element rex-i-generic-add" href="' . $list->getUrl(array('func' => 'add')) . '">
        <span class="rex-i-element-text">add</span>
    </a>';

    $list->setColumnLabel('id', $imgHeader);
    $list->setColumnLabel('title', $I18N->msg('asd_news_title'));
    $list->setColumnLabel('publishedAt', $I18N->msg('asd_news_publishedAt'));
    $list->removeColumn('status');
    $list->addTableColumnGroup(array(40, '*', 140, 50, 50, 50));

    $list->setColumnSortable('publishedAt', $sort);
    $list->setColumnSortable('title', $sort);

    $list->setColumnFormat('publishedAt', 'custom', function ($params) use ($list, $now, $clang, $I18N, $REX) {
        $publishedAt = new DateTime($list->getValue('publishedAt'));

        $value = $publishedAt->format('d.m.Y H:i');

        $value .= '
        <a href="' . $list->getParsedUrl(array('func' => 'unpublish', 'id' => '###id###')) . '">
            <img src="../' . $REX['MEDIA_ADDON_DIR'] . '/asd_news/unpublished.svg"
            width="20" height="20" style="vertical-align: middle; margin-left: 5px"
            onclick="return confirm(\''.$I18N->msg('asd_news_really_unpublish').'\');">
        </a>';

        if ($publishedAt->getTimestamp() == -62169987600 || $publishedAt->getTimestamp() === false) {
            $value = '<input type="button" class="submit datepicker" value="' . $I18N->msg('asd_news_publish') . '" data-id="###id###" data-clang="' . $clang . '" data-date="' . $now->format('d/m/Y H:i') . '">';
        }

        return '<span>' . $value . '</span>';
    });

    $list->addColumn('editCol', $I18N->msg('edit'), -1, array('<th colspan="3">' . $I18N->msg('actions') . '</th>', '<td>###VALUE###</td>'));
    $list->setColumnParams('editCol', array('func' => 'edit', 'id' => '###id###'));

    $list->addColumn('delCol', $I18N->msg('delete'), -1, array('', '<td>###VALUE###</td>'));
    $list->setColumnParams('delCol', array('func' => 'delete', 'id' => '###id###'));
    $list->addLinkAttribute('delCol', 'onclick', 'return confirm(\'' . $I18N->msg('asd_news_really_delete') . '\');');

    $list->addColumn('statusCol', 'offline', -1, array('', '<td>###VALUE###</td>'));
    $list->setColumnFormat('statusCol', 'custom', function ($params) use ($list, $I18N) {

        $url = $list->getParsedUrl(array('func' => 'status', 'id' => '###id###'));

        if ($list->getValue('status') == 1) {
            return '<a href="' . $url . '" class="rex-online">' . $I18N->msg('status_online') . '</a>';
        } else {
            return '<a href="' . $url . '" class="rex-offline">' . $I18N->msg('status_offline') . '</a>';
        }

    });

    /* Bugfix Datetimepicker */
    $now = $now->sub(new DateInterval(('P1M')));

    $list->show();
    echo '
    <script>
    jQuery(document).ready(function($) {

        $(".datepicker").each(function() {

            text = $(this).val();

            $(this).datetimepicker({
                minDateTime: new Date(' . $now->format('Y, m, d, H, i') . '),
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
                  $(this).val(text);
                },
                onFocus: function() {
                  $(this).val($(this).data("date"))
                }
            });


        });

    });

    </script>';

}


if ($func == 'add' || $func == 'edit') {

    if(rex_asd_news::$SEO_URL_CONTROL) {
        foreach(array('REX_FORM_SAVED', 'REX_FORM_DELETED') as $extension) {
            rex_register_extension($extension, 'url_generate::generatePathFile');
        }
    }

    $title = ($func == 'add') ? $I18N->msg('add') : $I18N->msg('edit');

    $form = new rex_news_form($REX['TABLE_PREFIX'] . 'asd_news', ucfirst($title), 'id=' . $id . ' AND clang = ' . $clang);

    $field = $form->addTextField('title');
    $field->setLabel($I18N->msg('asd_news_title'));

    $field = $form->addSelectField('category');
    $field->setLabel($I18N->msg('content_category'));

    $select = $field->getSelect();
    $select->addSqlOptions('SELECT `name`, `id` FROM ' . $REX['TABLE_PREFIX'] . 'asd_news_category');
    $select->setSize(1);

    $field = $form->addMediaField('picture');
    $field->setLabel($I18N->msg('asd_news_picture'));

    $field = $form->addTextAreaField('text');
    $field->setLabel($I18N->msg('asd_news_text'));
    $field->setAttribute('class', 'tinyMCEEditor');

    $form->addHiddenField('clang', (int)$clang);
    $form->addHiddenField('updatedAt', $now->format('Y-m-d H:i:s'));
    $form->addHiddenField('updatedBy', $REX['USER']->getValue('user_id'));

    $form->addParam('clang', (int)$clang);
    $form->addParam('id', (int)$id);

    if ($func == 'add') {
        $id = rex_asd_news::getLastNewsId() + 1;
    }

    $form->addHiddenField('id', $id);

    if ($func == 'add') {

        $form->addHiddenField('createdAt', $now->format('Y-m-d H:i:s'));
        $form->addHiddenField('createdBy', $REX['USER']->getValue('user_id'));

        rex_register_extension('REX_FORM_SAVED', function ($subject) use ($clang, $REX, $id) {

            $sql = $subject['sql'];
            $form = $subject['form'];

            $lang = new rex_sql();
            $lang->setQuery('SELECT `id` FROM `' . $REX['TABLE_PREFIX'] . 'clang` WHERE `id` != ' . $clang);
            for ($i = 1; $i <= $lang->getRows(); $i++) {

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