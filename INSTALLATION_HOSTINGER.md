# Guide d'Installation EXLIV Delivery sur Hostinger

## 📋 Prérequis

- Un compte Hostinger (hPanel)
- PHP 7.4 ou supérieur (Hostinger utilise généralement PHP 8.x)
- Base de données MySQL
- Accès FTP ou Gestionnaire de fichiers

## 🔧 Étape 1: Préparer la Base de Données

1. Connectez-vous à votre panneau Hostinger (hPanel)
2. Allez dans **Bases de données** > **MySQL**
3. Cliquez sur **Créer une base de données**
4. Remplissez les informations:
   - **Nom de la base de données**: `exliv_delivery` (ou autre)
   - **Utilisateur**: Créez un utilisateur ou utilisez un existant
   - **Mot de passe**: Générez un mot de passe fort
   - **Accès**: Sélectionnez "Tous les accès"
5. Cliquez sur **Créer**

**IMPORTANT**: Notez ces informations:
- Hôte MySQL: généralement `localhost` ou `mysql.hostinger.com`
- Nom de la base de données
- Nom d'utilisateur
- Mot de passe

## 📤 Étape 2: Uploader les Fichiers

### Option A: Via Gestionnaire de Fichiers Hostinger

1. Dans hPanel, allez dans **Fichiers** > **Gestionnaire de fichiers**
2. Naviguez vers le dossier `public_html`
3. Supprimez le fichier `default.php` s'il existe
4. Cliquez sur **Upload** (icône flèche vers le haut)
5. Sélectionnez votre fichier ZIP du projet
6. Une fois uploadé, cliquez-droit sur le ZIP et **Extraire**
7. Déplacez tous les fichiers dans `public_html` si nécessaire

### Option B: Via FTP

1. Utilisez FileZilla ou un autre client FTP
2. Connectez-vous avec les identifiants FTP Hostinger
3. Uploadez tous les fichiers dans le dossier `public_html`
4. Assurez-vous que la structure des dossiers est préservée

## 🔐 Étape 3: Configurer les Permissions

Les permissions correctes sont cruciales pour Hostinger:

1. Dans le Gestionnaire de fichiers, sélectionnez tous les fichiers
2. Cliquez sur **Permissions** (icône clé)
3. Configurez:
   - **Dossiers**: 755
   - **Fichiers PHP**: 644
   - **Dossier uploads**: 777 (ou 755 avec propriétaire correct)

**Via FTP**:
- Dossiers: `chmod 755`
- Fichiers: `chmod 644`
- uploads: `chmod 777`

## 🧪 Étape 4: Diagnostic (Recommandé)

Avant d'installer, exécutez le script de diagnostic:

1. Ouvrez votre navigateur
2. Allez sur: `https://votre-domaine.com/diagnostic.php`
3. Le script vérifiera:
   - Version PHP
   - Extensions requises
   - Permissions d'écriture
   - Connexion MySQL
4. Corrigez les problèmes signalés avant de continuer

## 🚀 Étape 5: Installation

1. Ouvrez votre navigateur
2. Allez sur: `https://votre-domaine.com/install.php`
3. Remplissez le formulaire avec les informations de votre base de données:
   - **Hôte**: `localhost` (essayez d'abord) ou `mysql.hostinger.com`
   - **Nom de la base de données**: celui créé à l'étape 1
   - **Utilisateur**: celui créé à l'étape 1
   - **Mot de passe**: celui créé à l'étape 1
4. Cliquez sur **Installer la Plateforme**
5. Attendez la redirection vers `install_database.php`
6. Cliquez sur **Installer les Tables de la Base de Données**

## ⚠️ Problèmes Communs et Solutions

### Problème 1: "Erreur de connexion à la base de données"

**Solutions**:
- Essayez `localhost` au lieu de `mysql.hostinger.com` (ou inversement)
- Vérifiez que l'utilisateur MySQL a les droits sur la base de données
- Vérifiez que le mot de passe est correct (attention aux caractères spéciaux)
- Dans hPanel, allez dans Bases de données > MySQL et vérifiez les détails

### Problème 2: "Permission denied"

**Solutions**:
- Vérifiez les permissions des dossiers (755) et fichiers (644)
- Le dossier `public_html` doit être accessible en écriture
- Le dossier `uploads` doit être accessible en écriture (777)

### Problème 3: Page blanche après installation

**Solutions**:
- Vérifiez le fichier `error_log` dans le dossier du projet
- Activez l'affichage des erreurs PHP dans hPanel:
  - hPanel > PHP > Sélectionnez votre domaine > Options PHP
  - `display_errors = On`
  - `error_reporting = E_ALL`

### Problème 4: Redirection ne fonctionne pas

**Solutions**:
- Vérifiez que le module Apache `mod_rewrite` est activé
- Dans hPanel > PHP > Sélectionnez votre domaine > Options
  - Assurez-vous que `mod_rewrite` est activé

### Problème 5: Fichiers .env non créés

**Solutions**:
- Vérifiez les permissions du dossier
- Créez manuellement le fichier `.env` avec le contenu:
  ```
  DB_HOST=localhost
  DB_NAME=votre_base_de_donnees
  DB_USER=votre_utilisateur
  DB_PASSWORD=votre_mot_de_passe
  APP_ENV=production
  APP_DEBUG=false
  ```

## 🔧 Configuration Post-Installation

### 1. Créer le Premier Utilisateur

1. Allez sur `https://votre-domaine.com/login.php`
2. Le système vous demandera de créer le premier administrateur
3. Ou insérez manuellement dans la base de données:

```sql
INSERT INTO users (id, fullname, picture, email, password, phone, type, roles, active, datesignup, trash)
VALUES (1, 'Admin', 'avatar.png', 'admin@votre-domaine.com', '$2y$10$YourHashedPassword', '', 'moderator', 'all', 'on', UNIX_TIMESTAMP(), '1');
```

### 2. Configurer les Paramètres

1. Connectez-vous avec le compte administrateur
2. Allez dans **Paramètres**
3. Configurez:
   - Nom de l'application
   - Logo
   - Devise
   - Préfixe des commandes
   - Frais de confirmation

### 3. Configurer les Intégrations (Optionnel)

1. Allez dans **Paramètres** ou **Intégrations E-commerce** (pour les clients)
2. Configurez:
   - Shopify (webhook URL: `https://votre-domaine.com/rcshopify.php`)
   - Youcan (webhook URL: `https://votre-domaine.com/rcyoucan.php`)
   - Google Sheets (email du compte de service)

## 🌐 Accéder à la Plateforme

- **Landing page publique**: `https://votre-domaine.com/home.php`
- **Connexion admin**: `https://votre-domaine.com/login.php`
- **Tracking public**: `https://votre-domaine.com/track.php`
- **Inscription publique**: `https://votre-domaine.com/register.php`

## 🔒 Sécurité Recommandée

1. **Supprimez les fichiers d'installation** après installation:
   - `install.php`
   - `install_database.php`
   - `diagnostic.php`

2. **Protégez le dossier admin** (optionnel):
   - Créez un fichier `.htaccess` dans un dossier admin avec:
     ```
     AuthType Basic
     AuthName "Zone Protégée"
     AuthUserFile /chemin/absolu/.htpasswd
     Require valid-user
     ```

3. **Activez HTTPS**:
   - Dans hPanel > SSL > Let's Encrypt
   - Activez le certificat SSL gratuit
   - Forcez la redirection HTTPS

4. **Configurez les backups automatiques**:
   - hPanel > Sauvegardes
   - Activez les sauvegardes quotidiennes

## 📞 Support Hostinger

Si vous rencontrez des problèmes:

- **Live Chat**: Disponible 24/7 dans hPanel
- **Base de connaissances**: https://support.hostinger.com
- **Tutoriels**: https://www.hostinger.com/tutorials

## 🔄 Mise à jour

Pour mettre à jour la plateforme:

1. Faites une sauvegarde de la base de données
2. Téléchargez la nouvelle version
3. Uploadez les fichiers (écrasez les anciens)
4. Exécutez `install_database.php` si nécessaire pour les mises à jour de schema

## 📝 Notes Importantes

- Hostinger utilise PHP 8.x par défaut - compatible avec EXLIV Delivery
- Le hôte MySQL est généralement `localhost` sur Hostinger
- Les timeouts PHP peuvent être ajustés dans hPanel > PHP > Options
- Les limites de mémoire peuvent être augmentées si nécessaire
- Le module `mod_rewrite` est activé par défaut sur Hostinger
