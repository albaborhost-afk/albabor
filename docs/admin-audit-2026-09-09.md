# Admin-Prüfung am 9. September 2026

Die Prüfung erfolgte im angemeldeten Live-Adminbereich und im zugehörigen Code.
Laravel Cloud meldete Deployment 335, Commit `f05f13b`, als erfolgreich veröffentlicht.
Die Unterschiede zum öffentlichen Formular entstehen durch getrennte Oberflächen.

## E-Mail-Anmeldung

Der öffentliche Login normalisierte E-Mail-Adressen bereits. Die Standardanmeldung
von Filament übernahm diese Anpassung nicht. Admin- und Händlerbereich verwenden
jetzt eine gemeinsame angepasste Anmeldung: Großbuchstaben werden bei der Eingabe
kleingeschrieben; vor Validierung und Anmeldung werden zusätzlich äußere Leerzeichen
entfernt. Das Passwort und die Prüfung der Zugriffsrechte bleiben unverändert.

Prüfung: 188 Tests mit 688 Assertions erfolgreich, davon fünf neue Login-Tests;
Pint für die neuen PHP-Dateien erfolgreich. Ein künstliches lokales Admin-Konto
konnte sich mit großgeschriebener E-Mail anmelden. Die sichtbare Kleinschreibung
wurde im Browser kontrolliert. Es wurden keine echten Konten oder Inserate bearbeitet.

## Welche Änderungen im Adminbereich vorhanden sind

| Funktion | Ort | Ergebnis |
| --- | --- | --- |
| Polnische Registrierung mit Flagge | Annonces → Modifier → Specifications → Immatriculation | `🇵🇱 Polonais` live vorhanden. |
| Korrekte Bezeichnung für erhaltenes Gebot | Annonces → Modifier → Informations → Type d'offre | `Offert (offre reçue)` live vorhanden. |
| Privatmodus | Utilisateurs → Créer/Modifier → Paramètres du compte | `Profil privé` mit Erläuterung zur anonymen Veröffentlichung live vorhanden. |
| Telefon-Landesvorwahl | Utilisateurs → Créer/Modifier → Informations personnelles | `Pays`, Flagge und Vorwahl sowie separates lokales Nummernfeld live vorhanden. |
| Motor-Gesamtleistung | Annonces → Modifier → Specifications → Motorisation | `Puissance / moteur (CV)` und automatisch berechnete `Puissance totale (CV)` live vorhanden. |

## Bei der ersten Prüfung noch fehlend

| Punkt aus dem PDF | Befund in Annonces → Modifier → Specifications |
| --- | --- |
| Hersteller-Vorschläge | `Fabricant` ist weiterhin ein gewöhnliches Textfeld ohne die neuen Vorschläge. |
| Registrierungsland bei „Autre“ | `Autre` ist auswählbar, das zusätzliche Feld `immatriculation_autre` fehlt im Formular. |
| Antriebs-/Propellerart | Das neue Feld `Type d’hélice` mit Ligne d’arbre, Embase, IPS 360° und Jet moteur fehlt. |
| Tankanzahl und Summen | Nur Kraftstoff, Frischwasser und Lagerung sind sichtbar. Anzahl der Tanks sowie Gesamt-Kraftstoff und Gesamtkapazität fehlen in der Admin-Oberfläche. Die Berechnungslogik existiert bereits im gemeinsamen Modell. |
| Vollständige Ausstattungsauswahl | Drei freie Tag-Felder sind vorhanden, aber nicht die neuen vollständigen Vorschlagslisten und die vierte Ausstattungsgruppe `specs.tags.extras`. Der alte Abschnitt `Extras` mit Annexe/Remorque/Place au port ist eine andere Datenstruktur. |
| Kontaktvorbelegung beim Verkäuferwechsel | Im Admin-Code sind einfache Kontaktfelder vorhanden; eine Übernahme der Kontaktdaten des gewählten Verkäufers ist dort nicht angeschlossen. |

Diese fehlenden Formularanpassungen wurden bei dieser Prüfung nicht als erledigt gewertet.
Das Prüfergebnis ist eine Bestandsaufnahme; die E-Mail-Anmeldung ist die dabei ausdrücklich
angeforderte zusätzliche Korrektur.

## Änderungen an anderen Stellen

- Länderabschnitte, PNG-Flaggen, gleich hohe Inseratskarten und der untere Marketingtext:
  öffentliche Startseite `https://albabor.com/`.
- Neue Suche und Motorfilter: `https://albabor.com/annonces`.
- Neue technischen Felder und Ausstattung: öffentliches Erstellen/Bearbeiten unter
  `https://albabor.com/annonces/creer` bzw. `/annonces/{id}/modifier`.
- WhatsApp-Länderauswahl für Werbeanfragen: `https://albabor.com/publicite`.
- Privatmodus für den Verkäufer: `https://albabor.com/profil` bzw. dessen Bearbeitung.
- Android und iOS: Änderungen sind in den lokalen Projekten enthalten und wurden
  kompiliert. Sie wurden bisher nicht als neue Store-Versionen veröffentlicht.

Die Live-Prüfung öffnete das vorhandene Inserat ANTARES 8.8 und die leere
Benutzer-Erstellungsmaske. Es wurden ausschließlich Felder und Optionen angesehen;
keine Speicher-, Erstellungs-, Freigabe- oder Löschaktion wurde ausgelöst.

## Korrektur des Datenverlusts beim Admin-Speichern

Nach der anschließenden Fehlermeldung wurde der tatsächliche Filament-Speicherablauf
mit einer vollständigen künstlichen Anzeige reproduziert. Bereits eine reine
Preisänderung entfernte `immatriculation_autre`, `type_helice`, `nombre_reservoirs`,
`specs.tags.extras` und Angaben außerhalb des Formularschemas. Aus zwei Tanks und
420 Litern Gesamtkapazität wurden dadurch ein angenommener Tank und 270 Liter.
Die drei vorhandenen TagsInput-Felder wandelten außerdem Arrays in Kommatext um.

Die Edit-Seite übernimmt jetzt nur eingereichte Felder in die bestehenden
Spezifikationsgruppen. Nicht dargestellte und bedingt ausgeblendete Angaben bleiben
erhalten. Einzelne Feldwerte werden vollständig ersetzt: leere Listen, gelöschte
Felder, `0` und `false` werden als bewusste Änderungen gespeichert. Ausstattungslisten
werden als Arrays gespeichert; ältere Kommatexte werden beim Einlesen normalisiert.

Die folgenden zuvor fehlenden Adminfelder wurden ergänzt: Herstellervorschläge,
Freitextregistrierung bei „Autre“, Antriebsart, Tankanzahl mit automatisch angezeigten
Summen und alle vier Ausstattungsgruppen mit Vorschlägen und freier Eingabe.
Die separate Kontaktvorbelegung beim Verkäuferwechsel gehört nicht zu dieser
Speicherkorrektur und ist weiterhin offen.

Prüfung:

- Gesamte Laravel-Suite: **197 Tests, 801 Assertions erfolgreich**.
- Neun neue Tests führen echtes Filament-/Livewire-Erstellen und -Speichern aus:
  dreimaliges Wiederöffnen und Speichern, alle vier Kategorien, ausgeblendete Felder,
  einzelne Tag-Entfernung und leere Listen, Null/0/false, alte Kommatexte, Validierungsfehler,
  Anzeigen ohne optionale Daten, Händlerbearbeitung sowie öffentliche Website und API.
- Browser mit separater SQLite-Datenbank und künstlichem Admin: Nur Preis geändert,
  gespeichert und erneut geöffnet; alle ursprünglichen technischen Angaben erhalten.
  Danach Tankanzahl von 2 auf 3 geändert und nur „Table cockpit“ entfernt: öffentliche
  Anzeige zeigt 450 L Kraftstoff, 570 L Gesamtkapazität und die übrige Ausstattung.
- Fotos und Kontaktdaten bleiben beim Ändern des Preises in den Regressionstests erhalten.
- Pint für die neuen/kleinen PHP-Dateien und `git diff --check` erfolgreich.

Die Korrektur verhindert weitere Verluste. Bereits zuvor aus der Datenbank gelöschte
Werte werden dadurch nicht rekonstruiert; dafür sind die ursprünglichen Angaben oder
ein passender Datenbankstand erforderlich. Echte Live-Anzeigen wurden nicht testweise
gespeichert oder mit geschätzten Daten ergänzt.
