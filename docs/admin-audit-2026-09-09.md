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

## Im Admin-Inseratsformular noch fehlend

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
