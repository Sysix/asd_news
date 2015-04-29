ASD News AddOn für REDAXO 4.6+
==============================

Features
--------

* Kompatibel mit SEO AddOns "seo42", "yrewrite", "rexseo"
* Kompatibel mit Plugin "url_control"
* Mehrsprachigkeit
* Erweiterung von Meta-Tags möglich (siehe `rex_asd_news::replaceSeoTags()`)
* Erweiterung von Feldern möglich (dank das Metainfo-Addon)
* SEO42 Image Manager Urls möglich
* On/Off Schaltung von News
* News ab einer bestimmten Anzahl zu archivieren
* fertige Module via Knopfdruck installieren


Update 1.4.2 - ??.04.15
------------

* Feature: Sitemap.xml Einbindung bei seo42/rexseo
* Feature: Admin's brauchen keine extra rechte mehr
* Bugfix: Datepicker sendet nun nurnoch wenn eine Zeit ausgewählt ist
* Bugfix: News veröffentlichen
* Bugfix: Keine News dupliziert beim einlegen einer neuen Sprache
* Bugfix: News Bilder konnten im Medienpool gelöscht werden
* Aufräumen: Spezifische Seo Einstellungen nun in rex_asd_news_config
* Typo: Veröffentlichen/Unveröffentlichen

Update 1.4.0 - 20.03.15
------------
* `$news->replaceSeoTags()` utf-8 Bugfix
* `$news->getRubric()` - Methode hinzugefügt
* Felder werden nun über das Addon `metainfo` verwaltet
* Benutzerrecht `asd_news[metainfo]` hinzugefügt
* Extension `ASD_NEWS_GETIMAGE` hinzugefügt
* Unter Einstellungen: News Artikel auswählbar
* Verbesserte Kompatibilität mit dem Plugin url_control
* F.A.Q. Eintrag "Warum sehe ich keine Einstellungen mehr?" hinzugefügt
* F.A.Q. Eintrag "Kann ich weitere Felder, wie z.B. Vorschautext oder eine Galerie, einfügen?" hinzugefügt
* F.A.Q. Eintrag "Ich sehe keine Kategorien / Bilder mehr. Was kann ich dagegen tun?" hinzugefügt
* Code aufgeräumt

Update 1.3.0 - 31.10.14
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

Update 1.2.0 - 26.09.14
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


Update 1.1.0 - 25.09.14
------------
* Methode `$news->isOnline()` hinzugefügt
* Neuste News zuerst
* Config in Data Ordner für Updatefähigkeit
* Veröffentlichen Bugfix unter Windows
* Rubrik Editieren Bugfix
* url_generate::generatePathFile beim hinzfügen/editieren einer News
* Where Condition flexibler gestaltet
* Erfolgsmeldung beim speichern der Einstellungen


Version 1.0.0 - 23.09.14
-------------

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

* Rubrik Id bekommen
```$url = $news->getRubric();```

* Rubriknamen bekommen
```$url = $news->getRubricName();```

* PublishDate bekommen als DateTime Objekt
```$date = $news->getPublishDate();```

* Überprüfen ob News online
``` $news->isOnline()) ```

* Monatname bekommen
```$monthName = $news->getMonthName($lang = '_de');```

* HTML Meta Tags einfügen
```
$news->replaceSeoTags(array(
  'keywords' => $foo,
  'og:image' => $news->getImage()
));
```