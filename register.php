<?php
/**
 * EXLIV Delivery - Public Registration Page
 * Page d'inscription publique pour les nouveaux clients
 */

session_start();
include("config.php");

$error = '';
$success = '';

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = sanitize_vars($_POST['fullname'] ?? '');
    $email = sanitize_vars($_POST['email'] ?? '');
    $phone = sanitize_vars($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $city = sanitize_vars($_POST['city'] ?? '');
    $address = sanitize_vars($_POST['address'] ?? '');
    
    // Validation
    if (empty($fullname) || empty($email) || empty($phone) || empty($password)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!validate_email($email)) {
        $error = 'Adresse email invalide.';
    } elseif (!validate_phone($phone)) {
        $error = 'Numéro de téléphone invalide.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        try {
            // Vérifier si l'email existe déjà
            $req = $bdd->prepare("SELECT id FROM users WHERE email = ?");
            $req->execute([$email]);
            
            if ($req->rowCount() > 0) {
                $error = 'Cette adresse email est déjà utilisée.';
            } else {
                // Hasher le mot de passe
                $hashed_password = hash_password($password);
                
                // Insérer le nouvel utilisateur
                $req = $bdd->prepare("INSERT INTO users(id,fullname,picture,email,password,phone,city,type,roles,active,datesignup,trash) 
                VALUES ('0',?,?,?,?,?,'client','Clients','on',?,'1')");
                $req->execute([
                    $fullname,
                    'avatar.png',
                    $email,
                    $hashed_password,
                    $phone,
                    $city,
                    time()
                ]);
                
                // Logger l'action
                add_audit_log($bdd->lastInsertId(), 'create', 'users', null, null, ['email' => $email, 'type' => 'client']);
                
                $success = 'Compte créé avec succès! Vous pouvez maintenant vous connecter.';
                
                // Rediriger vers la page de connexion après 2 secondes
                header('refresh:2;url=login.php');
            }
        } catch (PDOException $e) {
            $error = 'Erreur lors de l\'inscription: ' . $e->getMessage();
        }
    }
}

// Récupérer les villes
$cities = [];
try {
    $back = $bdd->query("SELECT city FROM cities WHERE trash='1' ORDER BY city");
    $cities = $back->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Ignorer si la table n'existe pas
}

$appName = $settings['appname'] ?? 'EXLIV Delivery';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - <?php echo $appName; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #667eea;
            font-size: 32px;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 16px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group input::placeholder {
            color: #aaa;
        }
        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        .success {
            background: #efe;
            color: #3c3;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .divider {
            text-align: center;
            margin: 20px 0;
            color: #999;
            position: relative;
        }
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e0e0e0;
        }
        .divider span {
            background: white;
            padding: 0 15px;
            position: relative;
        }
        .social-login {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        .social-btn {
            flex: 1;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-weight: 600;
            color: #333;
        }
        .social-btn:hover {
            background: #f8f9fa;
            border-color: #667eea;
        }
        @media (max-width: 600px) {
            .container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Inscription</h1>
            <p>Créez votre compte <?php echo $appName; ?></p>
        </div>
        
        <?php if ($error): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Nom complet *</label>
                <input type="text" name="fullname" placeholder="Votre nom complet" value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" placeholder="votre@email.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Téléphone *</label>
                <input type="tel" name="phone" placeholder="06XXXXXXXX" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Ville *</label>
                <select name="city" required>
                    <option value="">Sélectionner une ville</option>
                    <?php if (!empty($cities)): ?>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?php echo htmlspecialchars($city); ?>" <?php echo ($_POST['city'] ?? '') == $city ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($city); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="Casablanca">Casablanca</option>
                        <option value="Rabat">Rabat</option>
                        <option value="Marrakech">Marrakech</option>
                        <option value="Fès">Fès</option>
                        <option value="Tanger">Tanger</option>
                        <option value="Agadir">Agadir</option>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Adresse</label>
                <input type="text" name="address" placeholder="Votre adresse" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>Mot de passe *</label>
                <input type="password" name="password" placeholder="Au moins 6 caractères" required minlength="6">
            </div>
            
            <div class="form-group">
                <label>Confirmer le mot de passe *</label>
                <input type="password" name="confirm_password" placeholder="Répétez le mot de passe" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Créer mon compte</button>
        </form>
        
        <div class="divider">
            <span>ou</span>
        </div>
        
        <div class="social-login">
            <button class="social-btn">
                <i class="fab fa-google"></i> Google
            </button>
            <button class="social-btn">
                <i class="fab fa-facebook-f"></i> Facebook
            </button>
        </div>
        
        <div class="login-link">
            <p>Vous avez déjà un compte? <a href="login.php">Connectez-vous</a></p>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="home.php" style="color: #667eea; text-decoration: none; font-weight: 600;">← Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>
