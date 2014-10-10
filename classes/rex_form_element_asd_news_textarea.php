<?php

class rex_form_element_asd_news_textarea extends rex_form_element {

    public function __construct($tag, &$table, $attributes = array(), $separateEnding = false) {

        parent::rex_form_element('textarea', $table, $attributes, true);

        if(OOAddon::isAvailable('tinymce')) {

            $this->setAttribute('class', 'tinyMCEEditor');

        } elseif(OOAddon::isAvailable('ckeditor')) {

            $this->setAttribute('class', 'ckeditor');

        }

    }

    function formatClass()
    {
        return 'rex-form-textarea';
    }
}

?>