
================================
Shopgate Framework
================================

Dieses Framework ermöglicht die schnelle und einfache Integration
von Shopgate in ein bestehendes Shopping-System.

================================
Aufbau der Dateistruktur
================================

Dateiordner der Cronjobs.
/cronjobs/
	
Skript zum Updaten der items-CSV-Datei.
/cronjobs/update_items_csv.php

Skript zum Updaten der pages-CSV-Datei.
/cronjobs/update_pages_csv.php

Skript zum Updaten der reviews-CSV-Datei.
/cronjobs/update_reviews_csv.php

=================================

Dateiordner zum Speichern von CSV- und Log-Dateien.
/data/

Datei zum Verwalten der Zugriffsrechte auf den Dateiordner.
Browser können nicht auf diesen Ordner zugreifen.
/data/.htaccess

=================================

Dateiordner mit der Programmbibliothek.
/lib/

Datei zum Verwalten von Shopgate-Connect.
siehe https://www.shopgate.com/apidoc/shopgate_connect_api
/lib/connect_api.php

Datei welche die verschiedenen Funktionsaufrufe der einzelnen 
Unterklassen verwaltet.
/lib/core_api.php

Das Framework an sich. Diese Datei stellt die Grundfunktionalität 
des Frameworks zur Verfügung.
/lib/framework.php

Datei zum Verwalten aller Aufrufe bezüglich Produkten.
/lib/item_api.php

Datei zum Verwalten aller Aufrufe bezüglich Bestellungen.
/lib/order_api.php

==================================

Dateiordner in dem Plugins gespeichert werden.
/plugins/

Eine Bespieldatei für die Integration eines eigenen Plugins.
/plugins/plugin_example.inc.php

Das xt-commerce Plugin.
/plugins/plugin_xtcommerce.inc.php

==================================

Datei zur Verwaltung von Zugriffsrechten.
Lediglich der Zugriff auf api.php und admin.php wird gewährt.
/.htaccess

Datei mit der GUI für die Konfiguration der myconfig.php.
Bitte im Browser aufrufen.
/admin.php

Einstiegspunkt der Anwendung.
/api.php

Beispieldatei wie Ihre Konfigurationdatei des Frameworks
aussehen könnte.
/config.php

Konfigurationsdatei, die über die GUI (admin.php) verwaltet wird.
Achtung diese Datei müssen sie selbst erstellen oder durch die
GUI erzeugen lassen.
/myconfig.php


================================
Installation
================================

Öffnen Sie die Datei config.php im shopgate_framework-Verzeichnis 
und tragen Sie dort Ihre Daten ein. 
Haben Sie Ihre Konfiguration eingetragen, speichern sie die 
Datei bitte unter dem Namen 'myconfig.php' im selben Verzeichnis. 
Loggen Sie sich auf www.shopgate.com ein. In den Einstellungen Ihres 
Shops können Sie im Untermenü 'Integration von Ihrem Shop' und dort
unter 'Shopgate Framework' das Framework aktivieren. Tragen Sie dazu 
in das Feld 'Framework Url' den vollständigen Pfad zum Shopgate-
Framework ein. Beachten Sie, daß aus Sicherheitsgründen nur
das https:// Protokoll verwendet werden kann. 

Eine ausführliche Installationsanleitung finden Sie unter:
http://code.google.com/p/shopgate-framework/wiki/InstallationUndKonfiguration


================================
Kontakt
================================

Shopgate GmbH
Badborngasse 1A
D-35510 Butzbach (Germany)

fon +49 (6033) 7470-20
cel +49 (176) 295 24 228
mail support@shopgate.com
website https://www.shopgate.com/

News unter:
http://twitter.com/shopgate

Geschäftsführer: Ortwin Kartmann
Beirat: Andrea Anderheggen
Handelsregister: HRB 5951
Hauptsitz der Gesellschaft: Butzbach
UST-ID: DE237442513
