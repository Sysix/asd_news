<?php

/**
 * MetaForm Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_asd_a62_tableExpander extends rex_a62_tableExpander
{
    var $metaPrefix;
    var $tableManager;

    /*public*/
    function rex_asd_a62_tableExpander($metaPrefix, $metaTable, $tableName, $fieldset, $whereCondition, $method = 'post', $debug = false)
    {
        parent::rex_a62_tableExpander($metaPrefix, $metaTable, $tableName, $fieldset, $whereCondition, $method, $debug);
    }

    /*public*/
    function init()
    {
        global $REX, $I18N;

        // ----- EXTENSION POINT
        // IDs aller Feldtypen bei denen das Parameter-Feld eingeblendet werden soll
        $typeFields = rex_register_extension_point('A62_TYPE_FIELDS', array(REX_A62_FIELD_SELECT, REX_A62_FIELD_RADIO, REX_A62_FIELD_CHECKBOX, REX_A62_FIELD_REX_MEDIA_BUTTON, REX_A62_FIELD_REX_MEDIALIST_BUTTON, REX_A62_FIELD_REX_LINK_BUTTON, REX_A62_FIELD_REX_LINKLIST_BUTTON));

        $field = &$this->addReadOnlyField('prefix', $this->metaPrefix);
        $field->setLabel($I18N->msg('minfo_field_label_prefix'));

        $field = &$this->addTextField('name');
        $field->setLabel($I18N->msg('minfo_field_label_name'));

        $field = &$this->addSelectField('prior');
        $field->setLabel($I18N->msg('minfo_field_label_prior'));
        $select = &$field->getSelect();
        $select->setSize(1);
        $select->addOption($I18N->msg('minfo_field_first_prior'), 1);
        // Im Edit Mode das Feld selbst nicht als Position einfügen
        $qry = 'SELECT name,prior FROM ' . $this->tableName . ' WHERE `name` LIKE "' . $this->metaPrefix . '%"';
        if ($this->isEditMode()) {
            $qry .= ' AND field_id != ' . $this->getParam('field_id');
        }
        $qry .= ' ORDER BY prior';
        $sql = rex_sql::factory();
        $sql->setQuery($qry);
        for ($i = 0; $i < $sql->getRows(); $i++) {
            $select->addOption(
                $I18N->msg('minfo_field_after_prior', $sql->getValue('name')),
                $sql->getValue('prior') + 1
            );
            $sql->next();
        }

        $field = &$this->addTextField('title');
        $field->setLabel($I18N->msg('minfo_field_label_title'));
        $field->setNotice($I18N->msg('minfo_field_notice_title'));

        $gq = new rex_sql;
        $gq->setQuery('SELECT dbtype,id FROM ' . $REX['TABLE_PREFIX'] . '62_type');
        $textFields = array();
        foreach ($gq->getArray() as $f) {
            if ($f['dbtype'] == 'text') {
                $textFields[$f['id']] = $f['id'];
            }
        }

        $field = &$this->addSelectField('type');
        $field->setLabel($I18N->msg('minfo_field_label_type'));
        $field->setAttribute('onchange', 'meta_checkConditionalFields(this, new Array(' . implode(',', $typeFields) . '), new Array(' . implode(',', $textFields) . '));');
        $select = &$field->getSelect();
        $select->setSize(1);

        $changeTypeFieldId = $field->getAttribute('id');

        $qry = 'SELECT label,id FROM ' . $REX['TABLE_PREFIX'] . '62_type';
        $select->addSqlOptions($qry);

        $notices = '';
        for ($i = 1; $i < REX_A62_FIELD_COUNT; $i++) {
            if ($I18N->hasMsg('minfo_field_params_notice_' . $i)) {
                $notices .= '<span class="rex-form-notice" id="a62_field_params_notice_' . $i . '" style="display:none">' . $I18N->msg('minfo_field_params_notice_' . $i) . '</span>' . "\n";
            }
        }
        $notices .= '
        <script type="text/javascript">
            var needle = new getObj("' . $field->getAttribute('id') . '");
            meta_checkConditionalFields(needle.obj, new Array(' . implode(',', $typeFields) . '), new Array(' . implode(',', $textFields) . '));
        </script>';

        $field = &$this->addTextAreaField('params');
        $field->setLabel($I18N->msg('minfo_field_label_params'));
        $field->setSuffix($notices);

        $field = &$this->addTextAreaField('attributes');
        $field->setLabel($I18N->msg('minfo_field_label_attributes'));
        $notice = '<span class="rex-form-notice" id="a62_field_attributes_notice">' . $I18N->msg('minfo_field_attributes_notice') . '</span>' . "\n";
        $field->setSuffix($notice);

        $field = &$this->addTextField('default');
        $field->setLabel($I18N->msg('minfo_field_label_default'));

        $attributes = array();
        $attributes['internal::fieldClass'] = 'rex_form_asd_restrictons_element';
        $field = &$this->addField('', 'restrictions', $value = null, $attributes);
        $field->setLabel($I18N->msg('minfo_field_label_restrictions'));
        $field->setAttribute('size', 10);
    }

}