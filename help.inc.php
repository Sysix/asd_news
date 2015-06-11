<div class="rex-addon-output">
    <?php
    ob_start();
    include(__DIR__ . '/DOCUMENTATION.md');
    $content = ob_get_contents();
    ob_end_clean();
    echo preg_replace('/<table([^>]*)>/', '<table class="rex-table" $1>', $content);
    ?>
</div>
<?php

include(__DIR__ . '/pages/faq.php');