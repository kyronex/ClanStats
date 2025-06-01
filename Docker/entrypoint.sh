#!/bin/bash
set -e

echo "🚀 Démarrage ClanStats..."

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
    
    # Installer les dépendances si vendor n'existe pas
    if [ ! -d "vendor" ]; then
        echo "📦 Installation dépendances Composer..."
        composer install
    fi
fi

# Vérifier et installer React si nécessaire
if [ ! -f ".react-configured" ]; then
    echo "🔧 Installation React..."
    setup-react
else
    echo "✅ React déjà configuré"
    
    # Vérifier si les node_modules sont présents
    if [ ! -d "node_modules" ]; then
        echo "📦 Réinstallation node_modules..."
        npm install
    fi
    
    # Build des assets si nécessaire
    if [ ! -d "public/build" ]; then
        echo "🏗️  Build des assets..."
        npm run dev
    fi
fi

# Permissions finales
chown -R www-data:www-data /var/www/html

echo "🎉 Démarrage Apache..."
exec apache2-foreground