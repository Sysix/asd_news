<?php

class rex_asd_news_url_control
{
    /**
     * update the article id in the url_control sql table
     * @param int $id
     */
    public static function updateArticleId($id)
    {
        global $REX;

        $sql = new rex_sql();
        $sql->setTable($REX['TABLE_PREFIX'] . 'url_control_generate');
        $sql->setWhere('`table` = "' . rex_asd_news_config::getTable() . '"');
        $sql->select('id');
        for ($i = 1; $i <= $sql->getRows(); $i++) {
            $saveSql = new rex_sql();
            $saveSql->setTable($REX['TABLE_PREFIX'] . 'url_control_generate');
            $saveSql->setWhere('`id` = ' . $sql->getValue('id'));
            $saveSql->setValue('article_id', $id);
            $saveSql->update();

            $sql->next();
        }
    }


    /**
     * check if entrys exists for the addon
     * @return bool
     */
    public static function checkEntrys()
    {
        global $REX;

        $sql = new rex_sql();
        $sql->setTable($REX['TABLE_PREFIX'] . 'url_control_generate');
        $sql->setWhere('`table` = "' . rex_asd_news_config::getTable() .'"');
        $sql->select('1');

        return (bool) $sql->getRows();
    }

}