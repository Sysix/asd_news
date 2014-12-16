<?php

$news_id = rex_asd_news::getNewsId();

if ($news_id) {

    /** @var rex_asd_news $news */
    $news = rex_asd_news::getNewsById($news_id);

    $url = $news->getUrl();
    $text = $news->getValue('asd_text');
    $date = $news->getPublishDate();
    $title = $news->getValue('title');
    $id = $news->getValue('id');

    $news->replaceSeoTags(array(
        'keywords' => '',
        'og:image' => $news->getImage(),
        'og:published_time' => $date->format('c'),
        'og:title' => $news->getValue('title')
    ));

    ?>
    <div class="asd-news" id="news-<?php echo $id; ?>">
        <h3><?php echo $title; ?></h3>
        <img src="<?php echo $news->getImage() ?>" alt="" class="news-picture">
        <span class="asd-news-date"><?php echo $date->format('d. ').$news->getMonthName().$date->format(' Y H:i'); ?></span>
        <?php echo $text; ?>
        <a class="button" href="<?php echo rex_getUrl('', '') ?>">zurück</a>
    </div>
    <?php


} else {

    $newsList = rex_asd_news::getNewsByCategory('REX_VALUE[1]');

    $pager = new rex_asd_pager($REX['ADDON']['asd_news']['config']['max-per-page'], 'page');
    $pager->setRowCount(count($newsList));

    $newsList = $pager->filterList($newsList);

    foreach ($newsList as  $news) {
    /** @var rex_asd_news $news */

        $title = $news->getValue('title');
        $url = $news->getUrl();
        $id = $news->getValue('id');
        $date = $news->getPublishDate();


        ?>
        <div class="asd-news" id="news-<?php echo $id; ?>">
            <h3><?php echo $title; ?></h3>
            <?php echo $date->format('d.m.Y H:i'); ?>
            <a href="<?php echo $url; ?>">Artikel ansehen</a>
        </div>
        <?php

    }

    echo $pager->getButtons();
}

?>