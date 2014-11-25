<?php

class rex_news_form extends rex_form {

    public $sqlFields;

    public function __construct($tableName, $fieldset, $whereCondition, $method = 'post', $debug = false) {

        if(OOAddon::isAvailable('metainfo')) {
            require_once rex_path::addon('metainfo', 'extensions/extension_art_metainfo.inc.php');

            $this->sqlFields = _rex_a62_metainfo_sqlfields('asd_',
                'AND (`p`.`restrictions` = "" )'); // OR p.`restrictions` LIKE "%|' . $this->getValue('category') . '|%"
        }

        parent::rex_form($tableName, $fieldset, $whereCondition, $method, $debug);

    }

    public function preSave($fieldsetName, $fieldName, $fieldValue, &$saveSql) {

        if(OOAddon::isAvailable('metainfo')) {
            $this->sqlFields->reset();
            $params = array();
            _rex_a62_metainfo_handleSave($params, $saveSql, $this->sqlFields);
        }

        return parent::preSave($fieldsetName, $fieldName, $fieldValue, $saveSql);
    }

    public function getMetainfoExtension() {
        return rex_a62_metaFields($this->sqlFields, $this, 'rex_a62_metainfo_form_item', array());
    }

    public function getValue($name) {

        $value = null;

        $postValue = $this->elementPostValue($this->getFieldsetName(), $name);
        if ($postValue !== null) {
            $value = $this->stripslashes($postValue);
        }

        // Wert aus der DB nehmen, falls keiner extern und keiner im POST angegeben
        if ($value === null && $this->sql->getRows() == 1 && $this->sql->hasValue($name)) {
            $value = $this->sql->getValue($name);
        }

        if (is_array($value)) {
            $value = '|' . implode('|', $value) . '|';
        }

        return $value;

    }

    public function getValues() {

        $values = array();
        foreach ($this->elements as $fieldsetName => $fieldsetElementsArray) {
            foreach ($fieldsetElementsArray as $key => $element) {
                if ($this->isFooterElement($element)) {
                    continue;
                }
                if ($this->isRawElement($element)) {
                    continue;
                }

                // PHP4 compat notation
                $values[$element->getFieldName()] = $element->getSaveValue();
            }
        }

        return $values;

    }

}