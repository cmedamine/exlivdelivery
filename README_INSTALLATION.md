# 📦 EXLIV Delivery - Guide d'Installation

## 🚀 Installation sur Hostinger

### Étape 1: Télécharger et Extraire le Fichier ZIP

1. Téléchargez le fichier `exliv-delivery-platform.zip`
2. Connectez-vous à votre compte Hostinger
3. Allez dans le **Gestionnaire de fichiers** (File Manager)
4. Naviguez vers le dossier `public_html` ou le sous-dossier où vous voulez installer la plateforme
5. **Téléchargez** le fichier ZIP dans ce dossier
6. **Extraire** le fichier ZIP (clic droit → Extract)

### Étape 2: Lancer l'Installation

1. Ouvrez votre navigateur web
2. Accédez à votre site: `https://votre-domaine.com` ou `https://votre-domaine.com/sous-dossier`
3. Vous serez **automatiquement redirigé** vers la page d'installation (`install.php`)

### Étape 3: Configuration de la Base de Données

Sur la page d'installation, remplissez les informations suivantes:

- **Hôte de la base de données**: `localhost` (pour Hostinger, c'est généralement `localhost`)
- **Nom de la base de données**: Le nom que vous voulez pour votre base (ex: `colisliv_stockout`)
- **Nom d'utilisateur MySQL**: Votre nom d'utilisateur MySQL de Hostinger
- **Mot de passe MySQL**: Votre mot de passe MySQL de Hostinger

> **Note**: La base de données sera créée automatiquement si elle n'existe pas.

### Étape 4: Finaliser l'Installation

1. Cliquez sur le bouton **"Installer la Plateforme"**
2. L'installateur va:
   - Tester la connexion à la base de données
   - Créer la base de données si nécessaire
   - Générer le fichier de configuration `.env`
   - Sécuriser les fichiers sensibles
3. Après 3 secondes, vous serez redirigé vers la page d'installation de la base de données

### Étape 5: Installation des Tables de la Base de Données

1. Sur la page `install_database.php`, cliquez sur le bouton pour installer les tables
2. L'installateur va créer automatiquement toutes les tables requises:
   - users (utilisateurs avec rôles)
   - settings (paramètres globaux)
   - commands (commandes)
   - trackingstates (états de suivi)
   - Et 20+ autres tables
3. Une fois terminé, vous serez redirigé vers le tableau de bord

### Étape 6: Première Connexion

1. Connectez-vous avec vos identifiants existants ou créez un compte administrateur
2. Configurez les paramètres de l'application dans **Paramètres**

## 🔧 Prérequis Serveur

Votre hébergement Hostinger doit avoir:

- ✅ PHP 7.4 ou supérieur
- ✅ Extension MySQL/PDO
- ✅ Extension GD (pour les images)
- ✅ Extension mbstring
- ✅ Extension curl
- ✅ Extension zip

> **Hostinger** inclut généralement toutes ces extensions par défaut.

## 📝 Informations de Base de Données Hostinger

Pour trouver vos identifiants MySQL sur Hostinger:

1. Connectez-vous à votre panneau Hostinger
2. Allez dans **Bases de données** → **MySQL**
3. Vous trouverez:
   - **Hôte**: Généralement `localhost`
   - **Utilisateur**: Votre nom d'utilisateur de base de données
   - **Mot de passe**: Le mot de passe que vous avez défini
   - **Base de données**: Vous pouvez en créer une nouvelle ou utiliser une existante

## 🔒 Sécurité

L'installateur crée automatiquement:

- Un fichier `.env` contenant vos credentials (protégé par .htaccess)
- Un fichier `.htaccess` pour empêcher l'accès direct aux fichiers de configuration
- Un fichier `config_installed.php` pour confirmer l'installation

### Nouvelles Fonctionnalités de Sécurité

- ✅ **Fonctions centralisées**: `functions.php` contient toutes les fonctions utilitaires
- ✅ **Sanitization améliorée**: Protection contre les injections SQL et XSS
- ✅ **Configuration sécurisée**: Secrets Shopify et Google API dans `.env`
- ✅ **Journal d'audit**: Table `audit_log` pour tracer les actions administrateur
- ✅ **Protection CSRF**: Table `csrf_tokens` pour la protection CSRF (à implémenter)

## 🆕 Nouvelles Fonctionnalités

### Gestion de la Base de Données

- **Installation automatique**: Script `install_database.php` pour créer toutes les tables
- **Sauvegarde**: Télécharger des sauvegardes SQL complètes
- **Restauration**: Restaurer depuis un fichier de sauvegarde via `restorebackup.php`
- **Schema complet**: Fichier `database_schema.sql` avec toutes les tables documentées

### Intégrations Externes

- **Shopify**: Webhook sécurisé avec secret configurable dans `.env`
- **Google Sheets**: Email API configurable dans settings
- **OneSignal**: App ID configurable dans settings
- **SMS**: Infrastructure prête pour intégration (Twilio, Nexmo, etc.)

### Fonctions Utilitaires

Le fichier `functions.php` inclut:
- `sanitize_vars()` - Nettoyage des variables
- `hash_password()` - Hash bcrypt des mots de passe
- `verify_password()` - Vérification des mots de passe
- `generate_csrf_token()` - Génération de tokens CSRF
- `verify_csrf_token()` - Vérification de tokens CSRF
- `add_audit_log()` - Journal d'audit
- `format_date()` - Formatage de dates
- `format_price()` - Formatage de prix
- `validate_email()` - Validation d'emails
- `validate_phone()` - Validation de téléphones
- Et bien plus...

## 🐛 Résolution de Problèmes

### Erreur: "Erreur de connexion à la base de données"

- Vérifiez que vos identifiants MySQL sont corrects
- Assurez-vous que la base de données existe ou que l'utilisateur a les droits de création
- Vérifiez que l'hôte est correct (généralement `localhost` sur Hostinger)

### Erreur: "Extension PHP manquante"

- Contactez le support Hostinger pour activer les extensions PHP requises

### Page blanche après installation

- Vérifiez les permissions des fichiers (doivent être 644 pour les fichiers, 755 pour les dossiers)
- Vérifiez que le fichier `.env` a été créé correctement

## 📞 Support

Si vous rencontrez des problèmes lors de l'installation:

1. Vérifiez que toutes les extensions PHP sont activées
2. Vérifiez vos identifiants de base de données
3. Assurez-vous que les permissions des fichiers sont correctes

---

**Bon déploiement ! 🎉**
