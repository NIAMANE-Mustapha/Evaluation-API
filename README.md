# Evaluation API Platform - Gestion d'un Bar

Application de gestion d'un bar développée avec **Symfony 8** et **API Platform 5**.

## Membres du groupe

- **Mustapha NIAMANE** (@NIAMANE-Mustapha)

---

## Fonctionnalités et Rôles

Le système gère 4 profils d'utilisateurs avec des droits spécifiques :

| Rôle | Identifiant exemple | Permissions |
|---|---|---|
| **Patron** (`ROLE_PATRON`) | `patron` | CRUD complet sur le personnel (Serveurs et Barmans), consultation de toutes les commandes et filtrage par période. |
| **Barman** (`ROLE_BARMAN`) | `barman` | CRUD complet sur les Boissons et Médias (upload d'images), consultation des commandes en cours, attribution d'une commande à soi-même, passage du statut à « prête », filtrage des commandes par période. |
| **Serveur** (`ROLE_SERVEUR`) | `mustapha-serveur` | Création de commande (attribution automatique du serveur connecté et date/heure automatique), modification des boissons d'une commande non payée, enregistrement du paiement, consultation du détail d'une commande, filtrage par période. |
| **Client** (Non authentifié) | *Anonyme* | Consultation de la carte des boissons et du détail d'une boisson. |

---

## Prérequis

- **PHP** >= 8.4 avec extensions `pdo_mysql`, `openssl`, `sodium`
- **Composer**
- **MySQL** ou **MariaDB**
- **Symfony CLI** (recommandé)

---

## Installation et Démarrage

### 1. Cloner le projet
```bash
git clone https://github.com/NIAMANE-Mustapha/Evaluation-API.git
cd Evaluation-API
```

### 2. Installer les dépendances
```bash
composer install
```

### 3. Configuration de la base de données
Modifier la variable `DATABASE_URL` dans le fichier `.env` ou créer un fichier `.env.local` :
```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/bar_evaluation?serverVersion=8.0.32&charset=utf8mb4"
```

### 4. Génération des clés JWT
Générer la paire de clés privée/publique pour l'authentification JWT :
```bash
php bin/console lexik:jwt:generate-keypair
```

### 5. Création de la base et exécution des migrations
```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
```

### 6. Chargement des données de test (Fixtures)
Cette commande crée les comptes de démonstration, les boissons avec photos et des commandes d'exemple :
```bash
php bin/console doctrine:fixtures:load --no-interaction
```

### 7. Lancement du serveur de développement
```bash
symfony serve
# ou
php -S 127.0.0.1:8000 -t public
```

L'API et la documentation Swagger sont accessibles sur : **http://127.0.0.1:8000/api**

---

## Comptes de test (Fixtures)

| Rôle | Nom d'utilisateur (`username`) | Mot de passe |
|---|---|---|
| **Patron** | `patron` | `patron123` |
| **Barman** | `barman` | `barman123` |
| **Serveur** | `mustapha-serveur` | `serveur123` |

---

## Collection Postman

Le fichier d'export de la collection Postman est disponible à la racine du dépôt :  
`Evaluation_Bar_API.postman_collection.json`

### Utilisation de la collection :
1. Importer `Evaluation_Bar_API.postman_collection.json` dans Postman.
2. Exécuter la requête de connexion correspondant au profil souhaité (`Connexion Patron`, `Connexion Barman` ou `Connexion Serveur`).
3. Le token JWT est automatiquement extrait et enregistré dans les variables de collection (`token_patron`, `token_barman`, `token_serveur`).
4. Toutes les requêtes suivantes sont pré-configurées avec l'en-tête `Authorization: Bearer <token>` approprié.

### Structure des dossiers Postman :
1. **1. Authentification (JWT)** : Récupération des tokens via `/api/login_check`.
2. **2. Patron - Gestion du personnel (Users)** : CRUD complet sur les serveurs et barmans.
3. **3. Barman - Boissons, Médias & Commandes en cours** : Upload d'image, CRUD boissons, filtrage `?status=en cours de préparation`, prise en charge et statut `prête`.
4. **4. Serveur - Gestion des commandes & paiements** : Création avec serveur auto-assigné, mise à jour des boissons si non payée, encaissement (`status: payée`).
5. **5. Filtre par période** : Filtrage des commandes avec `createdAt[after]` et `createdAt[before]`.
