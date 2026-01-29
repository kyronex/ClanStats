#!/bin/bash
set -e

echo "🚀 Démarrage ClanStats..."

# Symfony
if [ ! -f "composer.json" ]; then
    echo "📁 Création projet Symfony 6.4..."
    composer create-project symfony/skeleton:"^6.4" temp_project
    mv temp_project/* ./; mv temp_project/.* ./ 2>/dev/null || true; rm -rf temp_project
    composer require symfony/asset:6.4.* symfony/form:6.4.* symfony/http-client:6.4.* symfony/monolog-bundle symfony/security-bundle:6.4.* symfony/serializer:6.4.* symfony/twig-bundle:6.4.* symfony/validator:6.4.* phpdocumentor/reflection-docblock:^5.6 phpstan/phpdoc-parser:^2.1
else
    echo "✅ Projet Symfony détecté"
    # ✅ CHANGEMENT 1 : Vérification fichier précis au lieu du dossier
    if [ ! -f "vendor/autoload_runtime.php" ]; then
        echo "📦 Installation Composer..."
        composer install --no-interaction --optimize-autoloader
    else
        echo "✅ Vendor OK"
    fi
fi

# React
if [ ! -f ".react-configured" ]; then
    echo "⚛️  Configuration React 18..."
    rm -rf node_modules/.cache public/build/* 2>/dev/null || true
    setup-react
else
    echo "✅ React configuré"

    # ✅ CHANGEMENT 2 : Vérification React au lieu de Babel
    if [ ! -d "node_modules" ] || [ ! -f "node_modules/react/package.json" ]; then
        echo "📦 Installation npm..."
        npm install
    else
        echo "✅ Node modules OK"
    fi

    if ! node -e "require('react-dom/client')" 2>/dev/null; then
        echo "🔄 Upgrade React 18..."
        npm install react@^18.2.0 react-dom@^18.2.0
    fi

    # ✅ CHANGEMENT 3 : Build seulement si assets manquants
    if [ ! -f "public/build/app.js" ] || [ ! -f "public/build/manifest.json" ]; then
        echo "🏗️ Build assets..."
        rm -rf node_modules/.cache 2>/dev/null || true

        if npm run dev; then
            echo "✅ Build dev OK"
        else
            echo "⚠️  Fallback build production..."
            npm run build || { echo "🔧 Rebuild complet..."; rm -rf public/build/* node_modules; npm install; npm run build; }
        fi
    else
        echo "✅ Assets déjà présents"
    fi

    if [ -f "public/build/app.js" ]; then
        js_size=$(wc -c < public/build/app.js 2>/dev/null || echo "0")
        [ "$js_size" -lt 1000 ] && echo "⚠️  Fichier suspect - rebuild..." && npm run build
        echo "✅ Assets générés (${js_size}b)"
    else
        echo "❌ app.js manquant - rebuild forcé..."
        rm -rf public/build/* node_modules/.cache; npm run build
    fi
fi

# Webpack Dev Server
echo "🔥 Démarrage webpack dev server..."
touch /var/log/webpack.log; chmod 666 /var/log/webpack.log
[ ! -f "public/build/app.js" ] && npm run dev

npm run dev-server > /var/log/webpack.log 2>&1 &
WEBPACK_PID=$!

cleanup() { kill $WEBPACK_PID 2>/dev/null || true; exit 0; }
trap cleanup SIGTERM SIGINT

echo "⏳ Attente webpack dev server..."
timeout=30; connection_attempts=0

while [ $timeout -gt 0 ]; do
    connection_attempts=$((connection_attempts + 1))
    curl -s -f http://127.0.0.1:8081 >/dev/null 2>&1 && { echo "✅ Dev server prêt ($connection_attempts tentatives)"; break; }
    [ $((connection_attempts % 10)) -eq 0 ] && echo "🔄 Tentative $connection_attempts..."
    sleep 2; timeout=$((timeout-2))
done

[ $timeout -le 0 ] && echo "⚠️  Dev server lent - continuons..."

echo "🌐 Démarrage Apache..."
echo ""
echo "🎯 APPLICATION PRÊTE:"
echo "   📱 App: http://localhost"
echo "   🗃️  DB:  http://localhost:8083"
echo "   🔗 Ngrok: http://localhost:4040"
echo "   📝 Logs: docker exec -it clan_stats_web tail -f /var/log/webpack.log"
echo ""

exec apache2-foreground
