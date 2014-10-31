ASD News AddOn für REDAXO 4.6+
==============================

Features
--------

* Kompatibel mit SEO AddOns "seo42", "yrewrite", "rexseo"
* Kompatibel mit Plugin "url_control"
* Mehrsprachigkeit
* Erweiterung von Meta-Tags möglich (siehe `rex_asd_news::replaceSeoTags()`)
* SEO42 Image Manager Urls möglich
* On/Off Schaltung von News
* News ab einer bestimmten Anzahl zu archivieren
* fertige Module via Knopfdruck installieren

Update 1.3.0
------------
* CKEditor Support
* Admin's müssen jetzt die benötigten Rechte besitzen
* Bilder die das Addon verwendet, können nicht gelöscht werden
* anderen jQuery Datetimepicker thx@RexDude & thx@xdan
* Pagination-Auswahl zwischen Seitenanzahl & Vor/Zurück-Buttons
* Pagination/Pager CSS-ID über Einstellungen verwaltbar
* Backend Struktur geändert
* Securityfix
* Bugfix: offline News veröffentlichen
* Bugfix: Aktiv Style bei Navigation

Update 1.2.0
------------
* Unter Einstellungen => ab welcher Newsanzahl die News in das Archiv landen
* Modul "ASD News - Archiv" hinzugefügt
* Modul "ASD News - Alle News" hinzugefügt
* Extension _ASD_NEWS_GENERATE_URL_ hinzugefügt (`$news->getUrl()`)
* Methode `$news->getRubricName()` hinzugefügt
* `rex_asd_news::getAllNews($clang = null)` - Ausgabe aller News
* `rex_asd_news::getArchiveNews()` - Ausgabe des Archivs
* `$news->getUrl($params = array())` - `$params` kann nun `clang` & `article-id` beinhalten
* `$pager->setArchive($archive = false)` - Newsanzahl für Archiv berücksichtigen
* `$pager->getButtons()` Ausgabe der next/prev Buttons
* Bugfix "call unstatic method`getDefaultWhere` & `generateWhere` static"


Update 1.1.0
------------
* Methode `$news->isOnline()` hinzugefügt
* Neuste News zuerst
* Config in Data Ordner für Updatefähigkeit
* Veröffentlichen Bugfix unter Windows
* Rubrik Editieren Bugfix
* url_generate::generatePathFile beim hinzfügen/editieren einer News
* Where Condition flexibler gestaltet
* Erfolgsmeldung beim speichern der Einstellungen


PHP Methoden
------------

* Einzelne News
```$news = rex_asd_news::getNewsById((int)$id);```

* Mehrere News
```$news = rex_asd_news::getNewsByIds(array(1, 2, 3, 4));```

* Alle News
```$news = rex_asd_news::getAllNews();```

* Archivierte News 
```$news = rex_asd_news::getArchiveNews();```

* Mehrere News von einer Kategorie
```$news = rex_asd_news::getNewsByCategory((int)$catId);```

* News ID bekommen
```$newsId = rex_asd_news::getNewsId();```

* SQL Spalte bekommen (siehe `rex_sql::getValue`)
```$title = $news->getValue('title', $default = null);```

* URL bekommen
```$url = $news->getUrl($params = array());```

* Rubriknamen bekommen
```$url = $news->getRubricName();```

* PublishDate bekommen als DateTime Objekt
```$date = $news->getPublishDate();```

* Überprüfen ob News online
```if($news->isOnline())```

* Monatname bekommen
```$monthName = $news->getMonthName($lang = '_de');```

* Meta Tags einfügen
```
$news->replaceSeoTags(array(
  'keywords' => $foo,
  'og:image' => $news->getImage()
));
```