<?php

$news_id = rex_asd_news::getNewsId();

if ($news_id) {

    $news = rex_asd_news::getNewsById($news_id);

    $url = $news->getUrl();
    $text = $news->getValue('text');
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
        <b><?php echo $date->format('d. ').$news->getMonthName().$date->format(' Y H:i'); ?></b><br />
        <img src="<?php echo $news->getImage() ?>" alt="" >
        <?php echo $text; ?>
    </div>
    <?php


} else {

    foreach (rex_asd_news::getNewsByCategory('REX_VALUE[1]') as $news) {
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

}

?>