-- ============================================
-- EXLIV Delivery - Database Schema
-- Version: 1.0
-- Date: 12 août 2026
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================
-- Table: users
-- Description: Utilisateurs du système avec différents rôles
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `picture` varchar(255) DEFAULT 'avatar.png',
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `sav` varchar(20) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `cin` varchar(20) DEFAULT NULL,
  `store` varchar(255) DEFAULT NULL,
  `bank` varchar(255) DEFAULT NULL,
  `rib` varchar(255) DEFAULT NULL,
  `stockout` varchar(255) DEFAULT NULL,
  `emailstockout` varchar(255) DEFAULT NULL,
  `type` enum('moderator','dlm','subdlm','client','worker') NOT NULL DEFAULT 'moderator',
  `roles` text,
  `active` enum('on','off') DEFAULT 'on',
  `datesignup` int(11) DEFAULT NULL,
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `type` (`type`),
  KEY `trash` (`trash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: settings
-- Description: Paramètres globaux de l'application
-- ============================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logo` varchar(255) DEFAULT NULL,
  `appname` varchar(255) DEFAULT 'EXLIV Delivery',
  `cmdprefix` varchar(10) DEFAULT 'CMD',
  `currency` varchar(10) DEFAULT 'MAD',
  `confirmationfees` decimal(10,2) DEFAULT 0.00,
  `sepdelivered` enum('0','1') DEFAULT '0',
  `simplestats` enum('0','1') DEFAULT '0',
  `requirednote` enum('0','1') DEFAULT '0',
  `reseller` enum('0','1') DEFAULT '0',
  `onesignal` varchar(255) DEFAULT NULL,
  `google_sheet_email` varchar(255) DEFAULT NULL,
  `google_api_key` varchar(255) DEFAULT NULL,
  `shopify_secret` varchar(255) DEFAULT NULL,
  `shopify_store_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `settings` (`id`, `logo`, `appname`, `cmdprefix`, `currency`, `confirmationfees`, `sepdelivered`, `simplestats`, `requirednote`, `reseller`) VALUES
(1, NULL, 'EXLIV Delivery', 'CMD', 'MAD', 0.00, '0', '0', '0', '0');

-- ============================================
-- Table: parametres
-- Description: Paramètres utilisateur personnalisés
-- ============================================
CREATE TABLE IF NOT EXISTS `parametres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `nbrows` int(11) DEFAULT 20,
  `rowcolor` enum('0','1') DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  FOREIGN KEY (`user`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: cities
-- Description: Villes/zones de livraison
-- ============================================
CREATE TABLE IF NOT EXISTS `cities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `city` varchar(100) NOT NULL,
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `trash` (`trash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: shippingfees
-- Description: Frais de livraison par ville et livreur
-- ============================================
CREATE TABLE IF NOT EXISTS `shippingfees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dlm` int(11) NOT NULL,
  `city` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `dlm` (`dlm`),
  KEY `city` (`city`),
  KEY `trash` (`trash`),
  FOREIGN KEY (`dlm`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: clientfees
-- Description: Frais de livraison par client
-- ============================================
CREATE TABLE IF NOT EXISTS `clientfees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `client` (`client`),
  KEY `trash` (`trash`),
  FOREIGN KEY (`client`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: gshippingfees
-- Description: Frais de livraison par ville (global)
-- ============================================
CREATE TABLE IF NOT EXISTS `gshippingfees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `city` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `city` (`city`),
  KEY `trash` (`trash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: trackingstates
-- Description: États de suivi des commandes
-- ============================================
CREATE TABLE IF NOT EXISTS `trackingstates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` varchar(100) NOT NULL,
  `color` varchar(20) NOT NULL DEFAULT '#757575',
  `agent` text,
  `phase` varchar(100) NOT NULL,
  `kpi` varchar(50) NOT NULL DEFAULT '',
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `phase` (`phase`),
  KEY `kpi` (`kpi`),
  KEY `trash` (`trash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default tracking states
INSERT INTO `trackingstates` (`state`, `color`, `agent`, `phase`, `kpi`, `trash`) VALUES
('Nouveau', '#2196F3', 'Modérateur', 'Confirmation', 'En cours', '1'),
('Confirmé', '#03A9F4', 'Modérateur,Agent de confirmation', 'Confirmation', 'En cours', '1'),
('Reporté', '#FF9800', 'Modérateur,Client,Agent de confirmation', 'Confirmation', 'En cours', '1'),
('Interessé', '#9C27B0', 'Modérateur,Agent de confirmation', 'Confirmation', 'En cours', '1'),
('Changement adresse', '#FF9800', 'Modérateur,Client,Agent de confirmation', 'Confirmation', 'En cours', '1'),
('Annulé', '#F44336', 'Modérateur,Client,Agent de confirmation,Livreur', 'Confirmation', 'Echouées', '1'),
('Refusé', '#F44336', 'Modérateur,Agent de confirmation,Livreur', 'Confirmation', 'Echouées', '1'),
('Hors zone', '#F44336', 'Modérateur,Agent de confirmation,Livreur', 'Confirmation', 'Echouées', '1'),
('Ramassé', '#00BCD4', 'Modérateur,Livreur', 'Ramassage', 'En cours', '1'),
('En cours de livraison', '#2196F3', 'Modérateur,Livreur', 'Livraison', 'En cours', '1'),
('Livré', '#4CAF50', 'Modérateur,Livreur', 'Livraison', 'Livrées', '1'),
('Retour', '#795548', 'Modérateur,Livreur', 'Retour', 'Echouées', '1'),
('Echec de livraison', '#F44336', 'Modérateur,Livreur', 'Livraison', 'Echouées', '1');

-- ============================================
-- Table: commands
-- Description: Commandes
-- ============================================
CREATE TABLE IF NOT EXISTS `commands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `product` text NOT NULL,
  `qty` text NOT NULL,
  `dlm` int(11) DEFAULT 0,
  `subdlm` int(11) DEFAULT 0,
  `client` int(11) NOT NULL DEFAULT 0,
  `worker` int(11) DEFAULT 0,
  `store` int(11) DEFAULT 0,
  `source` varchar(50) DEFAULT 'Site',
  `package_type` enum('particulier','rapide','normal') DEFAULT 'normal',
  `fullname` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `fees` decimal(10,2) DEFAULT 0.00,
  `extrafees` decimal(10,2) NOT NULL DEFAULT 0.00,
  `package` int(11) NOT NULL DEFAULT 0,
  `phase` varchar(50) DEFAULT 'confirmation',
  `state` varchar(50) DEFAULT 'Nouveau',
  `datereported` int(11) DEFAULT NULL,
  `note` text,
  `workers` text,
  `collected` enum('on','off') NOT NULL DEFAULT 'off',
  `invoiced` enum('on','off') DEFAULT 'off',
  `invoiceddlm` enum('on','off') NOT NULL DEFAULT 'off',
  `confirmed` enum('on','off') NOT NULL DEFAULT 'off',
  `treated` enum('on','off') NOT NULL DEFAULT 'off',
  `archived` enum('0','1') NOT NULL DEFAULT '0',
  `echange` enum('0','1') NOT NULL DEFAULT '0',
  `openpackage` enum('0','1') NOT NULL DEFAULT '0',
  `clientname` varchar(255) DEFAULT NULL,
  `clientphone` varchar(20) DEFAULT NULL,
  `tracking_code` varchar(50) DEFAULT NULL,
  `dateadd` int(11) NOT NULL,
  `dateupdate` int(11) NOT NULL,
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `tracking_code` (`tracking_code`),
  KEY `dlm` (`dlm`),
  KEY `subdlm` (`subdlm`),
  KEY `client` (`client`),
  KEY `worker` (`worker`),
  KEY `store` (`store`),
  KEY `state` (`state`),
  KEY `phase` (`phase`),
  KEY `collected` (`collected`),
  KEY `archived` (`archived`),
  KEY `package_type` (`package_type`),
  KEY `trash` (`trash`),
  KEY `dateadd` (`dateadd`),
  FOREIGN KEY (`dlm`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`subdlm`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`worker`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`store`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: commandshistory
-- Description: Historique des changements d'état des commandes
-- ============================================
CREATE TABLE IF NOT EXISTS `commandshistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `command` varchar(50) NOT NULL,
  `state` varchar(50) NOT NULL,
  `agent` varchar(255) NOT NULL DEFAULT '',
  `dateadd` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `command` (`command`),
  KEY `dateadd` (`dateadd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: historylog
-- Description: Historique des activités
-- ============================================
CREATE TABLE IF NOT EXISTS `historylog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` text NOT NULL,
  `dateadd` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `dateadd` (`dateadd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: stocks
-- Description: Stocks clients
-- ============================================
CREATE TABLE IF NOT EXISTS `stocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `ref` varchar(100) NOT NULL,
  `qty` int(11) NOT NULL,
  `received` enum('on','off') NOT NULL DEFAULT 'off',
  `dateadd` int(11) NOT NULL DEFAULT 0,
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `client` (`client`),
  KEY `trash` (`trash`),
  FOREIGN KEY (`client`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: stockdlms
-- Description: Stocks livreurs
-- ============================================
CREATE TABLE IF NOT EXISTS `stockdlms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dlm` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `ref` varchar(100) NOT NULL DEFAULT '',
  `qty` int(11) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `received` enum('on','off') NOT NULL DEFAULT 'off',
  `dateadd` int(11) NOT NULL DEFAULT 0,
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `dlm` (`dlm`),
  KEY `trash` (`trash`),
  FOREIGN KEY (`dlm`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: shipments
-- Description: Envois
-- ============================================
CREATE TABLE IF NOT EXISTS `shipments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` int(11) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `ref` varchar(100) NOT NULL,
  `qty` int(11) NOT NULL,
  `received` enum('on','off') NOT NULL DEFAULT 'off',
  `dateadd` int(11) NOT NULL DEFAULT 0,
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `client` (`client`),
  KEY `trash` (`trash`),
  FOREIGN KEY (`client`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: packaging
-- Description: Emballages
-- ============================================
CREATE TABLE IF NOT EXISTS `packaging` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `trash` (`trash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: products
-- Description: Produits
-- ============================================
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `ref` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `trash` (`trash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: stores
-- Description: Magasins/boutiques
-- ============================================
CREATE TABLE IF NOT EXISTS `stores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` text,
  `phone` varchar(20),
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `trash` (`trash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: subdlm
-- Description: Relation sous-livreurs
-- ============================================
CREATE TABLE IF NOT EXISTS `subdlm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dlm` int(11) NOT NULL,
  `subdlm` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `dlm` (`dlm`),
  KEY `subdlm` (`subdlm`),
  FOREIGN KEY (`dlm`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subdlm`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: notices
-- Description: Annonces
-- ============================================
CREATE TABLE IF NOT EXISTS `notices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` text NOT NULL,
  `shown` enum('on','off') DEFAULT 'off',
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `trash` (`trash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: expenses
-- Description: Dépenses
-- ============================================
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `dlm` int(11) DEFAULT NULL,
  `dateadd` int(11) NOT NULL,
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `dlm` (`dlm`),
  KEY `trash` (`trash`),
  FOREIGN KEY (`dlm`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: smsdevices
-- Description: Appareils SMS
-- ============================================
CREATE TABLE IF NOT EXISTS `smsdevices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device` varchar(100) NOT NULL,
  `apikey` varchar(255) NOT NULL,
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `trash` (`trash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: smsmodels
-- Description: Modèles de SMS
-- ============================================
CREATE TABLE IF NOT EXISTS `smsmodels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` text,
  `state` varchar(255) NOT NULL DEFAULT '',
  `message` text,
  `active` enum('on','off') NOT NULL DEFAULT 'on',
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `active` (`active`),
  KEY `trash` (`trash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: reclamations
-- Description: Réclamations
-- ============================================
CREATE TABLE IF NOT EXISTS `reclamations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` int(11) DEFAULT NULL,
  `service` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','resolved','closed') DEFAULT 'pending',
  `dateadd` int(11) NOT NULL,
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `client` (`client`),
  KEY `trash` (`trash`),
  FOREIGN KEY (`client`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: spreadsheets
-- Description: Google Sheets
-- ============================================
CREATE TABLE IF NOT EXISTS `spreadsheets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(500) NOT NULL DEFAULT '',
  `dateadd` int(11) NOT NULL DEFAULT 0,
  `sheetid` varchar(255) NOT NULL DEFAULT '',
  `sheetname` varchar(255) NOT NULL DEFAULT '',
  `lastrow` int(11) NOT NULL DEFAULT 1,
  `autofetch` enum('on','off') NOT NULL DEFAULT 'off',
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `client` (`client`),
  KEY `autofetch` (`autofetch`),
  KEY `trash` (`trash`),
  FOREIGN KEY (`client`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: bls
-- Description: Bons de livraison
-- ============================================
CREATE TABLE IF NOT EXISTS `bls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('BRA','BL','BR','BRL') NOT NULL DEFAULT 'BL',
  `command` varchar(50) NOT NULL DEFAULT '',
  `dlm` int(11) NOT NULL DEFAULT 0,
  `subdlm` int(11) NOT NULL DEFAULT 0,
  `client` int(11) NOT NULL DEFAULT 0,
  `code` varchar(50) NOT NULL DEFAULT '',
  `cmds` text,
  `done` enum('on','off') NOT NULL DEFAULT 'off',
  `dateadd` int(11) NOT NULL,
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `command` (`command`),
  KEY `dlm` (`dlm`),
  KEY `client` (`client`),
  KEY `code` (`code`),
  KEY `done` (`done`),
  KEY `type` (`type`),
  KEY `trash` (`trash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: factures
-- Description: Factures
-- ============================================
CREATE TABLE IF NOT EXISTS `factures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('client','dlm') NOT NULL DEFAULT 'client',
  `target` int(11) NOT NULL DEFAULT 0,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `dateadd` int(11) NOT NULL DEFAULT 0,
  `code` varchar(50) NOT NULL DEFAULT '',
  `dlm` int(11) NOT NULL DEFAULT 0,
  `client` int(11) NOT NULL DEFAULT 0,
  `nbcommands` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `note` text,
  `validated` enum('on','off') NOT NULL DEFAULT 'off',
  `received` enum('on','off') NOT NULL DEFAULT 'off',
  `datecreated` int(11) NOT NULL DEFAULT 0,
  `datereceived` int(11) NOT NULL DEFAULT 0,
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `target` (`target`),
  KEY `code` (`code`),
  KEY `dlm` (`dlm`),
  KEY `client` (`client`),
  KEY `received` (`received`),
  KEY `type` (`type`),
  KEY `trash` (`trash`),
  FOREIGN KEY (`target`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: facturesdetails
-- Description: Commandes associées aux factures
-- ============================================
CREATE TABLE IF NOT EXISTS `facturesdetails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `facture` varchar(50) NOT NULL,
  `command` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `facture` (`facture`),
  KEY `command` (`command`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: audit_log
-- Description: Journal d'audit des actions
-- ============================================
CREATE TABLE IF NOT EXISTS `audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text,
  `new_values` text,
  `ip_address` varchar(45),
  `user_agent` varchar(255),
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `table_name` (`table_name`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: csrf_tokens
-- Description: Tokens CSRF pour protection
-- ============================================
CREATE TABLE IF NOT EXISTS `csrf_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `expires_at` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: ecommerce_integrations
-- Description: Intégrations e-commerce par client (Youcan, Shopify, Google Sheets)
-- ============================================
CREATE TABLE IF NOT EXISTS `ecommerce_integrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `platform` enum('shopify','youcan','google_sheets') NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `store_url` varchar(500) DEFAULT NULL,
  `webhook_url` varchar(500) DEFAULT NULL,
  `sheet_id` varchar(255) DEFAULT NULL,
  `sheet_email` varchar(255) DEFAULT NULL,
  `is_active` enum('on','off') DEFAULT 'on',
  `last_sync` int(11) DEFAULT NULL,
  `dateadd` int(11) NOT NULL,
  `trash` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `platform` (`platform`),
  KEY `trash` (`trash`),
  FOREIGN KEY (`client_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
