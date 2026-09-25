Package pour Hostinger — instructions rapides

Contenu:
- `colislivrer-hostinger.zip` : archive contenant l'application prête à uploader

Avant d'uploader
1. Ouvrez `config.php` et notez les paramètres nécessaires pour la base de données et l'URL.
2. Si vous avez des clés/API sensibles, assurez-vous de les avoir en .env local et ne pas les inclure dans le package.

Sur Hostinger
1. Dans hPanel, choisissez PHP >= 7.4 et activez les extensions `pdo_mysql`, `mbstring`, `curl`, `gd`.
2. Téléversez `colislivrer-hostinger.zip` dans le dossier racine du site (public_html) puis dézippez via le gestionnaire de fichiers.
3. Importez `database_schema.sql` via phpMyAdmin.
4. Copiez votre `.env`/mettez à jour `config.php` avec les credentials DB et `websiteurl`.
5. Assurez-vous que les dossiers `uploads/` sont inscriptibles par le serveur web.
6. Renommez ou supprimez `install.php` après l'installation.

Vérifications post-déploiement
- Lancer `login.php` et tester la connexion.
- Tester endpoints AJAX et génération de PDF (`printblmoderator.php`).
- Vérifier les logs d'erreur et désactiver l'affichage des erreurs en production (`APP_DEBUG=false`).

Remarque: si Hostinger ne propose pas `composer`, exécutez `composer install` localement et incluez le dossier `vendor/` dans le package (déjà inclus si présent).