#!/bin/bash
set -e

echo "🚀 Démarrage ClanStats OPTIMISÉ..."

# Vérifier si le projet Symfony existe
if [ ! -f "composer.json" ]; then
    echo "📁 Création nouveau projet Symfony..."
    composer create-project symfony/skeleton:"^6.4" temp_project
    mv temp_project/* ./
    mv temp_project/.* ./ 2>/dev/null || true
    rm -rf temp_project

    echo "📦 Installation dépendances Symfony..."
    composer require \
        symfony/asset:6.4.* \
        symfony/form:6.4.* \
        symfony/http-client:6.4.* \
        symfony/monolog-bundle \
        symfony/security-bundle:6.4.* \
        symfony/serializer:6.4.* \
        symfony/twig-bundle:6.4.* \
        symfony/validator:6.4.* \
        phpdocumentor/reflection-docblock:^5.6 \
        phpstan/phpdoc-parser:^2.1
else
    echo "✅ Projet Symfony existant détecté"

    if [ ! -d "vendor" ]; then
        echo "📦 Installation dépendances Composer..."
        composer install
    fi
fi

# ✅ Configuration React optimisée
if [ ! -f ".react-configured" ]; then
    echo "🔧 Installation React 18+ OPTIMISÉE..."

    # ✅ Nettoyage préventif
    echo "🧹 Nettoyage préventif..."
    rm -rf node_modules/.cache public/build/* 2>/dev/null || true

    setup-react
else
    echo "✅ React déjà configuré"

    # ✅ Vérification React 18+ spécifique
    if [ ! -d "node_modules" ] || [ ! -f "node_modules/@babel/plugin-transform-modules-commonjs/package.json" ]; then
        echo "📦 Installation/Réparation dépendances npm..."

        # ✅ Installation des dépendances manquantes
        if [ ! -f "node_modules/@babel/plugin-transform-modules-commonjs/package.json" ]; then
            echo "🔧 Installation dépendance ES6 manquante..."
            npm install --save-dev @babel/plugin-transform-modules-commonjs
        fi

        npm install
    fi

    # ✅ Vérification React 18+ createRoot disponible
    echo "🔍 Vérification React 18+ createRoot API..."
    if ! node -e "require('react-dom/client')" 2>/dev/null; then
        echo "⚠️  react-dom/client manquant - Upgrade vers React 18+..."
        npm install react@^18.2.0 react-dom@^18.2.0
    fi

    # ✅ NOUVEAU: Build avec vérification robuste
    echo "🏗️  Build assets React 18+ optimisé..."

    # Nettoyage cache avant build
    rm -rf node_modules/.cache 2>/dev/null || true

    # Build avec gestion d'erreur
    if npm run dev; then
        echo "✅ Build dev React 18+ réussi!"
    else
        echo "⚠️  Build dev échoué - Tentative build production..."
        if npm run build; then
            echo "✅ Build production React 18+ réussi!"
        else
            echo "❌ Build échoué - Mode de récupération..."
            echo "🔧 Reconstruction complète React 18+..."
            rm -rf public/build/* node_modules
            npm install
            npm run build
        fi
    fi

    # ✅ NOUVEAU: Vérification post-build
    echo "🔍 Vérification assets React 18+ générés..."
    if [ -f "public/build/app.js" ]; then
        echo "✅ Assets JS/CSS React 18+ générés avec succès!"

        # Vérification taille fichiers (détection erreurs)
        js_size=$(wc -c < public/build/app.js 2>/dev/null || echo "0")
        if [ "$js_size" -lt 1000 ]; then
            echo "⚠️  Fichier app.js suspect (taille: ${js_size}b) - Rebuild..."
            npm run build
        else
            echo "✅ Fichier app.js OK (taille: ${js_size}b)"
        fi

        ls -la public/build/*.js public/build/*.css 2>/dev/null || echo "⚠️  Certains assets manquent"
    else
        echo "❌ ERREUR: app.js non généré!"
        echo "🚨 Mode RÉCUPÉRATION activé..."

        rm -rf public/build/* node_modules/.cache
        npm run build --verbose

        if [ ! -f "public/build/app.js" ]; then
            echo "💥 ÉCHEC CRITIQUE - Vérifiez webpack.config.js"
            echo "📋 Contenu public/build/:"
            ls -la public/build/ || echo "Dossier inexistant"
        fi
    fi
fi

# ✅ Démarrage dev server optimisé
echo "🔥 Démarrage webpack dev server React 18+ optimisé..."

# Création du fichier de log
touch /var/log/webpack.log
chmod 666 /var/log/webpack.log

# ✅ NOUVEAU: Pré-vérification avant démarrage dev server
if [ ! -f "public/build/app.js" ]; then
    echo "⚠️  Pas d'assets physiques - Build rapide React 18+..."
    npm run dev
fi

# Démarrage webpack dev server avec logging amélioré
npm run dev-server > /var/log/webpack.log 2>&1 &
WEBPACK_PID=$!

# Fonction de nettoyage
cleanup() {
    echo "🧹 Arrêt des services..."
    kill $WEBPACK_PID 2>/dev/null || true
    exit 0
}

trap cleanup SIGTERM SIGINT

# ✅ NOUVEAU: Attente webpack avec diagnostic React 18+
echo "⏳ Attente webpack dev server React 18+ (diagnostic actif)..."
timeout=30
connection_attempts=0

while [ $timeout -gt 0 ]; do
    connection_attempts=$((connection_attempts + 1))

    if curl -s -f http://127.0.0.1:8081 >/dev/null 2>&1; then
        echo "✅ Webpack dev server React 18+ prêt sur http://127.0.0.1:8081!"
        echo "🔍 Tentatives de connexion: $connection_attempts"
        break
    fi

    # ✅ Diagnostic en cours d'attente
    if [ $((connection_attempts % 10)) -eq 0 ]; then
        echo "🔄 Tentative $connection_attempts - Encore $timeout secondes..."

        # Vérification logs webpack
        if [ -f "/var/log/webpack.log" ]; then
            tail_output=$(tail -3 /var/log/webpack.log 2>/dev/null || echo "")
            if [ -n "$tail_output" ]; then
                echo "📋 Logs webpack React 18+ récents:"
                echo "$tail_output" | sed 's/^/   /'
            fi
        fi
    fi

    sleep 2
    timeout=$((timeout-2))
done

if [ $timeout -le 0 ]; then
    echo "⚠️  Webpack dev server lent - mais continuons..."
    echo "📋 Diagnostic final React 18+:"
    echo "   🔍 Processus webpack: $(pgrep -f webpack || echo 'non trouvé')"
    echo "   📝 Logs: docker exec -it clan_stats_web tail -f /var/log/webpack.log"
    echo "   ⚛️  React version: $(node -e "console.log(require('react/package.json').version)" 2>/dev/null || echo 'N/A')"
    echo "   🔧 createRoot API: $(node -e "require('react-dom/client'); console.log('OK')" 2>/dev/null || echo 'ERREUR')"
fi

echo "🌐 Démarrage Apache..."
echo ""
echo "🎯 APPLICATION REACT 18+ OPTIMISÉE PRÊTE:"
echo "   📱 App: http://localhost"
echo "   🗃️  DB:  http://localhost:8083 (phpMyAdmin)"
echo "   🔗 Ngrok: http://localhost:4040 (dashboard)"
echo "   🔥 Hot Reload: React 18+ createRoot API"
echo ""
echo "💡 Diagnostic webpack React 18+:"
echo "   docker exec -it clan_stats_web tail -f /var/log/webpack.log"
echo ""
echo "🚀 AMÉLIORATIONS REACT 18+:"
echo "   ✅ createRoot API (react-dom/client)"
echo "   ✅ Concurrent Features ready"
echo "   ✅ Auto-batching des updates"
echo "   ✅ Gestion erreurs ES6 modules"
echo "   ✅ Auto-réparation dépendances"  
echo "   ✅ Build de fallback automatique"
echo "   ✅ Diagnostic en temps réel"
echo ""

# Démarrage Apache au premier plan
exec apache2-foreground
