<?php

class rex_news_form extends rex_form {

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