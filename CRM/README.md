# Einfaches CRM -- Schulprojekt

Dieses Projekt implementiert ein vollständiges, einfaches CRM-System mit
folgenden Funktionen:

## 📌 Features

### 🏠 Startoberfläche

-   Kundenliste mit Suche
-   Globale Bestellliste (chronologisch absteigend)
-   Globale Kontaktliste (mit Filter nach Art)

### 👤 Kunden-Detailansicht

-   Kundendaten
-   Umsatz gesamt
-   Umsatz letztes Jahr
-   Umsatz nach Datumsbereich (mit Filter-Funktion)
-   Letzte 10 Bestellungen
-   Letzte 10 Kontakte

### ➕ Datenerfassung direkt im CRM

-   Neue Kunden anlegen
-   Neue Bestellungen für einen Kunden anlegen
-   Neue Kontakte für einen Kunden erfassen
-   CSV-Export der Bestellungen eines Kunden

------------------------------------------------------------------------

# 🛠️ Technologien

-   **PHP 8.x**
-   **MySQL / MariaDB**
-   **Bootstrap 5 (CDN)**
-   **HTML/CSS**
-   Hosting: *easyname Webspace*

------------------------------------------------------------------------

# 📦 Projektstruktur

    CRM/
    ├─ db/
    │  ├─ schemas.sql 
    ├─ src/
    │  ├─ config.php
    │  ├─ functions.php
    │  ├─ login.php
    │  ├─ logout.php
    │  ├─ index.php
    │  ├─ customer.php
    │  ├─ customer_new.php
    │  ├─ order_new.php
    │  ├─ contact_new.php
    │  ├─ export_orders.php
    │  └─ seed.php
    |-screenshots/
    |  ├─ startseite.png
    |  └─ kundendetais.png
    |README.md
    

------------------------------------------------------------------------

# 🧩 Setup-Anleitung

Diese Anleitung erklärt Schritt für Schritt, wie das CRM installiert und
gestartet wird.

------------------------------------------------------------------------

## ✅ 1. Repository herunterladen

Sie können das Repository als ZIP-Datei herunterladen oder über Git
klonen:

    git clone <REPO-URL>

------------------------------------------------------------------------

## ✅ 2. Datenbank vorbereiten

1.  phpMyAdmin öffnen\
2.  Neue Datenbank anlegen (oder bestehende verwenden)\
3.  Datei `db/schemas.sql` importieren

Dadurch werden die Tabellen erstellt:

-   `users`
-   `customers`
-   `orders`
-   `contacts`

------------------------------------------------------------------------

## ✅ 3. Zugangsdaten in `config.php` eintragen

In der Datei `src/config.php` folgende Zeilen anpassen:

``` php
$DB_HOST = 'localhost' oder 'mysql.easyname.com';
$DB_NAME = 'DEINE_DB';
$DB_USER = 'DEIN_BENUTZER';
$DB_PASS = 'DEIN_PASSWORT';
```

Diese Daten entsprechen genau denen der MySQL-Datenbank.

------------------------------------------------------------------------

## ✅ 4. Beispiel-Daten einfügen (Seeder)

Über den Browser folgenden Link öffnen:

    /CRM/seed.php

Es erscheint:

    Seeding...
    Tabellen geleert.
    Benutzer eingefügt.
    Kunden eingefügt.
    Bestellungen eingefügt.
    Kontakte eingefügt.
    Fertig.

Nach erfolgreichem Seed: ⚠️ **Datei seed.php löschen** (aus
Sicherheitsgründen)

------------------------------------------------------------------------

## ✅ 5. Login

Login-Seite öffnen:

    /CRM/login.php

Standard-Login:

-   **Benutzername:** chef
-   **Passwort:** chef123

------------------------------------------------------------------------

# 🧪 Nutzung des CRM

## ✔ Kundenbereich

-   Kunden suchen
-   Kunden auswählen
-   Kunden-Detailansicht

Im Kundenprofil können hinzugefügt werden:

-   Neue Bestellung
-   Neuer Kontakt
-   CSV Export aller Bestellungen
-   Umsatz anzeigen

------------------------------------------------------------------------

## ✔ Bestellungen-Tab

-   Anzeige aller Bestellungen im System
-   Sortiert nach Datum
-   Suchfunktion

------------------------------------------------------------------------

## ✔ Kontakte-Tab

-   Alle Kontakte global einsehen
-   Filter nach Kontakt-Art (Telefon, E-Mail usw.)

------------------------------------------------------------------------

# 📤 CSV-Export (Wichtig für die Abgabe)

Für jeden Kunden gibt es einen Button:

**„CSV Export"**

Dieser erzeugt automatisch eine Datei:

    Bestellungen_K-1001_20240620.csv

Mit folgenden Spalten:

-   Datum\
-   Bestellnummer\
-   Betrag (€)\
-   Status

Perfekt für die Bewertung und Demonstration.

------------------------------------------------------------------------

# 🖼️ Screenshots

Die beiden Pflicht-Ansichten im Ordner `docs/screenshots/`:

-   startseite.png\
-   kundendetail.png

------------------------------------------------------------------------

# 🧾 Hinweise für den Lehrer

-   Das Projekt nutzt **kein Framework**, sondern reines PHP →
    Schulprojekt-konform\
-   Bootstrap sorgt für einfache, responsive Darstellung\
-   Der Code ist modular aufgebaut (`functions.php`, Seed, Migration
    usw.)\
-   Alle Muss-, Soll- und Bonus-Kriterien der Aufgabenstellung sind
    erfüllt

------------------------------------------------------------------------

# ✔ Fertig!

Das CRM ist vollständig lauffähig und kann sofort verwendet oder
erweitert werden.
