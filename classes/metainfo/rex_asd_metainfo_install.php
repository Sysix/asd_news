<?php

/**
 * Class rex_asd_metainfo_install
 * Für den leichteren Gebrauch von Metainfo Funktionen/Einstellungen
 * wird sowohl für die install.inc.php, uninstall.inc.php und update.inc.php benutzt
 */
class rex_asd_metainfo_install
{

    /**
     * set the property for the metainfo.
     * added Entrys in metaTables and prefixes
     */
    public static function setProperty()
    {
        global $REX;

        $metaTables = OOAddon::getProperty('metainfo', 'metaTables', array());
        $metaTables['asd_'] = $REX['TABLE_PREFIX'] . 'asd_news';
        OOAddon::setProperty('metainfo', 'metaTables', $metaTables);

        $prefixes = OOAddon::getProperty('metainfo', 'prefixes', array());
        if (!in_array('asd_', $prefixes)) {
            $prefixes[] = 'asd_';
        }
        OOAddon::setProperty('metainfo', 'prefixes', $prefixes);
    }

    /**
     * added the metafields
     * @return string
     */
    public static function addFields()
    {
        global $REX;

        $class = '';
        if(OOAddon::isAvailable('tinymce')) {
            $class = 'tinyMCEEditor';
        } elseif(OOAddon::isAvailable('ckeditor')) {
            $class ='ckeditor';
        }

        return self::checkErrorMessage(
            a62_add_field('translate:content_category', 'asd_category', 2, '', 3, '', 'SELECT `name`, `id` FROM ' . $REX['TABLE_PREFIX'] . 'asd_news_category'),
            a62_add_field('translate:asd_news_picture', 'asd_picture', 3, '', 6, ''),
            a62_add_field('translate:asd_news_text', 'asd_text', 4, 'class=' . $class, 2, '')
        );
    }

    /**
     * @param mixed $args
     * @return string
     */
    public static function checkErrorMessage($args)
    {
        $args = (is_array($args)) ? $args : func_get_args();

        $returnString = '';
        foreach ($args as $toCheck) {

            if (is_string($toCheck)) {
                $returnString .= $toCheck . '<br />';
            }

        }

        if($returnString) {
            $returnString = 'Metainfo Error: <br />' . $returnString;
        }

        return $returnString;
    }

    /**
     * delete the metafields
     */
    public static function delFields()
    {
        global $REX;

        $sql = new rex_sql();
        $sql->setQuery('SELECT `name` FROM ' . $REX['TABLE_PREFIX'] . '62_params WHERE `name` LIKE "asd_%"');

        $delFields = array();

        for($i = 1; $i <= $sql->getRows(); $i++) {
            $delFields[] = a62_delete_field($sql->getValue('name'));
            var_dump($sql->getValue('name'));
            $sql->next();
        }

        return self::checkErrorMessage($delFields);
    }

}