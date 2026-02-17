# API de Gestion de Profils - Test Technique HelloCSE

## Description

API REST développée avec Laravel 11 permettant la gestion de profils utilisateurs avec authentification via Laravel Sanctum.

## Technologies utilisées

- **Laravel 11**
- **PHP 8.2**
- **SQLite** (base de données)
- **Laravel Sanctum** (authentification API)
- **Composer 2.x**

## Installation

### Prérequis

- PHP >= 8.2
- Composer >= 2.0
- Extensions PHP : openssl, pdo_sqlite, mbstring, fileinfo, zip

### Étapes d'installation

1. **Cloner le repository**
```bash
git clone [URL_DU_REPO]
cd hellocse-api
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configurer l'environnement**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurer la base de données**

Le fichier `.env` est déjà configuré pour utiliser SQLite :
```env
DB_CONNECTION=sqlite
```

Créer le fichier de base de données :
```bash
# Windows PowerShell
New-Item database/database.sqlite

# Windows CMD
type nul > database\database.sqlite

# Linux/Mac
touch database/database.sqlite
```

5. **Lancer les migrations et seeders**
```bash
php artisan migrate --seed
```

6. **Créer le lien symbolique pour le stockage des images**
```bash
php artisan storage:link
```

7. **Lancer le serveur de développement**
```bash
php artisan serve
```

L'API sera accessible sur : `http://127.0.0.1:8000`


## Authentification

L'API utilise Laravel Sanctum pour l'authentification par tokens.

### Compte administrateur de test

```
Email: admin@test.com
Password: password
```

## Endpoints

### Routes publiques

#### 1. Lister les profils actifs
```http
GET /api/profiles
```

**Réponse :**
```json
[
    {
        "id": 1,
        "nom": "Dupont",
        "prenom": "Jean",
        "image": "profiles/xxx.jpg",
        "created_at": "2026-02-17T18:50:30.000000Z",
        "updated_at": "2026-02-17T18:50:30.000000Z"
    }
]
```

⚠️ **Note :** Le champ `statut` n'est pas retourné sur cet endpoint public.

---

### Routes protégées (nécessitent un token d'authentification)

**Header requis pour toutes les routes protégées :**
```http
Authorization: Bearer 1|8orv1lo9TvvbfQD4AgL1NuYUulrlavO3hsJsYHYlcf1d091f
Accept: application/json
```

#### 2. Créer un profil
```http
POST /api/profiles
Content-Type: multipart/form-data

nom: Martin
prenom: Jean
statut: actif (ou inactif, en_attente)
image: [fichier image JPG/PNG/GIF, max 10MB]
```

**Réponse :**
```json
{
    "message": "Profil créé avec succès",
    "profile": {
        "id": 21,
        "nom": "Martin",
        "prenom": "Jean",
        "image": "profiles/xxx.jpg",
        "statut": "actif",
        "created_at": "2026-02-17T19:00:00.000000Z",
        "updated_at": "2026-02-17T19:00:00.000000Z"
    }
}
```

#### 3. Voir un profil (avec statut)
```http
GET /api/profiles/{id}
```

**Réponse :**
```json
{
    "id": 1,
    "nom": "Dupont",
    "prenom": "Jean",
    "image": "profiles/xxx.jpg",
    "statut": "actif",
    "created_at": "2026-02-17T18:50:30.000000Z",
    "updated_at": "2026-02-17T18:50:30.000000Z"
}
```

#### 4. Modifier un profil
```http
PUT /api/profiles/{id}
Content-Type: application/json

{
    "nom": "Nouveau Nom",
    "prenom": "Nouveau Prénom",
    "statut": "en_attente" ou "actif" ou "inactif"
}
```

**Réponse :**
```json
{
    "message": "Profil modifié avec succès",
    "profile": {
        "id": 1,
        "nom": "Nouveau Nom",
        "prenom": "Nouveau Prénom",
        "statut": "en_attente",
    }
}
```

⚠️ **Note :** Tous les champs sont optionnels lors de la modification.

#### 5. Supprimer un profil
```http
DELETE /api/profiles/{id}
```

**Réponse :**
```json
{
    "message": "Profil supprimé avec succès"
}
```


## Validation des données

### Création de profil (StoreProfileRequest)

| Champ | Type | Règles | Description |
|-------|------|--------|-------------|
| nom | string | required, max:255 | Nom du profil |
| prenom | string | required, max:255 | Prénom du profil |
| image | file | required, image, mimes:jpeg,png,jpg,gif, max:10240 | Image du profil (max 10MB) |
| statut | string | required, in:inactif,en_attente,actif | Statut du profil |


## Seeders et Factories

### Lancer les seeders
```bash
php artisan db:seed
```

Cela créera :
- **1 administrateur de test** (admin@test.com / password)
- **5 administrateurs aléatoires**
- **20 profils aléatoires** avec différents statuts

### Créer des données spécifiques
```bash
# Uniquement les administrateurs
php artisan db:seed --class=AdministratorSeeder

# Uniquement les profils
php artisan db:seed --class=ProfileSeeder
```

## Tests

### Tester avec Postman

1. Importer la collection Postman (si fournie)
2. Créer une variable d'environnement `token`
3. Se connecter via `/api/login` et copier le token
4. Utiliser `1|8orv1lo9TvvbfQD4AgL1NuYUulrlavO3hsJsYHYlcf1d091f` dans les headers des requêtes protégées

### Tester avec cURL

**Lister les profils publics :**
```bash
curl http://127.0.0.1:8000/api/profiles
```

**Créer un profil (avec token) :**
```bash
curl -X POST http://127.0.0.1:8000/api/profiles \
  -H "Authorization: Bearer 1|8orv1lo9TvvbfQD4AgL1NuYUulrlavO3hsJsYHYlcf1d091f" \
  -H "Accept: application/json" \
  -F "nom=Martin" \
  -F "prenom=Jean" \
  -F "statut=actif" \
  -F "image=@/chemin/vers/image.jpg"
```

### Lancer les tests unitaires
```bash
# Tous les tests
php artisan test

# Tests avec détails
php artisan test --testdox

# Tests spécifiques
php artisan test tests/Feature/AuthTest.php
php artisan test tests/Feature/ProfileTest.php
```

### Tests implémentés

Le projet inclut **13 tests unitaires** couvrant toutes les fonctionnalités principales.

**Tests d'authentification (AuthTest) :**
- Connexion avec identifiants valides
- Connexion avec identifiants invalides
- Validation des champs requis

**Tests de profils (ProfileTest) :**
- Listing des profils actifs uniquement
- Vérification que le champ statut n'est pas retourné sur l'endpoint public
- Création de profil (avec/sans authentification)
- Validation des données (nom requis, statut valide)
- Affichage d'un profil avec son statut
- Modification d'un profil
- Suppression d'un profil

### Résultat des tests
```
PASS  Tests\Feature\AuthTest
✓ login with valid credentials
✓ login with invalid credentials
✓ login requires email
✓ logout 

PASS  Tests\Feature\ProfileTest
✓ list only active profiles
✓ public endpoint does not return status
✓ create profile requires authentication
✓ create profile with authentication
✓ profile creation requires nom
✓ profile creation rejects invalid status
✓ show profile with status
✓ update profile
✓ delete profile

Tests:  13 passed
```


## Structure du projet

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php      # Authentification
│   │       └── ProfileController.php   # Gestion des profils
│   └── Requests/
│       ├── StoreProfileRequest.php     # Validation création
│       └── UpdateProfileRequest.php    # Validation modification
├── Models/
│   ├── Administrator.php               # Modèle Administrateur
│   └── Profile.php                     # Modèle Profil
database/
├── factories/
│   ├── AdministratorFactory.php        # Factory Administrateur
│   └── ProfileFactory.php              # Factory Profil
├── migrations/
│   ├── xxxx_create_administrators_table.php
│   └── xxxx_create_profiles_table.php
└── seeders/
    ├── AdministratorSeeder.php
    ├── ProfileSeeder.php
    └── DatabaseSeeder.php
routes/
└── api.php                             # Routes API
```

## Fonctionnalités implémentées
 
- **Endpoint protégé** pour créer un profil  
- **Endpoint public** pour lister les profils actifs (sans le champ statut)  
- **Endpoint protégé** pour modifier/supprimer un profil  
- **Validation des données** via FormRequest  
- **Upload d'images** fonctionnel  
- **Gestion des statuts** (inactif, en_attente, actif)  
- **Seeders et factories** pour générer des données de test  
- Code **commenté** et **structuré**  


## Commandes utiles

```bash
# Réinitialiser la base de données
php artisan migrate:fresh --seed

# Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Lister toutes les routes
php artisan route:list

# Accéder à Tinker (console interactive)
php artisan tinker
```


## Notes techniques

### Choix techniques

- **SQLite** : Base de données légère, idéale pour le développement et les tests
- **Sanctum** : Solution d'authentification simple et efficace pour les API
- **FormRequest** : Validation centralisée et réutilisable
- **Storage public** : Gestion des fichiers via le système de stockage Laravel

### Sécurité

- Mots de passe hashés automatiquement
- Tokens d'authentification gérés par Sanctum
- Validation stricte des fichiers uploadés (type, taille)

## Auteur & Licence

Ce projet est développé par Yvan Ghomman et uniquement à des fins de test technique pour HelloCSE.