## Datu izguve un apstrāde

### Tehniskās prasības programmas palaišanai

* `PHP 8.1`
* `NodeJs 18.12`

### Programmas palaišana

1) jāieinstelē nepieciešamās pakas, izmantojot `npm install`
2) jāizsauc scrape.js programma (datu ieguves daļai), izmantojot komandu `node scrape.js`
3) jāizplida main.php programma (datu apstrādei, statistikas apkopošanai), izmantojot `php main.php`

### Programmas failu skaidrojums

* `node_modules, package.json, package-lock.json` - ar Node saistītie faili
* `.gitignore` - ignorējamo failu saraksts (git)
* `data` - mape kurā glabājas iegūtie dati un telefona numuru, epastu kopējie saraksti
* `old.code` - mape, kurā ir orģinālā programmas versija (ir vairāki iztrūkumi) un testa faili
* `results` - mape, kurā ir apstrādātie dati (kopējie un pa mājaslapām sadalīti tukšie, atkārtojošie rezultāti)
* `main.php` - PHP programma, kuras vienīgais mērķis ir izsauc un izplidīt SorterAndFilter klasi 
* `scrape.js` - JavaScript programma, kura iegūst datus
* `SorterAndFilter.php` - PHP programma, izveidota klases veidā, kas apstrādā iegūtos datus
* `statistics.txt` - kopējā statistika, par apstrādātajiem rezultātiem

### Piezīmes par programmas darbību

* `scrape.js` ir tieši saistīts ar mājaslapām, no kurām tiek iegūti dati, kas nozīmē, ka nākotnē kāda šīs programmas daļa vairs var nedarboties un vajadzēs veikt labojumus