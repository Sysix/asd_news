<table>
        <tr>
            <th colspan="2">Methoden um bestimmte News zu erhalten</th>
        </tr>
        <tr>
            <td>Einzelne News</td>
            <td><code>$news = rex_asd_news::getNewsById(1);</code></td>
        </tr>
        <tr>
            <td>Mehrere News</td>
            <td><code>$news = rex_asd_news::getNewsByIds(array(1, 2, 3, 4));</code></td>
        </tr>
        <tr>
            <td>Alle News</td>
            <td><code>$news = rex_asd_news::getAllNews();</code></td>
        </tr>
        <tr>
            <td>Archivierte News</td>
            <td><code>$news = rex_asd_news::getArchiveNews();</code></td>
        </tr>
        <tr>
            <td>Mehrere News durch Kategorie-Id</td>
            <td><code>$news = rex_asd_news::getNewsByCategory(1);</code></td>
        </tr>
        <tr>
            <th colspan="2">Einzelne News Methoden</th>
        </tr>
        <tr>
            <td>Sql Spalte</td>
            <td><code>$news->getValue('title')</code></td>
        </tr>
        <tr>
            <td>Url</td>
            <td><code>$news->getUrl()</code></td>
        </tr>
        <tr>
            <td>Rubrik Id</td>
            <td><code>$news->getRubric()</code></td>
        </tr>
        <tr>
            <td>Rubriknamen</td>
            <td><code>$news->getRubricName()</code></td>
        </tr>
        <tr>
            <td>Veröffentlichungs-Datum (DateTime Objekt)</td>
            <td><code>$news->getPublishDate()</code></td>
        </tr>
        <tr>
            <td>Bild-Url</td>
            <td><code>$news->getImage($imageType = null)</code></td>
        </tr>
        <tr>
            <th colspan="2">Extra Methoden</th>
        </tr>
        <tr>
            <td>Meta Tags im Head-Bereich einfügen</td>
            <td><pre><code>rex_asd_news::replaceMetaTags(array(
                        'keywords' => $foo,
                        'og:image' => $news->getImage()
                        ));</code></pre>
            </td>
        </tr>
        <tr>
            <td>Online Abfrage</td>
            <td><code>$news->isOnline()</code></td>
        </tr>
    </table>