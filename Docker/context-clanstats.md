# 🎯 PROMPT SYSTÈME COMPLET - ClanStats Docker Setup

> **Version** : 1.0
> **Date** : 17 décembre 2024
> **Projet** : ClanStats - Symfony 6.4 + React 18 + Docker
> **Budget tokens** : 200 000

---

## 📌 Système d'ancrage contextuel

**Balise de référence** : `|||||||||||||||`

### ⚠️ Alertes programmées

Informe-moi aux seuils suivants de consommation du contexte :

| Seuil | Tokens   | Niveau      | Statut                  |
| ----- | -------- | ----------- | ----------------------- |
| 20%   | 40 000   | 🟢 Niveau 1 | Surveillance normale    |
| 40%   | 80 000   | 🟡 Niveau 2 | Attention recommandée   |
| 60%   | 120 000  | 🟠 Niveau 3 | Synthèse suggérée       |
| 80%   | 160 000  | 🟠 Niveau 4 | Action requise          |
| 90%+  | 180 000+ | 🔴 Critique | Régénération nécessaire |

### Format d'alerte requis :

```
⚠️ ALERTE CONTEXTE - [XX]% consommé
Espace restant estimé : [XX XXX] tokens
Recommandation : [action suggérée]
Prochaine alerte à : [XX]%
```

---

## 🏗️ CONTEXTE PROJET : ClanStats

### 📊 Vue d'ensemble

Application web pour gestion et visualisation de statistiques de clans.

### Stack technique complète

#### Backend

- **Framework** : Symfony 6.4
- **Langage** : PHP 8.1
- **ORM** : Doctrine
- **Database** : MySQL 8.0
- **API** : REST (potentiel GraphQL)

#### Frontend

- **Library** : React 18.2.0
- **Build Tool** : Webpack Encore 4.0
- **Charts** : Chart.js 4.4.0 + react-chartjs-2 5.2.0
- **Transpiler** : Babel 7

#### Infrastructure

- **Serveur Web** : Apache 2.4
- **Containerisation** : Docker + Docker Compose
- **Dev Server** : Webpack Dev Server (HMR activé)
- **Tunneling** : Ngrok
- **Admin DB** : phpMyAdmin

#### Extensions PHP

- pdo
- pdo_mysql
- intl

#### Modules Apache

- headers
- rewrite
- proxy
- proxy_http
- proxy_wstunnel

---

## 📄 FICHIER 1 : docker-compose.yml

**Emplacement** : `/Docker/docker-compose.yml`

```yaml
name: clanstats

services:
  # ============================================
  # SERVICE WEB - Apache + PHP + Node + Symfony
  # ============================================
  web:
    build:
      context: ..
      dockerfile: ./Docker/Dockerfile
    container_name: clan_stats_web
    ports:
      - "80:80" # Apache (Symfony)
      - "8081:8081" # Webpack Dev Server (HMR)
    volumes:
      - ../ClanStats:/var/www/html
      - node_modules_volume:/var/www/html/node_modules
    networks:
      - clan-stats-network
    environment:
      APP_ENV: ${APP_ENV}
      APP_SECRET: ${APP_SECRET}
      DATABASE_URL: mysql://${DB_USER}:${DB_PASSWORD}@db:3306/${DB_NAME}
      NODE_ENV: dev
    restart: unless-stopped
    depends_on:
      - db

  # ============================================
  # SERVICE DATABASE - MySQL 8.0
  # ============================================
  db:
    image: mysql:8.0
    container_name: clan_stats_db
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: ${DB_NAME}
      MYSQL_USER: ${DB_USER}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql
    networks:
      - clan-stats-network
    restart: unless-stopped

  # ============================================
  # SERVICE PHPMYADMIN - Interface DB
  # ============================================
  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: clan_stats_phpmyadmin
    environment:
      PMA_HOST: db
      PMA_USER: ${DB_USER}
      PMA_PASSWORD: ${DB_PASSWORD}
    ports:
      - "8083:80"
    networks:
      - clan-stats-network
    depends_on:
      - db

  # ============================================
  # SERVICE NGROK - Tunneling public
  # ============================================
  ngrok:
    image: ngrok/ngrok:latest
    container_name: clan_stats_ngrok
    environment:
      NGROK_AUTHTOKEN: ${NGROK_AUTHTOKEN}
    command: http web:80
    ports:
      - "4040:4040" # Dashboard Ngrok
    networks:
      - clan-stats-network
    depends_on:
      - web

# ============================================
# RÉSEAUX
# ============================================
networks:
  clan-stats-network:
    driver: bridge

# ============================================
# VOLUMES PERSISTANTS
# ============================================
volumes:
  db_data: # Données MySQL
  node_modules_volume: # Modules Node isolés
```

### 🔌 Ports exposés

| Port | Service            | Description                    |
| ---- | ------------------ | ------------------------------ |
| 80   | Apache             | Application Symfony principale |
| 8081 | Webpack Dev Server | Hot Module Replacement (HMR)   |
| 3306 | MySQL              | Base de données                |
| 8083 | phpMyAdmin         | Interface administration DB    |
| 4040 | Ngrok              | Dashboard tunneling            |

### 📦 Volumes

- **db_data** : Persistance données MySQL
- **node_modules_volume** : Isolation modules Node (performance Docker)
- **../ClanStats** : Mount bind du code source

---

## 📄 FICHIER 2 : Dockerfile

**Emplacement** : `/Docker/Dockerfile`

```dockerfile
# ============================================
# BASE IMAGE - PHP 8.1 avec Apache
# ============================================
FROM php:8.1-apache

# ============================================
# COMPOSER - Gestionnaire dépendances PHP
# ============================================
COPY --from=composer:2.6.4 /usr/bin/composer /usr/bin/composer

# ============================================
# VARIABLES ENVIRONNEMENT
# ============================================
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    DEBIAN_FRONTEND=noninteractive

# ============================================
# INSTALLATION DÉPENDANCES SYSTÈME
# ============================================
RUN apt-get update && apt-get install -y --no-install-recommends \
    # Outils de base
    git \
    zip \
    unzip \
    vim \
    curl \
    gnupg2 \
    # Dépendances PHP
    libicu-dev \
    default-mysql-client \
    # Installation Node.js 18.x
    && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    # Extensions PHP
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql intl \
    # Configuration Apache
    && a2enmod headers rewrite proxy proxy_http proxy_wstunnel \
    # Installation Symfony CLI
    && curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony \
    # Nettoyage
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /root/.symfony5

# ============================================
# CONFIGURATION WORKING DIRECTORY
# ============================================
WORKDIR /var/www/html

# ============================================
# COPIE FICHIERS CONFIGURATION
# ============================================
COPY Docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY Docker/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY Docker/setup-react.sh /usr/local/bin/setup-react.sh

# ============================================
# PERMISSIONS SCRIPTS
# ============================================
RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/setup-react.sh

# ============================================
# EXPOSITION PORTS
# ============================================
EXPOSE 80 8081

# ============================================
# POINT D'ENTRÉE
# ============================================
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
```

### 🔧 Composants installés

#### Extensions PHP

- **pdo** : Base pour connexions DB
- **pdo_mysql** : Driver MySQL
- **intl** : Internationalisation

#### Modules Apache

- **headers** : Gestion en-têtes HTTP
- **rewrite** : URL rewriting
- **proxy** : Proxy inverse
- **proxy_http** : Proxy HTTP
- **proxy_wstunnel** : WebSocket tunneling (pour HMR)

#### CLI Tools

- **composer** 2.6.4 : Gestionnaire dépendances PHP
- **symfony** CLI : Outils ligne de commande Symfony
- **node** 18.x : Runtime JavaScript
- **npm** : Gestionnaire packages Node

---

## 📄 FICHIER 3 : entrypoint.sh

**Emplacement** : `/Docker/entrypoint.sh`

```bash
#!/bin/bash
set -e

echo "🚀 Démarrage ClanStats..."

# ============================================
# NAVIGATION VERS RÉPERTOIRE APPLICATION
# ============================================
cd /var/www/html || exit 1

# ============================================
# CONFIGURATION SYMFONY
# ============================================
if [ ! -f ".env.local" ]; then
    echo "⚙️ Création .env.local..."
    cp .env .env.local 2>/dev/null || echo "DATABASE_URL=${DATABASE_URL}" > .env.local
fi

# ============================================
# INSTALLATION DÉPENDANCES COMPOSER
# ============================================
if [ ! -d "vendor" ]; then
    echo "📦 Installation dépendances Composer..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# ============================================
# ATTENTE DISPONIBILITÉ MYSQL
# ============================================
echo "⏳ Attente MySQL..."
until mysql -h db -u"${DB_USER}" -p"${DB_PASSWORD}" -e "SELECT 1" >/dev/null 2>&1; do
    echo "   MySQL pas encore prêt, nouvelle tentative dans 2s..."
    sleep 2
done
echo "✅ MySQL prêt"

# ============================================
# CONFIGURATION REACT (PREMIÈRE FOIS UNIQUEMENT)
# ============================================
if [ ! -f ".react-configured" ]; then
    echo "⚛️ Configuration React initiale..."
    /usr/local/bin/setup-react.sh
else
    echo "✅ React déjà configuré (skip)"
fi

# ============================================
# DÉMARRAGE APACHE EN ARRIÈRE-PLAN
# ============================================
echo "🌐 Démarrage Apache..."
apache2-ctl -D FOREGROUND &

# Pause pour laisser Apache démarrer
sleep 3

# ============================================
# DÉMARRAGE WEBPACK DEV SERVER (HMR)
# ============================================
if [ -f "package.json" ] && [ -d "node_modules" ]; then
    echo "🔥 Démarrage Webpack Dev Server (HMR)..."
    npm run dev-server &
else
    echo "⚠️ React non configuré, skip dev-server"
fi

# ============================================
# ATTENTE PROCESSUS (keep container alive)
# ============================================
wait
```

### 🔄 Ordre d'exécution

1. **Navigation** vers `/var/www/html`
2. **Vérification** `.env.local` (création si absent)
3. **Installation** dépendances Composer (si `vendor/` absent)
4. **Attente** disponibilité MySQL (retry loop)
5. **Configuration** React (premier démarrage uniquement, flag `.react-configured`)
6. **Démarrage** Apache en mode daemon
7. **Démarrage** Webpack Dev Server (si `package.json` existe)
8. **Attente** infinie (keep container alive)

### 🛡️ Sécurités

- `set -e` : Arrêt sur erreur
- Retry loop MySQL avec timeout
- Vérification existence fichiers avant exécution
- Flag `.react-configured` pour éviter reconfigurations

---

## 📄 FICHIER 4 : setup-react.sh

**Emplacement** : `/Docker/setup-react.sh`

```bash
#!/bin/bash
set -e

echo "⚛️ Configuration React 18 + Webpack Encore..."

cd /var/www/html || exit 1

# ============================================
# CONFIGURATION PACKAGE.JSON
# ============================================
echo "📝 Écriture package.json..."
cat > package.json <<'EOF'
{
  "name": "clanstats-frontend",
  "version": "1.0.0",
  "description": "Frontend React pour ClanStats",
  "private": true,
  "devDependencies": {
    "@babel/core": "^7.17.0",
    "@babel/preset-env": "^7.16.0",
    "@babel/preset-react": "^7.16.0",
    "@symfony/webpack-encore": "^4.0.0",
    "webpack": "^5.0.0",
    "webpack-cli": "^4.9.0",
    "webpack-dev-server": "^4.7.0",
    "babel-loader": "^8.2.3",
    "css-loader": "^6.5.1",
    "style-loader": "^3.3.1",
    "file-loader": "^6.2.0"
  },
  "dependencies": {
    "react": "18.2.0",
    "react-dom": "18.2.0",
    "chart.js": "^4.4.0",
    "react-chartjs-2": "^5.2.0"
  },
  "scripts": {
    "dev-server": "encore dev-server --hot --host 0.0.0.0 --port 8081 --allowed-hosts all",
    "dev": "encore dev",
    "watch": "encore dev --watch",
    "build": "encore production --progress"
  }
}
EOF

# ============================================
# CONFIGURATION WEBPACK ENCORE
# ============================================
echo "⚙️ Écriture webpack.config.js..."
cat > webpack.config.js <<'EOF'
const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })
    .enableReactPreset()
    .enableSassLoader()
    .copyFiles({
        from: './assets/images',
        to: 'images/[path][name].[hash:8].[ext]'
    })
    .configureDevServerOptions(options => {
        options.host = '0.0.0.0';
        options.port = 8081;
        options.allowedHosts = 'all';
        options.client = {
            webSocketURL: {
                hostname: 'localhost',
                port: 8081,
                protocol: 'ws'
            }
        };
        options.watchFiles = {
            paths: ['src/**/*.php', 'templates/**/*.twig'],
            options: {
                usePolling: true,
                interval: 1000
            }
        };
    });

module.exports = Encore.getWebpackConfig();
EOF

# ============================================
# CONFIGURATION BABEL
# ============================================
echo "🎨 Écriture .babelrc..."
cat > .babelrc <<'EOF'
{
  "presets": [
    ["@babel/preset-env", {
      "useBuiltIns": "usage",
      "corejs": 3
    }],
    ["@babel/preset-react", {
      "runtime": "automatic"
    }]
  ],
  "plugins": [
    "@babel/plugin-proposal-class-properties"
  ]
}
EOF

# ============================================
# CRÉATION STRUCTURE ASSETS
# ============================================
echo "📁 Création structure assets/..."
mkdir -p assets/components assets/styles assets/images

# ============================================
# FICHIER APP.JS PRINCIPAL (React 18 + HMR)
# ============================================
echo "⚛️ Écriture assets/app.js..."
cat > assets/app.js <<'EOF'
import React from "react";
import "./styles/app.css";

// ============================================
// IMPORT REACT 18 - createRoot API
// ============================================
let createRoot;
try {
    createRoot = require("react-dom/client").createRoot;
    console.log("✅ React 18 createRoot loaded");
} catch (e) {
    console.warn("⚠️ Fallback vers react-dom classique", e);
    const ReactDOM = require("react-dom");
    createRoot = (container) => ({
        render: (el) => ReactDOM.render(el, container),
    });
}

// ============================================
// COMPOSANTS EXEMPLE
// ============================================
const Test = () => (
    <div className="clanstats-app">
        <h1>🎮 ClanStats</h1>
        <div className="hot-reload-demo">
            <p>✅ React 18.2.0 opérationnel</p>
            <p>🔥 Hot Module Replacement actif</p>
            <button className="btn-primary">Tester HMR</button>
        </div>
        <div className="hot-reload-info">
            <p>Modifiez ce fichier pour tester le HMR !</p>
            <small>Webpack Dev Server : localhost:8081</small>
        </div>
    </div>
);

const ClanSearchForm = () => (
    <div className="clan-search">
        <h2>🔍 Recherche de Clan</h2>
        <input type="text" placeholder="Entrez le nom du clan..." />
        <button className="btn-secondary">Rechercher</button>
    </div>
);

// ============================================
// FONCTION RENDER AVEC MULTI-MOUNT POINTS
// ============================================
let rootTest = null;
let rootClanSearchForm = null;

function render() {
    // Mount point 1: #react-app
    const mountTest = document.getElementById("react-app");

    // Mount point 2: #clan-search
    const mountClanSearchForm = document.getElementById("clan-search");

    if (mountTest) {
        if (!rootTest) {
            rootTest = createRoot(mountTest);
        }
        rootTest.render(<Test />);
    }

    if (mountClanSearchForm) {
        if (!rootClanSearchForm) {
            rootClanSearchForm = createRoot(mountClanSearchForm);
        }
        rootClanSearchForm.render(<ClanSearchForm />);
    }
}

// ============================================
// RENDER INITIAL
// ============================================
render();

// ============================================
// HOT MODULE REPLACEMENT (HMR)
// ============================================
if (module.hot) {
    console.log("🔥 HMR disponible");

    // Accept modifications du module actuel
    module.hot.accept((err) => {
        if (err) {
            console.error("❌ HMR error", err);
        } else {
            console.log("✅ HMR: Module rechargé");
            render();
        }
    });

    // Accept modifications dans ./components/
    module.hot.accept(
        require.context("./components", true, /\.(js|jsx)$/),
        () => {
            console.log("🔄 HMR: Composant modifié dans ./components/ !");
            render();
        }
    );

    // Accept modifications CSS
    module.hot.accept("./styles/app.css", () => {
        console.log("🎨 CSS rechargé via HMR");
    });
}
EOF

# ============================================
# FICHIER CSS PRINCIPAL
# ============================================
echo "🎨 Écriture assets/styles/app.css..."
cat > assets/styles/app.css <<'EOF'
/* ============================================
   RESET & BASE
   ============================================ */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
                 "Helvetica Neue", Arial, sans-serif;
    background: #f5f5f5;
    color: #333;
}

/* ============================================
   COMPOSANT PRINCIPAL
   ============================================ */
.clanstats-app {
    max-width: 800px;
    margin: 2rem auto;
    padding: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    color: #fff;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.clanstats-app h1 {
    font-size: 2rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

/* ============================================
   DEMO HMR
   ============================================ */
.hot-reload-demo {
    margin: 1.5rem 0;
}

.hot-reload-demo p {
    margin: 0.5rem 0;
    font-size: 1.1rem;
}

.hot-reload-demo button {
    margin: 0.4rem;
    padding: 0.6rem 1rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
    transition: transform 0.2s, box-shadow 0.2s;
}

.hot-reload-demo button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.hot-reload-demo .btn-primary {
    background: #28a745;
    color: #fff;
}

.hot-reload-demo .btn-secondary {
    background: #17a2b8;
    color: #fff;
}

/* ============================================
   INFO BOX
   ============================================ */
.hot-reload-info {
    background: rgba(255, 255, 255, 0.08);
    padding: 1rem;
    border-radius: 8px;
    margin-top: 1rem;
    backdrop-filter: blur(10px);
}

.hot-reload-info p {
    margin: 0.3rem 0;
}

.hot-reload-info small {
    opacity: 0.8;
    font-size: 0.85rem;
}

/* ============================================
   CLAN SEARCH
   ============================================ */
.clan-search {
    max-width: 600px;
    margin: 2rem auto;
    padding: 1.5rem;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.clan-search h2 {
    color: #333;
    margin-bottom: 1rem;
}

.clan-search input {
    width: 100%;
    padding: 0.8rem;
    border: 2px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    margin-bottom: 1rem;
}

.clan-search input:focus {
    outline: none;
    border-color: #667eea;
}

.clan-search button {
    width: 100%;
    padding: 0.8rem;
    background: #667eea;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
}

.clan-search button:hover {
    background: #5568d3;
}
EOF

# ============================================
# NETTOYAGE AVANT INSTALL
# ============================================
echo "📦 Nettoyage avant installation npm..."
rm -rf node_modules package-lock.json 2>/dev/null || true

# ============================================
# INSTALLATION NPM
# ============================================
echo "📥 Installation dépendances npm..."
npm install

# ============================================
# VÉRIFICATION REACT-DOM/CLIENT
# ============================================
echo "🔍 Test final react-dom/client..."
node -e "
try {
    const { createRoot } = require('react-dom/client');
    console.log('✅ react-dom/client OK', typeof createRoot);
} catch(e) {
    console.error('❌ react-dom/client KO', e.message);
    process.exit(1);
}
" || {
    echo "🔧 Réinstallation react-dom..."
    npm uninstall react-dom
    npm install react-dom@18.2.0 --save --exact
}

# ============================================
# NETTOYAGE CACHE
# ============================================
echo "🧹 Nettoyage cache npm..."
npm cache clean --force >/dev/null 2>&1 || true
rm -rf node_modules/.cache 2>/dev/null || true

# ============================================
# BUILD DÉVELOPPEMENT
# ============================================
echo "🏗️ Build développement (npm run dev)..."
npm run dev || {
    echo "⚠️ Build dev échoué, tentative build production..."
    npm run build || true
}

# ============================================
# VÉRIFICATION FINALE
# ============================================
echo "🔍 Vérification finale..."
if [ -f "public/build/app.js" ]; then
    echo "✅ Build réussi ! Fichiers générés :"
    ls -la public/build/ | grep -E '\.(js|css)$' || true
else
    echo "⚠️ Build non trouvé dans public/build/"
    echo "Lancer manuellement: npm run build"
fi

# ============================================
# FLAG CONFIGURATION TERMINÉE
# ============================================
touch .react-configured
echo "✅ Configuration React terminée avec succès !"
```

### 🔧 Actions du script

1. **Configuration** `package.json` avec dépendances React 18
2. **Configuration** `webpack.config.js` avec HMR
3. **Configuration** `.babelrc` pour JSX
4. **Création** structure `assets/` (components, styles, images)
5. **Génération** `app.js` avec createRoot API et HMR
6. **Génération** `app.css` avec styles de base
7. **Installation** dépendances npm
8. **Vérification** react-dom/client
9. **Build** développement initial
10. **Flag** `.react-configured` pour éviter re-exécution

### 🎨 Composants générés

- **Test** : Composant démo avec boutons
- **ClanSearchForm** : Formulaire recherche clan

### 🔥 Configuration HMR

- **Accept self** : Reload module actuel
- **Accept components** : Watch dossier `./components/`
- **Accept CSS** : Reload styles sans refresh page

---

## 🎯 INSTRUCTIONS D'UTILISATION

### 🚀 Démarrage du projet

```bash
# Depuis le dossier Docker/
cd Docker/

# Démarrer tous les services
docker-compose up -d

# Voir les logs en temps réel
docker-compose logs -f web

# Arrêter les services
docker-compose down

# Rebuild complet (après modification Dockerfile)
docker-compose up -d --build
```

### 🔌 Accès aux services

| Service                 | URL                   | Description          |
| ----------------------- | --------------------- | -------------------- |
| **Application Symfony** | http://localhost      | Interface principale |
| **Webpack Dev Server**  | http://localhost:8081 | Assets HMR           |
| **phpMyAdmin**          | http://localhost:8083 | Administration DB    |
| **Ngrok Dashboard**     | http://localhost:4040 | URLs publiques       |

### 🛠️ Commandes utiles

```bash
# Accéder au shell du container web
docker exec -it clan_stats_web bash

# Accéder au shell MySQL
docker exec -it clan_stats_db mysql -u${DB_USER} -p${DB_PASSWORD}

# Voir les logs d'un service spécifique
docker-compose logs -f web
docker-compose logs -f db

# Redémarrer un service
docker-compose restart web

# Voir les processus actifs
docker-compose ps

# Supprimer volumes et containers
docker-compose down -v

# Build assets manuellement
docker exec -it clan_stats_web npm run build

# Lancer migrations Doctrine
docker exec -it clan_stats_web php bin/console doctrine:migrations:migrate
```

---

## 📊 GUIDE SURVEILLANCE CONTEXTE

### 🎯 Pour l'Assistant IA

#### À chaque réponse, évalue :

1. **Taille réponse actuelle** : ~X tokens
2. **Cumul depuis début** : ~X tokens
3. **Pourcentage consommé** : X%
4. **Prochaine alerte** : X%

#### Format de suivi interne :

```
[CONTEXTE INTERNE]
Tokens réponse actuelle: ~XXX
Tokens cumulés: ~XX XXX
Consommation: XX%
Alerte: [AUCUNE|NIVEAU X]
```

#### Déclenchement alerte :

```
|||||||||||||||

⚠️ ALERTE CONTEXTE - XX% consommé
Espace restant estimé : XX XXX tokens
État actuel :
  - Réponses générées : XX
  - Tokens utilisés : ~XX XXX
  - Fichiers en mémoire : 4 (docker-compose, Dockerfile, entrypoint, setup-react)

Recommandation : [suggestion adaptée au niveau]

Prochaine alerte à : XX%

|||||||||||||||
```

### 📋 Recommandations par niveau

#### 🟢 Niveau 1 (20%) - 40 000 tokens

**Statut** : Surveillance normale
**Action** : Informer simplement
**Recommandation** : Aucune action requise

#### 🟡 Niveau 2 (40%) - 80 000 tokens

**Statut** : Attention recommandée
**Action** : Suggérer synthèse optionnelle
**Recommandation** :

- Résumer les points clés abordés
- Proposer de sauvegarder l'état actuel

#### 🟠 Niveau 3 (60%) - 120 000 tokens

**Statut** : Synthèse suggérée
**Action** : Recommander fortement une synthèse
**Recommandation** :

- Générer un résumé structuré de la session
- Identifier les décisions/solutions importantes
- Proposer de régénérer avec contexte allégé

#### 🟠 Niveau 4 (80%) - 160 000 tokens

**Statut** : Action requise
**Action** : Imposer une synthèse
**Recommandation** :

- **OBLIGATOIRE** : Créer document récapitulatif
- Préparer nouveau prompt contexte
- Lister éléments à conserver en priorité

#### 🔴 Critique (90%+) - 180 000+ tokens

**Statut** : Régénération nécessaire
**Action** : Arrêter conversation
**Recommandation** :

- **STOP** : Ne plus accepter de nouvelles questions complexes
- Fournir synthèse finale complète
- Générer nouveau prompt avec contexte optimisé
- Inviter l'utilisateur à démarrer nouvelle session

---

### 🐛 Debug commun

#### Container web crash

```bash
# Voir logs détaillés
docker-compose logs web

# Vérifier entrypoint
docker exec clan_stats_web cat /usr/local/bin/entrypoint.sh

# Tester script manuellement
docker exec -it clan_stats_web bash
/usr/local/bin/entrypoint.sh
```

#### MySQL non accessible

```bash
# Vérifier container DB
docker-compose ps db

# Tester connexion depuis web
docker exec clan_stats_web mysql -h db -u root -p

# Recréer volume
docker-compose down -v
docker-compose up -d
```

#### HMR ne fonctionne pas

```bash
# Vérifier Webpack Dev Server
docker exec clan_stats_web ps aux | grep webpack

# Tester port 8081
docker exec clan_stats_web netstat -tuln | grep 8081

# Rebuild assets
docker exec clan_stats_web npm run dev

# Vérifier logs HMR
docker-compose logs web | grep "🔥"
```

#### React ne charge pas

```bash
# Vérifier build
docker exec clan_stats_web ls -la public/build/

# Vérifier module react-dom
docker exec clan_stats_web npm list react-dom

# Rebuild complet
docker exec clan_stats_web bash -c "rm -rf node_modules && npm install && npm run build"
```

---

## 📚 RESSOURCES & RÉFÉRENCES

### Documentation officielle

- [Symfony 6.4](https://symfony.com/doc/6.4/index.html)
- [React 18](https://react.dev/)
- [Webpack Encore](https://symfony.com/doc/current/frontend.html)
- [Docker Compose](https://docs.docker.com/compose/)
- [Chart.js](https://www.chartjs.org/)
- [React-chartjs-2](https://react-chartjs-2.js.org/)

### Commandes Symfony utiles

```bash
# Cache
docker exec clan_stats_web php bin/console cache:clear

# Routes
docker exec clan_stats_web php bin/console debug:router

# Base de données
docker exec clan_stats_web php bin/console doctrine:database:create
docker exec clan_stats_web php bin/console doctrine:schema:update --force
docker exec clan_stats_web php bin/console doctrine:migrations:migrate

# Assets
docker exec clan_stats_web php bin/console assets:install

# Debug
docker exec clan_stats_web php bin/console debug:container
docker exec clan_stats_web php bin/console debug:autowiring
```

---

|||||||||||||||
