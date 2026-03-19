# GestStock — Gestion de stock & Facturation

Application web PHP de gestion de stock et de facturation (factures en MAD).

---

## Stack technique

| Couche     | Technologie                     |
|------------|---------------------------------|
| Backend    | PHP 8.1+ (PDO, sessions)        |
| Base de données | MySQL 5.7+ / MariaDB 10+   |
| Frontend   | Bootstrap 5.3, Vanilla JS       |
| Architecture | MVC maison, routeur front-end |
| PDF        | Dompdf (optionnel)              |

---

## Installation rapide (XAMPP / WAMP / Laragon)

### 1. Copier les fichiers

Placez le dossier `facturation/` dans votre racine web :

- **XAMPP** : `C:/xampp/htdocs/facturation/`
- **WAMP**  : `C:/wamp64/www/facturation/`
- **Laragon** : `C:/laragon/www/facturation/`
- **macOS XAMPP** : `/Applications/XAMPP/htdocsFacturation/`

### 2. Créer la base de données

1. Ouvrez **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Importez le fichier `database/schema.sql`
   - Onglet **Importer** → sélectionnez le fichier → **Exécuter**
   - Cela crée la base `facturation` avec toutes les tables et des données démo

### 3. Configurer la connexion

Ouvrez `config/database.php` et adaptez :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'facturation');
define('DB_USER', 'root');      // Votre utilisateur MySQL
define('DB_PASS', '');          // Votre mot de passe MySQL
```

### 4. Activer mod_rewrite (Apache)

Le fichier `.htaccess` est inclus. Assurez-vous que `mod_rewrite` est activé :

- **XAMPP** : Dans `httpd.conf`, décommentez `LoadModule rewrite_module`
- **Laragon** : Activé par défaut

### 5. Accéder à l'application

```
http://localhost/facturation/
```

---

## Connexion démo

| Email                   | Mot de passe |
|-------------------------|-------------|
| admin@facturation.ma    | admin123    |

---

## Structure des dossiers

```
facturation/
├── config/
│   ├── app.php           # Configuration + helpers globaux
│   └── database.php      # Connexion PDO (singleton)
├── controllers/
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── ProductController.php
│   ├── ClientController.php
│   ├── InvoiceController.php
│   ├── StockController.php
│   └── CategoryController.php
├── models/
│   ├── User.php
│   ├── Product.php
│   ├── Client.php
│   ├── Invoice.php
│   ├── StockMovement.php
│   └── Category.php
├── views/
│   ├── layout/           # header, footer, delete_modal
│   ├── auth/             # login
│   ├── dashboard/        # tableau de bord
│   ├── products/         # CRUD produits
│   ├── clients/          # CRUD clients
│   ├── invoices/         # CRUD factures + impression + PDF
│   ├── stock/            # Vue stock + ajustements + historique
│   └── errors/           # 404
├── public/
│   ├── css/app.css
│   └── js/app.js
├── database/
│   └── schema.sql        # Schéma complet + données démo
├── vendor/               # (Dompdf si installé)
├── .htaccess
├── index.php             # Front controller / routeur
└── README.md
```

---

## Fonctionnalités

### Authentification
- Connexion / déconnexion sécurisée
- Mots de passe hachés en bcrypt (cost 12)
- Sessions PHP avec `session_regenerate_id()`

### Produits
- CRUD complet
- Champs : nom, SKU, catégorie, prix de vente HT, prix d'achat, quantité, stock minimum, description
- Badges de statut de stock : **En stock / Stock faible / Rupture**

### Gestion du stock
- Vue d'ensemble avec alertes stock faible
- Ajustements manuels (entrée / sortie) avec note
- Historique complet des mouvements par produit
- Mise à jour automatique lors de la création/modification/suppression de factures

### Clients
- CRUD complet
- Champs : nom, adresse, ICE, téléphone, email
- Protégé contre la suppression si des factures existent

### Factures
- CRUD complet
- Numérotation automatique : `FAC-AAAA-NNN`
- Ajout/suppression dynamique de lignes (JavaScript)
- Calcul automatique HT / TVA / TTC
- Statuts : Brouillon / Envoyée / Payée / Annulée
- **Intégrité du stock** :
  - Création → décrémente le stock
  - Modification → restaure l'ancien stock puis décrémente le nouveau
  - Suppression → restaure entièrement le stock
- Page d'impression propre (CSS print media)
- Export PDF (si Dompdf installé)

### Tableau de bord
- Statistiques : total produits, clients, factures, chiffre d'affaires
- Alertes stock faible
- Dernières factures

---

## Export PDF (optionnel)

Pour activer l'export PDF, installez Dompdf via Composer :

```bash
cd facturation/
composer require dompdf/dompdf
```

Sans Composer, le bouton PDF redirige vers la page d'impression navigateur.

---

## Sécurité

- Toutes les requêtes SQL utilisent des **requêtes préparées PDO** (protection injection SQL)
- Toutes les sorties HTML utilisent `htmlspecialchars()` via `e()` (protection XSS)
- Authentification requise sur toutes les pages via `requireAuth()`
- Les mots de passe sont hachés avec `password_hash()` / `password_verify()`
- Les transactions MySQL garantissent l'intégrité du stock

---

## Données démo incluses

- 1 utilisateur admin
- 5 catégories
- 12 produits
- 5 clients
- 5 factures (statuts variés)
- Mouvements de stock initiaux et liés aux factures
