<?php
/** EXLIV Delivery public seller registration. */
session_start();
include("config.php");
require_once __DIR__ . '/auth_security.php';

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = sanitize_vars($_POST['fullname'] ?? '');
    $email = sanitize_vars($_POST['email'] ?? '');
    $phone = sanitize_vars($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $city = sanitize_vars($_POST['city'] ?? '');
    $address = sanitize_vars($_POST['address'] ?? '');

    if (!public_auth_valid_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Votre session a expiré. Actualisez la page puis réessayez.';
    } elseif (empty($fullname) || empty($email) || empty($phone) || empty($password) || empty($city)) {
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
            $req = $bdd->prepare("SELECT id FROM users WHERE email = ?");
            $req->execute([$email]);
            if ($req->rowCount() > 0) {
                $error = 'Cette adresse email est déjà utilisée.';
            } else {
                $req = $bdd->prepare("INSERT INTO users(id,fullname,picture,email,password,phone,city,type,roles,active,datesignup,trash) VALUES ('0',?,'avatar.png',?,?,?,?, 'client','Clients','off',?,'1')");
                $req->execute([$fullname, $email, hash_password($password), $phone, $city, time()]);
                add_audit_log($bdd->lastInsertId(), 'create', 'users', null, null, ['email' => $email, 'type' => 'client', 'active' => 'off']);
                $success = 'Votre demande d\'inscription a été envoyée. Un administrateur doit approuver votre compte avant votre première connexion.';
                header('refresh:5;url=login.php');
            }
        } catch (PDOException $e) {
            $error = 'Erreur lors de l\'inscription: ' . $e->getMessage();
        }
    }
}

$cities = [];
try { $cities = $bdd->query("SELECT city FROM cities WHERE trash='1' ORDER BY city")->fetchAll(PDO::FETCH_COLUMN); } catch (PDOException $e) {}
$appName = $settings['appname'] ?? 'EXLIV Delivery';
$fallbackCities = ['Casablanca', 'Rabat', 'Marrakech', 'Fès', 'Tanger', 'Agadir'];
$cities = $cities ?: $fallbackCities;
?>
<style>
    .visual-side{display:block!important;padding:0!important;background:url('images/box-hero.png') center/cover no-repeat!important}
    .visual-side:before{background:rgba(16,37,72,.70)!important}
    .visual-content{position:absolute!important;left:48px;right:48px;bottom:55px;max-width:390px;text-align:left!important}
    .visual-content img{display:none!important}
    .visual-content .line{margin:0 0 21px!important}
</style>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Créer un compte vendeur | <?php echo htmlspecialchars($appName); ?></title><link rel="icon" href="images/logo-mark-CFsHaYkQ.png"><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"><style>
:root{--navy:#172b56;--deep:#102548;--orange:#e88a28;--ink:#263a61;--muted:#66738c;--border:#e4e9f1}*{box-sizing:border-box}body{margin:0;color:var(--ink);font-family:Barlow,Arial,sans-serif}.register-page{min-height:100vh;display:grid;grid-template-columns:1fr 1fr}.form-side{display:flex;justify-content:center;padding:48px 24px}.form-wrap{width:min(100%,480px)}.logo img{display:block;height:49px;width:auto}.form-wrap h1{margin:37px 0 8px;font:700 34px Archivo,Arial}.intro{margin:0;color:var(--muted);font-size:16px}.form{margin-top:28px}.grid{display:grid;grid-template-columns:1fr 1fr;gap:0 16px}.field{margin-top:17px}.field label{display:block;margin-bottom:7px;font-size:14px;font-weight:600}.field input,.field select{width:100%;border:1px solid #cbd3e1;border-radius:3px;padding:12px 13px;font:15px Barlow;color:var(--ink);background:#fff}.field input:focus,.field select:focus{outline:2px solid rgba(232,138,40,.22);border-color:var(--orange)}.password-wrap{position:relative}.password-wrap input{padding-right:45px}.password-toggle{position:absolute;right:0;top:0;width:44px;height:100%;border:0;background:transparent;color:var(--muted);cursor:pointer}.submit{width:100%;margin-top:25px;border:0;border-radius:3px;background:var(--orange);color:#fff;padding:14px;font:600 16px Barlow;cursor:pointer;transition:.2s}.submit:hover{background:#cf7318}.notice{margin-top:20px;padding:13px 15px;font-size:14px;line-height:1.45}.error{border-left:4px solid #c43c3c;background:#fff0f0;color:#9f2828}.success{border-left:4px solid #228a50;background:#edfff3;color:#17683b}.login{margin:24px 0 0;color:var(--muted);font-size:15px}.login a{color:var(--orange);font-weight:600;text-decoration:none}.login a:hover{text-decoration:underline}.visual-side{position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--deep),#223d70);padding:65px}.visual-side:before{content:"";position:absolute;inset:0;background:radial-gradient(circle at 80% 15%,rgba(232,138,40,.28),transparent 34%),radial-gradient(circle at 15% 90%,rgba(255,255,255,.1),transparent 32%)}.visual-content{position:relative;z-index:1;max-width:440px;color:#fff;text-align:center}.visual-content img{display:block;width:min(100%,330px);margin:0 auto 40px;filter:drop-shadow(0 25px 25px rgba(0,0,0,.25))}.line{width:56px;height:4px;background:var(--orange);margin:0 auto 21px}.visual-content h2{margin:0;font:700 clamp(27px,3vw,39px)/1.14 Archivo,Arial}.visual-content p{margin:17px 0 0;color:#d5deec;font-size:17px;line-height:1.55}@media(max-width:850px){.register-page{grid-template-columns:1fr}.visual-side{display:none}.form-side{min-height:100vh}}@media(max-width:480px){.form-side{padding:38px 21px}.form-wrap h1{margin-top:35px;font-size:30px}.grid{grid-template-columns:1fr}.field{margin-top:16px}}
</style></head><body><main class="register-page"><section class="form-side"><div class="form-wrap"><a href="home.php" class="logo"><img src="images/logo-mark-CFsHaYkQ.png" alt="<?php echo htmlspecialchars($appName); ?>"></a><h1>Devenir vendeur</h1><p class="intro">Créez votre compte gratuit et commencez à expédier dès aujourd'hui.</p><?php if ($error): ?><div class="notice error" role="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?><?php if ($success): ?><div class="notice success" role="status"><?php echo htmlspecialchars($success); ?> Redirection vers la connexion...</div><?php endif; ?><form method="post" class="form"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(public_auth_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>"><div class="grid"><div class="field"><label for="fullname">Nom complet</label><input id="fullname" name="fullname" required autocomplete="name" placeholder="Votre nom complet" value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>"></div><div class="field"><label for="phone">Téléphone</label><input id="phone" name="phone" required type="tel" autocomplete="tel" placeholder="06XXXXXXXX" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"></div></div><div class="field"><label for="email">Adresse e-mail</label><input id="email" name="email" required type="email" autocomplete="email" placeholder="vous@exemple.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"></div><div class="grid"><div class="field"><label for="city">Ville</label><select id="city" name="city" required><option value="">Sélectionnez votre ville</option><?php foreach ($cities as $city): ?><option value="<?php echo htmlspecialchars($city); ?>" <?php echo (($_POST['city'] ?? '') === $city) ? 'selected' : ''; ?>><?php echo htmlspecialchars($city); ?></option><?php endforeach; ?></select></div><div class="field"><label for="address">Adresse <span style="color:#66738c;font-weight:400">(facultatif)</span></label><input id="address" name="address" autocomplete="street-address" placeholder="Votre adresse" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>"></div></div><div class="grid"><div class="field"><label for="password">Mot de passe</label><div class="password-wrap"><input id="password" name="password" required type="password" minlength="6" autocomplete="new-password" placeholder="6 caractères minimum"><button class="password-toggle" data-target="password" type="button" aria-label="Afficher le mot de passe"><i class="fa-regular fa-eye"></i></button></div></div><div class="field"><label for="confirm_password">Confirmer le mot de passe</label><div class="password-wrap"><input id="confirm_password" name="confirm_password" required type="password" autocomplete="new-password" placeholder="Répétez le mot de passe"><button class="password-toggle" data-target="confirm_password" type="button" aria-label="Afficher le mot de passe"><i class="fa-regular fa-eye"></i></button></div></div></div><button class="submit" type="submit"><i class="fa-solid fa-user-plus"></i> Créer mon compte</button></form><p class="login">Vous avez déjà un compte ? <a href="login.php">Connectez-vous</a></p></div></section><aside class="visual-side"><div class="visual-content"><img src="images/box-hero.png" alt="Colis EXLIV Delivery"><div class="line"></div><h2>Expédiez plus simplement, livrez plus vite.</h2><p>Rejoignez les vendeurs qui font confiance à EXLIV Delivery pour leurs livraisons partout au Maroc.</p></div></aside></main><script>document.querySelectorAll('.password-toggle').forEach(function(button){button.addEventListener('click',function(){var input=document.getElementById(this.dataset.target),show=input.type==='password';input.type=show?'text':'password';this.setAttribute('aria-label',show?'Masquer le mot de passe':'Afficher le mot de passe');this.firstElementChild.className=show?'fa-regular fa-eye-slash':'fa-regular fa-eye';});});</script></body></html>
