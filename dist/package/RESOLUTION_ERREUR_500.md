# Guide de Résolution - Erreur HTTP 500 sur Hostinger

## ❌ Erreur: HTTP ERROR 500

Une erreur HTTP 500 signifie une erreur interne du serveur. Cela peut être causé par plusieurs problèmes.

## 🔍 Causes Possibles

1. **Fichier .env corrompu ou incomplet**
2. **Fichier config.php avec des erreurs**
3. **Permissions de fichiers incorrectes**
4. **Extensions PHP manquantes**
5. **Memory limit trop basse**
6. **Fichier .htaccess mal configuré**
7. **Erreur de connexion MySQL**
8. **Timeout PHP dépassé**

## 🚀 Étape 1: Utiliser le Script de Diagnostic

1. Uploadez le fichier `debug-500.php` sur votre serveur
2. Accédez à: `https://votre-domaine.com/debug-500.php`
3. Le script identifiera la cause exacte de l'erreur

## 🔧 Solutions Courantes

### Solution 1: Recréer le fichier .env

Si le fichier .env est corrompu ou incomplet:

1. **Supprimez les fichiers d'installation**:
   - `.env`
   - `config_installed.php`

2. **Relancez l'installation**:
   - Accédez à `https://votre-domaine.com/install.php`
   - Utilisez les identifiants MySQL corrects (avec préfixe)
   - Suivez les étapes d'installation

### Solution 2: Vérifier les Permissions

Dans hPanel > Fichiers > Gestionnaire de fichiers:

1. Sélectionnez tous les fichiers et dossiers
2. Cliquez sur **Permissions**
3. Configurez:
   - **Dossiers**: 755
   - **Fichiers PHP**: 644
   - **Dossier uploads**: 777 (ou 755 avec propriétaire correct)

### Solution 3: Augmenter la Memory Limit

Dans hPanel > PHP > Options:

1. Sélectionnez votre domaine
2. Modifiez les options:
   - **Memory Limit**: 256M ou 512M
   - **Max Execution Time**: 300
   - **Max Input Time**: 300
   - **Post Max Size**: 64M
   - **Upload Max Filesize**: 64M

### Solution 4: Vérifier le fichier .htaccess

Si .htaccess contient des erreurs:

1. Renommez `.htaccess` en `.htaccess.bak`
2. Testez si le site fonctionne
3. Si oui, le problème est dans .htaccess
4. Recréez .htaccess avec le contenu par défaut

**Contenu .htaccess par défaut:**
```apache
# Empêcher l'accès direct aux fichiers de configuration
<FilesMatch "^\.env$">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "^config_installed\.php$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Réécriture d'URL
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### Solution 5: Vérifier les Extensions PHP

Dans hPanel > PHP > Extensions:

Assurez-vous que ces extensions sont activées:
- PDO
- pdo_mysql
- GD
- mbstring
- curl
- zip

### Solution 6: Vérifier le fichier error_log

1. Dans le Gestionnaire de fichiers, cherchez `error_log`
2. Ouvrez-le pour voir les erreurs
3. Les erreurs vous indiqueront la cause exacte

## 📋 Étapes de Dépannage Avancées

### Étape 1: Activer l'affichage des erreurs

Dans hPanel > PHP > Options:
- `display_errors = On`
- `error_reporting = E_ALL`

### Étape 2: Tester la connexion MySQL

Utilisez `mysql-troubleshoot.php` pour vérifier:
- La connexion MySQL fonctionne
- Les identifiants sont corrects
- Les droits sont suffisants

### Étape 3: Vérifier config.php

Ouvrez `config.php` et vérifiez:
- Les variables sont correctement définies
- Pas de syntaxe PHP
- Les chemins sont corrects

### Étape 4: Tester index.php directement

Accédez à `https://votre-domaine.com/index.php`
Si cela fonctionne, le problème peut être dans .htaccess

## 🎯 Checklist de Résolution

- [ ] Exécuter debug-500.php pour identifier la cause
- [ ] Vérifier que .env existe et est complet
- [ ] Vérifier que config.php existe
- [ ] Tester la connexion MySQL
- [ ] Vérifier les permissions (755/644)
- [ ] Augmenter memory limit si nécessaire
- [ ] Vérifier les extensions PHP
- [ ] Consulter error_log pour les détails
- [ ] Tester avec .htaccess désactivé

## 📞 Support Hostinger

Si après toutes ces étapes vous avez toujours l'erreur:

1. **Live Chat**: Disponible 24/7 dans hPanel
2. **Base de connaissances**: https://support.hostinger.com
3. **Recherchez**: "HTTP 500 error Hostinger"

## 🔗 Liens Utiles

- Diagnostic 500: `https://votre-domaine.com/debug-500.php`
- Diagnostic MySQL: `https://votre-domaine.com/mysql-troubleshoot.php`
- Réinstallation: `https://votre-domaine.com/install.php`

## 💡 Astuces

1. **Toujours utiliser debug-500.php** en premier pour identifier la cause
2. **error_log** contient souvent les détails de l'erreur
3. **Permissions incorrectes** sont une cause fréquente sur Hostinger
4. **Memory limit** peut causer des erreurs 500 sur les scripts lourds
5. **.htaccess** mal configuré peut causer des erreurs 500
