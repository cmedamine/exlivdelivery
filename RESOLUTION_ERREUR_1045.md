# Guide de Résolution - Erreur MySQL 1045 sur Hostinger

## ❌ Erreur: SQLSTATE[HY000] [1045] Access denied for user

Cette erreur signifie que l'authentification MySQL a échoué. Voici les solutions étape par étape.

## 🔍 Causes Possibles

1. **Nom d'utilisateur incorrect** (préfixe manquant)
2. **Mot de passe incorrect** (caractères spéciaux, erreur de copie)
3. **Utilisateur inexistant** dans la base de données
4. **Droits insuffisants** sur la base de données
5. **Hôte incorrect** (localhost vs IP)
6. **Base de données inexistante**

## 📋 Étape 1: Vérifier les Identifiants dans hPanel

### 1.1 Accéder aux bases de données
1. Connectez-vous à hPanel
2. Allez dans **Bases de données** > **MySQL**
3. Vous verrez la liste de vos bases de données

### 1.2 Noter les informations correctes
Pour chaque base de données, Hostinger affiche:
- **Nom de la base de données**: `u123456789_exliv_delivery` (avec préfixe)
- **Nom d'utilisateur**: `u123456789_exliv_admin` (avec préfixe)
- **Mot de passe**: Cliquez sur "Afficher" ou "Régénérer"
- **Hôte**: Généralement `localhost`

**IMPORTANT**: Les préfixes `u123456789_` sont OBLIGATOIRES sur Hostinger!

## 🔧 Étape 2: Recréer l'Utilisateur MySQL

Si l'utilisateur n'existe pas ou si vous n'êtes pas sûr:

1. Dans hPanel, allez dans **Bases de données** > **MySQL**
2. Cliquez sur **Créer un utilisateur**
3. Entrez:
   - **Nom d'utilisateur**: `exliv_admin` (Hostinger ajoutera le préfixe automatiquement)
   - **Mot de passe**: Générez un mot de passe fort
   - **Confirmer le mot de passe**: Répétez le même mot de passe
4. Cliquez sur **Créer**

Notez le nom complet avec préfixe: `u123456789_exliv_admin`

## 🔐 Étape 3: Attribuer les Droits à l'Utilisateur

1. Dans hPanel, allez dans **Bases de données** > **MySQL**
2. Cliquez sur **Gérer les utilisateurs** ou **Modifier** à côté de votre utilisateur
3. Sélectionnez la base de données `u123456789_exliv_delivery`
4. Cochez **Tous les droits** ou sélectionnez:
   - SELECT
   - INSERT
   - UPDATE
   - DELETE
   - CREATE
   - DROP
   - ALTER
   - INDEX
5. Cliquez sur **Enregistrer**

## 🧪 Étape 4: Tester avec le Script de Dépannage

1. Uploadez le fichier `mysql-troubleshoot.php` sur votre serveur
2. Accédez à: `https://votre-domaine.com/mysql-troubleshoot.php`
3. Utilisez le formulaire pour tester différentes combinaisons
4. Le script vous dira exactement ce qui ne va pas

## 📝 Étape 5: Utiliser les Identifiants Corrects dans install.php

Une fois le test réussi, utilisez ces identifiants dans `install.php`:

```
Hôte: localhost (TOUJOURS localhost sur Hostinger pour les connexions internes)
Nom de la base de données: u123456789_exliv_delivery
Utilisateur: u123456789_exliv_admin
Mot de passe: [votre mot de passe]
```

**IMPORTANT**: 
- Utilisez TOUJOURS `localhost` si votre site est hébergé sur Hostinger
- `mysql.hostinger.com` est uniquement pour les connexions externes (site hébergé ailleurs)

## ⚠️ Erreurs Spécifiques et Solutions

### Erreur: "Access denied for user 'exliv_admin'@'localhost'"
**Cause**: Préfixe manquant
**Solution**: Utilisez `u123456789_exliv_admin` au lieu de `exliv_admin`

### Erreur: "Access denied for user 'u123456789_exliv_admin'@'127.0.0.1'"
**Cause**: Hôte incorrect
**Solution**: Utilisez `localhost` au lieu de `127.0.0.1`

### Erreur: "Access denied for user '...'@'localhost' (using password: NO)"
**Cause**: Mot de passe vide
**Solution**: Entrez le mot de passe correct

### Erreur: "Access denied for user '...'@'localhost' (using password: YES)"
**Cause**: Mot de passe incorrect
**Solution**: 
- Vérifiez le mot de passe dans hPanel
- Régénérez le mot de passe s'il ne fonctionne pas
- Attention aux caractères spéciaux (@, #, $, etc.)

### Erreur: "Access denied for user '...'@'localhost' to database 'exliv_delivery'"
**Cause**: Droits insuffisants sur la base de données
**Solution**: Attribuez tous les droits à l'utilisateur dans hPanel

## 🚀 Checklist Avant Installation

- [ ] Base de données créée dans hPanel
- [ ] Utilisateur MySQL créé avec préfixe
- [ ] Mot de passe généré et noté
- [ ] Droits attribués (Tous les droits)
- [ ] Test de connexion réussi avec mysql-troubleshoot.php
- [ ] Identifiants notés pour install.php

## 📞 Support Hostinger

Si après toutes ces étapes vous avez toujours l'erreur:

1. **Live Chat**: Disponible 24/7 dans hPanel
2. **Base de connaissances**: https://support.hostinger.com
3. **Tutoriels**: https://www.hostinger.com/tutorials

## 🔗 Liens Utiles

- Script de diagnostic: `https://votre-domaine.com/diagnostic.php`
- Script de dépannage MySQL: `https://votre-domaine.com/mysql-troubleshoot.php`
- Installation: `https://votre-domaine.com/install.php`

## 💡 Astuces Hostinger

1. **Préfixes**: Toujours utiliser les préfixes `uXXXXXX_` fournis par Hostinger
2. **Hôte**: Utiliser `localhost` par défaut
3. **Droits**: Attribuer "Tous les droits" pour éviter les problèmes
4. **Mot de passe**: Régénérer si vous n'êtes pas sûr
5. **Test**: Toujours tester avec mysql-troubleshoot.php avant l'installation
