# Corrections du 7 septembre 2026

Source : les 15 pages de « App Modification 7 SEP 2026.pdf ».

## Site et API

- [x] Champs de connexion lisibles sur fond clair, y compris avec le remplissage automatique.
- [x] Photos des annonces et indicateurs neutres en cas d'image absente.
- [x] Contact sous le vendeur, partage et messagerie ; médiation via l'administration.
- [x] Recherche compacte : catégorie, prix, dimensions, emplacement, année et fabricant.
- [x] Filtres moteur : propulsion, carburant, nombre, puissance et type d'hélice.
- [x] Annonces par pays, drapeaux et liens vers le filtre correspondant.
- [x] Suggestions de fabricants et saisie libre.
- [x] Immatriculation polonaise et pays à préciser pour « Autre ».
- [x] Types d'hélice : ligne d'arbre, embase, IPS 360° et jet moteur.
- [x] Calcul des réservoirs : 2 × 150 L + 100 L + 20 L = 420 L, anciennes données comprises.
- [x] Listes complètes de sécurité, confort, électronique et extras, avec valeurs personnalisées.
- [x] « Offert » désigne une offre déjà reçue, avec son montant.
- [x] Coordonnées préremplies depuis le profil ; valeurs conservées lors de la modification.
- [x] Publication privée et protection des coordonnées ; notifications de messages par e-mail.
- [x] Pinceau de floutage supplémentaire « Très petit ».
- [x] Consultation, modification et suppression des annonces en attente par leur propriétaire.
- [x] Choix de paiement simplifiés ; coordonnées centralisées pour le site et les applications.
- [x] Présentation marketing déplacée en bas de l'accueil ; navigation adaptée aux tablettes.

## Validation

- 178 tests Laravel réussis, 618 assertions, dont 12 nouveaux tests de régression.
- Build Vite réussi ; manifeste et CSS compilé inclus.
- Vérification visuelle du site en 390 et 768 pixels : connexion, création, modification,
  contact, filtres, détails, annonces en attente et choix de paiement.
- `git diff --check` réussi.

## Mise en service

- Nouveaux endpoints : `GET /api/v1/listings/countries` et
  `GET /api/v1/settings/payment-methods`.
- Aucune nouvelle migration dans ce lot.
- Les notifications utilisent la connexion mail et la file Laravel configurées.
  Un worker doit traiter la file pour envoyer les e-mails.
- Les modifications natives Android et iOS sont dans leurs projets séparés et
  nécessitent leur propre publication, après la mise en service de cette API.
- Aucun paiement réel ni envoi externe d'e-mail n'a été effectué pendant les tests.
