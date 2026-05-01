#!/bin/sh
set -e

echo ""
echo "============================================"
echo "   Farmers Market API — Startup"
echo "============================================"
echo ""

# ─── 1. Détecter l'environnement ─────────────────────────────────────
# Render fournit DATABASE_URL (PostgreSQL) + PORT
# Docker local fournit DB_HOST / DB_PORT (MySQL)
IS_RENDER=false
if [ -n "${DATABASE_URL}" ]; then
    IS_RENDER=true
    echo "🌐 Environnement détecté : Render (PostgreSQL)"
else
    echo "🐳 Environnement détecté : Docker local (MySQL)"
fi

# ─── 2. Écrire le .env depuis les variables d'environnement ──────────
echo "📝 Configuration de l'environnement..."
cat > /var/www/.env << ENVFILE
APP_NAME="Farmers Market API"
APP_ENV=${APP_ENV:-local}
APP_KEY=${APP_KEY:-}
APP_DEBUG=${APP_DEBUG:-true}
APP_URL=${APP_URL:-http://localhost}

LOG_CHANNEL=stack
LOG_LEVEL=debug

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS:-localhost,127.0.0.1}
ENVFILE

# PostgreSQL (Render) : DATABASE_URL est mappé vers DB_URL (convention Laravel 11+)
if [ "$IS_RENDER" = "true" ]; then
    cat >> /var/www/.env << ENVFILE
DB_CONNECTION=${DB_CONNECTION:-pgsql}
DB_URL=${DATABASE_URL}
ENVFILE
else
    # MySQL (Docker local)
    cat >> /var/www/.env << ENVFILE
DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST:-db}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-farmers_market}
DB_USERNAME=${DB_USERNAME:-farmer_user}
DB_PASSWORD=${DB_PASSWORD:-farmer_pass}
ENVFILE
fi

# ─── 3. Générer APP_KEY si absent ────────────────────────────────────
if grep -q "^APP_KEY=$" /var/www/.env; then
    echo "🔑 Génération de la clé d'application..."
    php artisan key:generate --force --no-interaction
fi

# ─── 4. Attendre MySQL (Docker uniquement) ───────────────────────────
if [ "$IS_RENDER" = "false" ]; then
    echo "⏳ Attente de MySQL sur ${DB_HOST:-db}:${DB_PORT:-3306}..."
    MAX=40
    n=0
    until nc -z "${DB_HOST:-db}" "${DB_PORT:-3306}" 2>/dev/null; do
        n=$((n+1))
        if [ "$n" -ge "$MAX" ]; then
            echo "❌ Timeout : MySQL inaccessible après ${MAX} tentatives."
            exit 1
        fi
        echo "   Tentative $n/$MAX — retry dans 3s..."
        sleep 3
    done
    echo "✅ MySQL up — attente initialisation..."
    sleep 4
fi

# ─── 5. Migration + Seeding (avec retry) ─────────────────────────────
echo "🗄️  Migrations et données de démo..."
n=0
until php artisan migrate:fresh --seed --force --no-interaction 2>&1; do
    n=$((n+1))
    if [ "$n" -ge 10 ]; then
        echo "❌ Migration échouée après 10 tentatives."
        exit 1
    fi
    echo "   Tentative $n/10 échouée — retry dans 5s..."
    sleep 5
done

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  ✅ Setup complet — données de démo seedées"
echo "  🚀 Serveur démarré sur le port ${PORT:-8000}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# ─── 6. Démarrer le serveur ──────────────────────────────────────────
# Render injecte $PORT ; Docker utilise 8000 par défaut
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
