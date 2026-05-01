# Farmers Market API — Backend Laravel

API RESTful pour la plateforme de gestion des ventes agricoles en Côte d'Ivoire.  
**Laravel 12 · PHP 8.2 · PostgreSQL (Render) / MySQL (Docker local) · Sanctum**

---

## Déploiement sur Render (production)

### Prérequis
- Compte GitHub avec ce dépôt poussé en **public**
- Compte [Render](https://render.com) (gratuit)

### Étapes

**1. Créer le Blueprint Render**

Le fichier `render.yaml` à la racine configure tout automatiquement.

- Sur Render : **New +** → **Blueprint**
- Connecter GitHub → sélectionner ce dépôt (`farmers-market-api`)
- Render détecte `render.yaml` et affiche : un **Web Service** + une **PostgreSQL DB**
- Cliquer **Apply** → confirmer la création

**2. Attendre le premier déploiement**

Le premier build prend **5 à 10 minutes** (téléchargement des dépendances PHP, migrations, seeding).  
Suivre les logs dans Render → farmers-market-api → **Logs**.

Quand on voit :
```
✅ Setup complet — données de démo seedées
🚀 Serveur démarré sur le port 10000
```
L'API est prête.

**3. Récupérer l'URL de l'API**

Dans Render → farmers-market-api → Settings :  
```
https://farmers-market-api-xxxx.onrender.com
```
**Conserver cette URL** — elle sera nécessaire pour configurer le frontend.

**4. Vérifier que l'API répond**

```bash
curl https://farmers-market-api-xxxx.onrender.com/api/health
# Réponse : {"status":"ok"}
```

**5. Tester avec Postman**

```
POST https://farmers-market-api-xxxx.onrender.com/api/login
Body: { "email": "operator@farmersmarket.ci", "password": "password" }
```

> **Note Render free tier :** Le service se met en veille après 15 min d'inactivité.  
> La première requête après veille prend ~30 secondes. C'est normal.

---

## Développement local avec Docker

```bash
# Depuis le dossier farmers_market_app :
docker compose up --build
# API disponible sur http://localhost:8080
```

Voir le README de `farmers_market_app` pour les détails complets.

---

## Développement local sans Docker

### Prérequis
- PHP 8.2 + Composer 2.x
- MySQL 8.0

### Installation

```bash
composer install
cp .env.example .env

# Configurer .env (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)

php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
# → http://localhost:8000
```

---

## Tests

Les tests utilisent **SQLite en mémoire** — aucune base de données externe nécessaire.

```bash
# Local
composer install
php artisan test

# Via Docker (depuis farmers_market_app/)
docker compose -f docker-compose.test.yml run --rm api-test
```

Résultat attendu :
```
Tests:    26 passed
Duration: ~3s
```

| Suite | Fichier | Couverture |
|-------|---------|-----------|
| Auth | `tests/Feature/AuthTest.php` | Login, logout, token, rôles |
| Farmers | `tests/Feature/FarmerTest.php` | CRUD, recherche, dettes |
| Transactions | `tests/Feature/TransactionTest.php` | Cash, crédit, limite de crédit |
| Repayments | `tests/Feature/RepaymentTest.php` | FIFO, partiel, taux commodity |

---

## Comptes de démonstration (seedés automatiquement)

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Admin | admin@farmersmarket.ci | password |
| Supervisor | supervisor@farmersmarket.ci | password |
| Operator 1 | operator1@farmersmarket.ci | password |
| Operator 2 | operator2@farmersmarket.ci | password |

---

## Endpoints principaux

| Méthode | Endpoint | Accès |
|---------|----------|-------|
| GET | `/api/health` | public |
| POST | `/api/login` | public |
| POST | `/api/logout` | authentifié |
| GET | `/api/farmers/search?q=` | tous rôles |
| GET/POST | `/api/farmers` | tous rôles |
| GET | `/api/farmers/{id}/debts` | tous rôles |
| GET | `/api/products` | tous rôles |
| GET | `/api/categories` | tous rôles |
| POST | `/api/transactions` | tous rôles |
| POST | `/api/repayments` | tous rôles |
| POST/PUT/DELETE | `/api/products` | admin, supervisor |
| POST/PUT/DELETE | `/api/categories` | admin, supervisor |
| GET/POST | `/api/users` | admin |

---

## Architecture

```
app/
├── Http/
│   ├── Controllers/    # Auth, Farmer, Product, Category, Transaction, Repayment, User
│   ├── Middleware/     # RoleMiddleware (admin / supervisor / operator)
│   └── Requests/       # Validation Form Request sur tous les endpoints
├── Models/             # User, Farmer, Product, Category, Transaction, Debt, Repayment, Setting
database/
├── migrations/         # Schéma complet (8 tables)
└── seeders/            # Données de démo réalistes (6 seeders)
routes/
└── api.php             # Routes avec contrôle d'accès par rôle
render.yaml             # Blueprint déploiement Render (one-click)
```
