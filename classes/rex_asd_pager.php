<?php

class rex_asd_pager extends rex_pager
{
    private $rowCount;
    private $rowsPerPage;
    private $cursorName;

    public $startAt;
    public $endAt;

    private $archive = false;

    public function __construct($rowsPerPage = 30, $cursorName = 'page')
    {
        global $REX;

        parent::__construct($rowsPerPage, $cursorName);

        $this->cursorName = $cursorName;
        $this->rowsPerPage = $REX['ADDON']['asd_news']['config']['max-per-page'];


    }

    /**
     * @param int $count
     * @return self
     */
    function setRowsCount($count)
    {
        global $REX;

        if ($REX['ADDON']['asd_news']['config']['min-archive'] < $count) {
            $count = $REX['ADDON']['asd_news']['config']['min-archive'];
        }

        $this->rowCount = $count;

        return $this;
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
     * @param int $start
     * @return self
     */
    public function setStartAt($start)
    {
        $this->startAt = $start;

        return $this;
    }

    /**
     * @param int $end
     * @return self
     */
    public function setEndAt($end)
    {
        $this->endAt = $end;

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
        // Set Start- / Endpoint
        $this->setStartAt($this->getCurrentPage() * $this->rowsPerPage);
        $this->setEndAt($this->rowsPerPage);

        // Set Start
        if (count($list) > $this->startAt) {
            while (key($list) < $this->startAt) {
                unset($list[key($list)]);
            }
            reset($list);
        }

        // Reset Keys
        $list = array_values($list);

        // Set End
        if (count($list) <= $this->endAt) {
            return $list;
        }

        // Set End #2
        $newList = array();
        foreach ($list as $i => $values) {
            if ($i >= $this->endAt) {
                break;
            }
            $newList[] = $values;
        }

        return $newList;
    }

    /**
     * @return string
     */
    public function getButtons()
    {
        global $REX;

        $return = array();

        if ($this->getPageCount() <= 1) {
            return '';
        }

        if ($REX['ADDON']['asd_news']['config']['pagination'] == 'site-number') {

            $return[] = '<ul class="pagination" id="asd-pagination">';

            // Prev Button
            $href = rex_getUrl('', '', array($this->getCursorName() => $this->getPrevPage()));
            $class = '';
            if ($this->getPrevPage() == $this->getCurrentPage()) {
                $href = '#';
                $class = ' class="disabled"';
            }
            $return[] = '<li' . $class . '><a href="' . $href . '">«</a></li>';

            for ($i = 0; $i < $this->getPageCount(); $i++) {

                $active = ($i == $this->getCurrentPage()) ? ' class="active"' : '';

                $return[] = '<li ' . $active . '>
                    <a href="' . rex_getUrl('', '', array($this->getCursorName() => $i)) . '">' . ($i + 1) . '</a>
                </li>';

            }

            // Next Button
            $href = rex_getUrl('', '', array($this->getCursorName() => $this->getNextPage()));
            $class = '';
            if ($this->getNextPage() == $this->getCurrentPage()) {
                $href = '#';
                $class = ' class="disabled"';
            }
            $return[] = '<li' . $class . '><a href="' . $href . '">»</a></li>';

            $return[] = '</ul>';
        }

        if ($REX['ADDON']['asd_news']['config']['pagination'] == 'pager') {

            $return[] = '<ul class="pager" id="asd-pager">';

            if ($this->getCurrentPage() != $this->getPrevPage()) {
                $return[] = '<li class="previous"><a href="' . rex_getUrl('', '', array($this->getCursorName() => $this->getPrevPage())) . '">prev</a></li>';
            }

            if ($this->getCurrentPage() != $this->getNextPage()) {
                $return[] = '<li class="next"><a href="' . rex_getUrl('', '', array($this->getCursorName() => $this->getNextPage())) . '">next</a></li>';
            }

            $return[] = '<ul>';

        }

        return implode(PHP_EOL, $return);
    }
}

?>