<?php
/**
 * EXLIV Delivery - Functions Library
 * Fonctions utilitaires centralisées
 */

/**
 * Sanitize les variables pour prévenir les injections SQL et XSS
 * @param string $var La variable à nettoyer
 * @return string La variable nettoyée
 */
function sanitize_vars($var) {
    if (is_array($var)) {
        return array_map('sanitize_vars', $var);
    }
    
    // Supprimer les patterns SQL dangereux
    if (preg_match("#script|select|update|delete|concat|create|table|union|length|show_table|mysql_list_tables|mysql_list_fields|mysql_list_dbs#i", $var)) {
        return "";
    }
    
    // Nettoyer les caractères spéciaux
    return htmlspecialchars(addslashes(trim($var)), ENT_QUOTES, 'UTF-8');
}

/**
 * Hash un mot de passe avec bcrypt
 * @param string $password Le mot de passe en clair
 * @return string Le mot de passe hashé
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Vérifie un mot de passe
 * @param string $password Le mot de passe en clair
 * @param string $hash Le hash stocké
 * @return bool True si le mot de passe est correct
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Génère un token CSRF
 * @param int $user_id L'ID de l'utilisateur (optionnel)
 * @return string Le token CSRF
 */
function generate_csrf_token($user_id = null) {
    global $bdd;
    
    $token = bin2hex(random_bytes(32));
    $expires_at = time() + 3600; // 1 heure
    
    try {
        $req = $bdd->prepare("INSERT INTO csrf_tokens (token, user_id, expires_at, created_at) VALUES (?, ?, ?, ?)");
        $req->execute([$token, $user_id, $expires_at, time()]);
        return $token;
    } catch (PDOException $e) {
        // Si la table n'existe pas encore, retourner un token simple
        return $token;
    }
}

/**
 * Vérifie un token CSRF
 * @param string $token Le token à vérifier
 * @param int $user_id L'ID de l'utilisateur (optionnel)
 * @return bool True si le token est valide
 */
function verify_csrf_token($token, $user_id = null) {
    global $bdd;
    
    try {
        $req = $bdd->prepare("SELECT * FROM csrf_tokens WHERE token = ? AND expires_at > ? LIMIT 1");
        $req->execute([$token, time()]);
        $result = $req->fetch();
        
        if ($result) {
            // Vérifier l'utilisateur si spécifié
            if ($user_id !== null && $result['user_id'] != $user_id) {
                return false;
            }
            
            // Supprimer le token après utilisation
            $req = $bdd->prepare("DELETE FROM csrf_tokens WHERE token = ?");
            $req->execute([$token]);
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        // Si la table n'existe pas, retourner false
        return false;
    }
}

/**
 * Nettoie les tokens CSRF expirés
 */
function cleanup_csrf_tokens() {
    global $bdd;
    
    try {
        $req = $bdd->prepare("DELETE FROM csrf_tokens WHERE expires_at < ?");
        $req->execute([time()]);
    } catch (PDOException $e) {
        // Ignorer si la table n'existe pas
    }
}

/**
 * Ajoute une entrée dans le journal d'audit
 * @param int $user_id L'ID de l'utilisateur
 * @param string $action L'action effectuée
 * @param string $table_name Le nom de la table
 * @param int $record_id L'ID de l'enregistrement (optionnel)
 * @param array $old_values Les anciennes valeurs (optionnel)
 * @param array $new_values Les nouvelles valeurs (optionnel)
 */
function add_audit_log($user_id, $action, $table_name, $record_id = null, $old_values = null, $new_values = null) {
    global $bdd;
    
    try {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $req = $bdd->prepare("INSERT INTO audit_log (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $req->execute([
            $user_id,
            $action,
            $table_name,
            $record_id,
            $old_values ? json_encode($old_values) : null,
            $new_values ? json_encode($new_values) : null,
            $ip_address,
            $user_agent,
            time()
        ]);
    } catch (PDOException $e) {
        // Ignorer si la table n'existe pas encore
    }
}

/**
 * Génère un code de commande unique
 * @return string Le code de commande
 */
function generate_command_code() {
    global $bdd;
    
    try {
        $back = $bdd->query("SELECT id FROM commands WHERE trash='1'");
        $count = $back->rowCount();
        return 'CMD-' . date('dmY') . '-' . sprintf("%05d", ($count + 1));
    } catch (PDOException $e) {
        return 'CMD-' . date('dmY') . '-' . sprintf("%05d", rand(1, 99999));
    }
}

/**
 * Formate un timestamp en date lisible
 * @param int $timestamp Le timestamp UNIX
 * @param string $format Le format de date (optionnel)
 * @return string La date formatée
 */
function format_date($timestamp, $format = 'd/m/Y H:i') {
    return date($format, $timestamp);
}

/**
 * Formate un prix
 * @param float $price Le prix
 * @param string $currency La devise (optionnel)
 * @return string Le prix formaté
 */
function format_price($price, $currency = 'MAD') {
    return number_format($price, 2, ',', ' ') . ' ' . $currency;
}

/**
 * Valide un email
 * @param string $email L'email à valider
 * @return bool True si l'email est valide
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valide un numéro de téléphone (format marocain)
 * @param string $phone Le numéro de téléphone
 * @return bool True si le numéro est valide
 */
function validate_phone($phone) {
    return preg_match('/^0[5-7][0-9]{8}$/', $phone) === 1;
}

/**
 * Génère un mot de passe aléatoire
 * @param int $length La longueur du mot de passe
 * @return string Le mot de passe généré
 */
function generate_password($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

/**
 * Redirige vers une URL avec message de succès/erreur
 * @param string $url L'URL de redirection
 * @param string $message Le message (optionnel)
 * @param string $type Le type de message (success/error) (optionnel)
 */
function redirect_with_message($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Affiche un message flash s'il existe
 */
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        
        $class = $type === 'success' ? 'success' : 'error';
        echo '<div class="lx-flash-message lx-' . $class . '">';
        echo '<p>' . htmlspecialchars($message) . '</p>';
        echo '</div>';
    }
}

/**
 * Limite le texte à une certaine longueur
 * @param string $text Le texte à limiter
 * @param int $length La longueur maximale
 * @param string $suffix Le suffixe à ajouter (optionnel)
 * @return string Le texte limité
 */
function truncate_text($text, $length, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Upload un fichier
 * @param array $file Le fichier $_FILES
 * @param string $destination Le dossier de destination
 * @param array $allowed_types Les types MIME autorisés (optionnel)
 * @param int $max_size La taille maximale en octets (optionnel)
 * @return array Le résultat ['success' => bool, 'filename' => string, 'error' => string]
 */
function upload_file($file, $destination, $allowed_types = ['image/jpeg', 'image/png', 'image/gif'], $max_size = 5242880) {
    $result = [
        'success' => false,
        'filename' => '',
        'error' => ''
    ];
    
    // Vérifier si le fichier a été uploadé
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $result['error'] = 'Aucun fichier uploadé';
        return $result;
    }
    
    // Vérifier la taille
    if ($file['size'] > $max_size) {
        $result['error'] = 'Fichier trop grand (max ' . ($max_size / 1024 / 1024) . 'MB)';
        return $result;
    }
    
    // Vérifier le type MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        $result['error'] = 'Type de fichier non autorisé';
        return $result;
    }
    
    // Générer un nom de fichier unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = $destination . '/' . $filename;
    
    // Créer le dossier s'il n'existe pas
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $result['success'] = true;
        $result['filename'] = $filename;
    } else {
        $result['error'] = 'Erreur lors de l\'upload du fichier';
    }
    
    return $result;
}

/**
 * Envoie un SMS (placeholder pour intégration future)
 * @param string $phone Le numéro de téléphone
 * @param string $message Le message
 * @return bool True si l'envoi réussit
 */
function send_sms($phone, $message) {
    // TODO: Intégrer avec un fournisseur SMS (Twilio, Nexmo, etc.)
    // Pour l'instant, retourner true pour simulation
    error_log("SMS envoyé à $phone: $message");
    return true;
}

/**
 * Envoie une notification OneSignal (placeholder)
 * @param array $user_ids Les IDs des utilisateurs
 * @param string $title Le titre de la notification
 * @param string $message Le message
 * @return bool True si l'envoi réussit
 */
function send_onesignal_notification($user_ids, $title, $message) {
    // TODO: Intégrer avec OneSignal API
    error_log("Notification OneSignal: $title - $message");
    return true;
}

/**
 * Journalise une erreur
 * @param string $message Le message d'erreur
 * @param array $context Le contexte additionnel (optionnel)
 */
function log_error($message, $context = []) {
    $log_message = date('Y-m-d H:i:s') . ' - ' . $message;
    if (!empty($context)) {
        $log_message .= ' - Context: ' . json_encode($context);
    }
    error_log($log_message);
    
    // Écrire dans un fichier de log si possible
    $log_file = __DIR__ . '/error_log';
    if (is_writable($log_file) || is_writable(dirname($log_file))) {
        file_put_contents($log_file, $log_message . PHP_EOL, FILE_APPEND);
    }
}
