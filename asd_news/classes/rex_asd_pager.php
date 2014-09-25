<?php

class rex_asd_pager extends rex_pager
{
    private $rowCount;
    private $rowsPerPage;
    private $cursorName;

    public function __construct($rowsPerPage = 30, $cursorName = 'page')
    {
        parent::__construct($rowsPerPage, $cursorName);

        $this->cursorName = $cursorName;
        $this->rowsPerPage = $rowsPerPage;
    }

    /**
     * Returns the number of the current page
     * @return int The current page number
     */
    public function getCurrentPage()
    {
        return rex_request($this->cursorName, 'int', 0);
    }

    public function filterList($list)
    {
        global $REX;

        $currentPage = $this->getCursor();
        $startNews = $this->getCursor($currentPage);

        if (count($list) > $startNews) {
            while (key($list) < $startNews) {
                unset($list[key($list)]);
            }
            reset($list);
        }

        $newList = array();
        $list = array_values($list);

        array_map(function ($value) use ($REX, &$newList) {

            if (count($newList) < $REX['ADDON']['asd_news']['config']['max-per-page']) {
                $newList[] = $value;
            }
            return $value;

        }, $list);

        return $newList;

    }
}

?>