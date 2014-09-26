<?php

class rex_asd_pager extends rex_pager
{
    private $rowCount;
    private $rowsPerPage;
    private $cursorName;

    private $prevButton = true;
    private $nextButton = true;

    private $archive = false;

    public function __construct($rowsPerPage = 30, $cursorName = 'page')
    {
        parent::__construct($rowsPerPage, $cursorName);

        $this->cursorName = $cursorName;
        $this->rowsPerPage = $rowsPerPage;
    }

    /**
     * @param bool $archive
     * @return self
     */
    public function setArchive($archive = false)
    {
        $this->archive = $archive;

        return $this;
    }

    /**
     * Returns the number of the current page
     * @return int The current page number
     */
    public function getCurrentPage()
    {
        return rex_request($this->cursorName, 'int', 0);
    }

    /**
     * @param array $list
     * @return array
     */
    public function filterList(array $list)
    {
        global $REX;

        $currentPage = $this->getCursor();
        $startNews = $this->getCursor($currentPage);

        $i = 1;

        if (count($list) > $startNews) {
            while (key($list) < $startNews) {
                unset($list[key($list)]);
                $i++;
            }
            reset($list);
        }

        $newList = array();
        $list = array_values($list);

        array_map(function ($value) use ($REX, &$newList, &$i) {

            if (count($newList) < $REX['ADDON']['asd_news']['config']['max-per-page']) {

                if (!$this->archive && $i >= $REX['ADDON']['asd_news']['config']['min-archive']) {

                    if ($i == $REX['ADDON']['asd_news']['config']['min-archive']) {
                        $this->nextButton = false;
                    } else {
                        return $value;
                    }

                }

                $newList[] = $value;
                $i++;
            }

            return $value;

        }, $list);

        return $newList;

    }

    /**
     * @return string
     */
    public function getButtons()
    {
        $return = array();

        if ($this->getPageCount() > 1) {
            if ($this->getCurrentPage() != $this->getPrevPage() && $this->prevButton) {
                $return[] = '<a class="button asd-pager-left" href="' . rex_getUrl('', '', array($this->getCursorName() => $this->getPrevPage())) . '">prev</a>';
            }

            if ($this->getCurrentPage() != $this->getNextPage() && $this->nextButton) {
                $return[] = '<a class="button asd-pager-right" href="' . rex_getUrl('', '', array($this->getCursorName() => $this->getNextPage())) . '">next</a>';
            }
        }

        return implode(PHP_EOL, $return);
    }
}

?>