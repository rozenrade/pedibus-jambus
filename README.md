# 🚶 Blog Pedibus Jambus

Bienvenue sur le dépôt du blog officiel de l'association **Pedibus Jambus** !

Ce site est une application web développée avec le framework **Symfony**, servant de blog pour partager les sorties et événements de l'association.

---

## 📋 Prérequis

Avant de commencer, assurez-vous d'avoir installé sur votre machine :

- **PHP** >= 8.2 ( disponible via *https://www.apachefriends.org/fr/index.html* )
- **Composer** 
- **Symfony CLI** ( regarder la documentation Symfony ) 
- Un serveur de base de données : **MySQL** / **MariaDB** ou **PostgreSQL** ( Si différent de MySQL modifier le lien dans le .env )

---

## 🚀 Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/votre-organisation/pedibus-jambus-blog.git
cd pedibus-jambus-blog
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Créer la base de données et appliquer les migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 4. Lancer le serveur de développement

```bash
symfony serve
```

L'application est ensuite accessible sur [http://localhost:8000](http://localhost:8000).

