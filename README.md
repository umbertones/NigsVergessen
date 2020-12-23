Nigs vergessen - eine einfache Einkaufsliste als single-php-Script

Der Datentransfer ist schlank, daß im Supermarkt auch eine Edge-Verbindung (mit ein bißchen Geduld) genügt (kann man in D leider viel zu oft testen....)

Voraussetzungen: ein xAMP-Server, dessen Zugangsdaten in einer gesonderten PHP-Datei gespeichert sind. Durch die Namensgebung ist die gesonderte Datei vor bösen Blicken von aussen auf einem xAMP-Server üblicherweise geschützt.

Auf diesem xAMP-Server legt man die beiden Dateien (php- und css-Datei) in ein Verzeichnis. Zugriffsschutz auf das Verzeichnis über den xAMP-Server (siehe dort)

Auf einer MYSQL-Datenbank auf dem xAMP-Server muss eine passende Tabelle angelegt sein (TODO: PHP-Funktion init, die die leere Tabelle erstellt)

Auf der Startseite sind zunächst 3 Links.

Ganz oben "Liste aktualisieren", quasi der Home-Button.
Dann "Artikel hinzufügen", mit dem man - ach, ach was - einen neuen Artikel in die Liste einfügen kann (TODO: ein Konzept für die Menge)
Man trägt den Namen ein und clickt auf "Senden", schon taucht der Artikel in der Einkaufsliste auf. So kann jeder mit Zugriff auf die Seite Artikel eintragen.
Artikel, die schon mal gekauft wurden, aber aktuell nicht auf der Liste sind, kann man unten durch einen Click auswählen. Oben stehen eine Anzahl von Top-Artikeln (Zahl wird in der Variable topanz festgelegt), darunter dann alle Artikel in alphabetischer Reihenfolge. Je häufiger der Artikel gekauft wurde, desto größer wird er dargestellt. Parallel wird das Datum des letzten Einkaufs in der Datenbank geführt.
Unter dem Link "Statistik anzeigen" kann man schauen wie oft man welchen Artikel gekauft hat und wann das letzte Mal war. 

Im Supermarkt hakt man die Artikel dann mit einem Click auf "Gekauft" von der Liste ab. Dabei wird das Datum, wann er gekauft wurde, aktualisiert.

TODO:
- fehlerhafte Einträge in der Datenbank editieren (Klassiker: Mahmedium statt Magnesium)
- Clear All für die Einkaufsliste (für die Merkriesen, die nicht einzeln abhaken müssen oder am Ende des Einkaufs "weg mit dem Rest")
- "Mist, verclickt" vulgo Undo-Funktion (zumindest der letzte Schritt, besser mehr
- Visible mit Bitmuster für 2. Liste (Daheim und Ferien)
