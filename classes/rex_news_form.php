<?php

class rex_news_form extends rex_form
{

    /** @var rex_sql $sqlFields */
    public $sqlFields;
    /** @var rex_sql $sql */
    public $sql;

    /** @var array $langOverSaved Felder für die Sprachübergreifenden Felder */
    public $langOverSaved = array();

    public function __construct($tableName, $fieldset, $whereCondition, $method = 'post', $debug = false)
    {
        global $REX;

        parent::rex_form($tableName, $fieldset, $whereCondition, $method, $debug);

        if (OOAddon::isAvailable('metainfo')) {
            require_once rex_path::addon('metainfo', 'extensions/extension_art_metainfo.inc.php');

            $category = $REX['ADDON']['asd_news']['config']['sql']['category'];

            $this->sqlFields = _rex_a62_metainfo_sqlfields('asd_',
                'AND (`p`.`restrictions` = "" OR p.`restrictions` LIKE "%|' . $this->getValue($category) . '|%") ');

        }
    }

    /**
     * set the values for the other lang
     * @param $args,...
     * @return $this
     */
    public function setOverSavedFields($args)
    {
        $args = func_get_args();
        $this->langOverSaved = $args;

        return $this;
    }

    /**
     * @return array
     */
    public function getOverSavedFields()
    {
        return $this->langOverSaved;
    }


    /**
     * @param string $fieldsetName
     * @param string $fieldName
     * @param string $fieldValue
     * @param rex_sql $saveSql
     * @return mixed
     */
    public function preSave($fieldsetName, $fieldName, $fieldValue, &$saveSql)
    {
        if (OOAddon::isAvailable('metainfo')) {
            $params = array();
            $this->sqlFields->reset();
            _rex_a62_metainfo_handleSave($params, $saveSql, $this->sqlFields);
        }

        return parent::preSave($fieldsetName, $fieldName, $fieldValue, $saveSql);
    }

    /**
     * @return string
     */
    public function getMetainfoExtension()
    {
        $this->sql->setValues($this->getMetaValues());
        return rex_a62_metaFields($this->sqlFields, $this, 'rex_a62_metainfo_form_item', array());
    }

    /**
     * @param $name
     * @return array|null|string
     */
    public function getValue($name)
    {
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

    /**
     * @return array
     */
    public function getValues()
    {
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
                /** @var  rex_form_element $element */
                $values[$element->getFieldName()] = $element->getSaveValue();
            }
        }

        return $values;
    }

    /**
     * @param rex_news_form $form
     * @param $id
     * @param $clang
     */
    public static function saveOverLangValues(rex_news_form $form, $id, $clang)
    {
        global $REX;

        $lang = new rex_sql();
        $lang->setQuery('SELECT `id` FROM `' . $REX['TABLE_PREFIX'] . 'clang` WHERE `id` != ' . $clang);
        for ($i = 1; $i <= $lang->getRows(); $i++) {

            $sql = new rex_sql();

            $sql->setTable($form->getTableName());
            $sql->setDebug(true);
            $sql->setWhere('`id` = ' . $id . ' AND `clang` = ' . $lang->getValue('id'));

            $sql->select('id');

            $sql->setTable($form->getTableName());
            $sql->setDebug(true);
            $sql->setWhere('`id` = ' . $id . ' AND `clang` = ' . $lang->getValue('id'));

            if(OOAddon::isAvailable('metainfo')) {
                $sql->setValues($form->getMetaValues());
            }

            if($sql->getRows()) {
                $sql->setValues(array_intersect_key(
                    $form->getValues(),
                    array_flip($form->getOverSavedFields())
                ));

                $sql->setValue('clang', $lang->getValue('id'));
                $sql->setValue('id', $id);
                $sql->update();
            } else {
                $sql->setValues($form->getValues());

                $sql->setValue('clang', $lang->getValue('id'));
                $sql->setValue('id', $id);
                $sql->insert();
            }

            $lang->next();
        }

    }


    /**
     * return array with the meta name & values
     * it test if the form is posted or not and add the necessary values
     * @return array
     */
    public function getMetaValues()
    {
        global $REX;

        $returnArray = array();

        $this->sqlFields->reset();
        for ($i = 0; $i < $this->sqlFields->getRows(); $i++, $this->sqlFields->next()) {

            $fieldName = $this->sqlFields->getValue('name');
            $fieldType = $this->sqlFields->getValue('type');
            $fieldAttributes = $this->sqlFields->getValue('attributes');
            $postValue = rex_post($fieldName, 'array', null);

            // Wert aus der DB nehmen, falls keiner extern und keiner im POST angegeben
            if ($postValue === null && $this->sql->getRows() == 1 && $this->sql->hasValue($fieldName)) {
                $postValue = $this->sql->getValue($fieldName);
            }

            // dont save restricted fields
            $attrArray = rex_split_string($fieldAttributes);
            if (isset($attrArray['perm'])) {
                if (!$REX['USER']->hasPerm($attrArray['perm'])) {
                    continue;
                }
                unset($attrArray['perm']);
            }

            // handle date types with timestamps
            if (isset($postValue['year']) && isset($postValue['month']) && isset($postValue['day']) && isset($postValue['hour']) && isset($postValue['minute'])) {
                if (isset($postValue['active'])) {
                    $saveValue = mktime((int)$postValue['hour'], (int)$postValue['minute'], 0, (int)$postValue['month'], (int)$postValue['day'], (int)$postValue['year']);
                } else {
                    $saveValue = 0;
                }
            } // handle date types without timestamps
            elseif (isset($postValue['year']) && isset($postValue['month']) && isset($postValue['day'])) {
                if (isset($postValue['active'])) {
                    $saveValue = mktime(0, 0, 0, (int)$postValue['month'], (int)$postValue['day'], (int)$postValue['year']);
                } else {
                    $saveValue = 0;
                }
            } // handle time types
            elseif (isset($postValue['hour']) && isset($postValue['minute'])) {
                if (isset($postValue['active'])) {
                    $saveValue = mktime((int)$postValue['hour'], (int)$postValue['minute'], 0, 0, 0, 0);
                } else {
                    $saveValue = 0;
                }
            } else {
                if (count($postValue) > 1) {
                    // Mehrwertige Felder
                    $saveValue = '|' . implode('|', $postValue) . '|';
                } else {
                    $postValue = (is_array($postValue)) && isset($postValue[0]) ? $postValue[0] : $postValue;

                    if ($fieldType == REX_A62_FIELD_SELECT && strpos($fieldAttributes, 'multiple') !== false ||
                        $fieldType == REX_A62_FIELD_CHECKBOX
                    ) {
                        // Mehrwertiges Feld, aber nur ein Wert ausgewählt
                        $saveValue = '|' . $postValue[0] . '|';
                    } else {
                        // Einwertige Felder
                        $saveValue = $postValue;
                    }
                }
            }

            // Wert in SQL zum speichern
            $returnArray[$fieldName] = $saveValue;
        }

        return $returnArray;
    }

}