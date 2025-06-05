#!/bin/bash
set -e

echo "🚀 Démarrage ClanStats ..."

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

# Configuration React
if [ ! -f ".react-configured" ]; then
    echo "🔧 Installation React..."
    setup-react
else
    echo "✅ React déjà configuré"
    
    if [ ! -d "node_modules" ]; then
        echo "📦 Réinstallation node_modules..."
        npm install
    fi
fi

# ✅ AJOUT: Démarrage webpack dev server en interne (pas d'exposition externe)
echo "🔄 Démarrage Webpack Dev Server (interne)..."
npm run dev-server > /var/log/webpack.log 2>&1 &

# Attendre que le dev server soit prêt
echo "⏳ Attente du démarrage Webpack Dev Server..."
sleep 5

# Permissions finales
chown -R www-data:www-data /var/www/html

echo "🎉 Démarrage Apache avec Proxy Hot Reload..."
echo "📍 URL unique: http://localhost"
echo "🔥 Hot reload actif via proxy Apache!"

exec apache2-foreground