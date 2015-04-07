<?php

class rex_asd_news_ajaxHandler
{

    /**
     * publish a news by id & clang
     * if publish-lang == all => $clang is not needed, but must be set
     * @param $id
     * @param $clang
     * @param DateTime $time
     * @return string
     */
    static public function publishNews($id, $clang, DateTime $time)
    {
        /**
         * @var array $REX
         * @var i18n $I18N
         */
        global $REX, $I18N;

        $sql = new rex_sql();
        $sql->setTable(rex_asd_news_config::getTable());
        $sql->setWhere('`id` = ' . $id . ' AND `clang` = ' . $clang);

        if (rex_asd_news_config::getConfig('published-lang') == 'all') {
            $sql->setWhere('`id` = ' . $id);
        }

        $sql->setValue('publishedAt', $time->format('Y-m-d H:i'));
        $sql->setValue('publishedBy', $REX['USER']->getValue('user_id'));
        $sql->setValue('status', 1);

        $sql->update();

        $sql->setQuery('SELECT * FROM `' . rex_asd_news_config::getTable() . '` WHERE `id` = ' . $id . ' AND `clang` = ' . $clang);

        return '
        <td>' . $id . '</td>
        <td>' . $sql->getValue('title') . '</td>
        <td><span>' . $time->format('d.m.Y H:i') . '</span></td>
        <td><a href="' . self::getBaseUrl($clang) . 'unpublish&amp;id=' . $id . '" class="rex-offline" onclick="return confirm(\'' . $I18N->msg('asd_news_really_unpublish') . '\');">' . $I18N->msg('asd_news_unpublish') . '</a></td>
        <td><a href="' . self::getBaseUrl($clang) . 'edit&amp;id=' . $id . '">' . $I18N->msg('edit') . '</a></td>
        <td><a href="' . self::getBaseUrl($clang) . 'delete&amp;id=' . $id . '" onclick="return confirm(\'' . $I18N->msg('asd_news_really_delete') . '\');">' . $I18N->msg('delete') . '</a></td>
        <td><a href="' . self::getBaseUrl($clang) . 'status&amp;id=' . $id . '" class="rex-online">' . $I18N->msg('status_online') . '</a></td>';


    }

    /**
     * return the redaxo url for the list
     * @param $clang
     * @return string
     */
    static private function getBaseUrl($clang)
    {
        return 'index.php?list=232cc606fc1a5fb5cf5badfc8e360ae0&amp;page=' . rex_asd_news_config::getName()
        . '&amp;subpage=news&amp;clang=' . $clang . '&amp;func=';
    }

}