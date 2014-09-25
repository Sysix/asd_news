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

* Mehrere News von einer Kategorie
```$news = rex_asd_news::getNewsByCategory((int)$catId);```

* News ID bekommen
```$newsId = rex_asd_news::getNewsId();```

* SQL Spalte bekommen (siehe `rex_sql::getValue`)
```$title = $news->getValue('title', $default = null);```

* URL bekommen
```$url = $news->getUrl($params = array());```

* PublishDate bekommen als DateTime Objekt*
```$date = $news->getPublishDate();```

* Monatname bekommen
```$monthName = $news->getMonthName($lang = '_de');```

* Meta Tags einfügen
```
$news->replaceSeoTags(array(
  'keywords' => $foo,
  'og:image' => $news->getImage()
));
```