# ClanStats

## Prérequis

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

## 🚀 Installation Rapide

### 1. Cloner le dépôt

```bash
git clone [url-du-depot]
cd CLANSTAT
```

### 2. Configuration

Copier et adapter le fichier d'environnement :

```bash
cp docker/.env.exemple docker/.env
```

Modifier le fichier `docker/.env` selon vos besoins.

### 3. Lancer les conteneurs

```bash
docker-compose -f docker/docker-compose.yml up -d
```

## 🌐 Accéder à l'application

Une fois les conteneurs démarrés :

- **Application web** : http://localhost  
- **PHPMyAdmin** : http://localhost:8080  
- **Interface Ngrok** (si activée) : http://localhost:4040

## 🛠️ Commandes Utiles

### Arrêter les conteneurs

```bash
docker-compose -f docker/docker-compose.yml down
```

### Voir les logs

```bash
docker-compose -f docker/docker-compose.yml logs -f
```

### Accéder au shell du conteneur web

```bash
docker-compose -f docker/docker-compose.yml exec web bash
```

### Exécuter une commande Symfony

```bash
docker-compose -f docker/docker-compose.yml exec web php /var/www/html/bin/console <commande>
```

---