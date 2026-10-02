<?php
session_start();
include("config.php");
require_once __DIR__ . '/auth_security.php';

if (isset($_POST['username'], $_POST['password'])) {
    $email = trim((string) $_POST['username']);
    $password = (string) $_POST['password'];
    if (!public_auth_valid_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Votre session a expiré. Actualisez la page puis réessayez.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'E-mail ou mot de passe est incorrect.';
    } else {
        $blockedFor = public_auth_login_block_remaining($bdd, $email);
        if ($blockedFor > 0) {
            $error = 'Trop de tentatives de connexion. Veuillez réessayer dans ' . ceil($blockedFor / 60) . ' minute(s).';
        } else {
            $account = $bdd->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
            $account->execute([$email]);
            $row = $account->fetch(PDO::FETCH_ASSOC);
            $validPassword = $row && (password_verify($password, $row['password']) || hash_equals((string) $row['password'], $password));
            if (!$validPassword) {
                public_auth_record_login_failure($bdd, $email);
                $error = 'E-mail ou mot de passe est incorrect.';
            } elseif ($row['trash'] !== '1') {
                $error = 'Votre compte n\'est pas disponible.';
            } elseif ($row['active'] !== 'on') {
                $error = 'Votre compte est en attente d\'approbation par un administrateur.';
            } else {
                if (!password_verify($password, $row['password'])) {
                    $upgrade = $bdd->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $upgrade->execute([hash_password($password), $row['id']]);
                }
                public_auth_clear_login_failures($bdd, $email);
                $_SESSION['id'] = $row['id'];
                $_SESSION['fullname'] = $row['fullname'];
                $_SESSION['picture'] = $row['picture'];
                $_SESSION['phone'] = $row['phone'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['roles'] = $row['roles'];
                $_SESSION['type'] = $row['type'];
                $_SESSION['upuser'] = $row['upuser'] ?? null;
                session_regenerate_id(true);
                header($_SESSION['type'] !== 'moderator' ? 'location: commands.php' : 'location: index.php');
                exit;
            }
        }
    }
}

if (file_exists("configdb.data")) { unlink("configdb.data"); }
if (file_exists("installer.php")) { unlink("installer.php"); }
$appName = $settings['appname'] ?? 'EXLIV Delivery';
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Connexion espace vendeur | <?php echo htmlspecialchars($appName); ?></title><link rel="icon" href="images/logo-mark-CFsHaYkQ.png"><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"><style>
:root{--navy:#172b56;--deep:#102548;--orange:#e88a28;--ink:#263a61;--muted:#66738c;--border:#e4e9f1}*{box-sizing:border-box}body{margin:0;color:var(--ink);font-family:Barlow,Arial,sans-serif}.login-page{min-height:100vh;display:grid;grid-template-columns:1fr 1fr}.form-side{display:flex;align-items:center;justify-content:center;padding:58px 24px}.form-wrap{width:min(100%,425px)}.logo img{display:block;height:49px;width:auto}.form-wrap h1{margin:47px 0 8px;font:700 34px Archivo,Arial}.intro{margin:0;color:var(--muted);font-size:16px}.form{margin-top:32px}.field{margin-top:19px}.field label{display:block;margin-bottom:8px;font-size:14px;font-weight:600}.field input{width:100%;border:1px solid #cbd3e1;border-radius:3px;padding:13px 14px;font:16px Barlow;color:var(--ink)}.field input:focus{outline:2px solid rgba(232,138,40,.22);border-color:var(--orange)}.password-wrap{position:relative}.password-wrap input{padding-right:45px}.password-toggle{position:absolute;right:0;top:0;width:44px;height:100%;border:0;background:transparent;color:var(--muted);cursor:pointer}.form-meta{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-top:18px;color:var(--muted);font-size:13px}.forgot{color:var(--orange);font-weight:600}.forgot:hover{text-decoration:underline}.submit{width:100%;margin-top:25px;border:0;border-radius:3px;background:var(--orange);color:#fff;padding:14px;font:600 16px Barlow;cursor:pointer;transition:.2s}.submit:hover{background:#cf7318}.error{margin-top:22px;border-left:4px solid #c43c3c;background:#fff0f0;color:#9f2828;padding:13px 15px;font-size:14px}.signup{margin-top:25px;color:var(--muted);font-size:15px}.signup a{color:var(--orange);font-weight:600;text-decoration:none}.signup a:hover{text-decoration:underline}.visual-side{position:relative;overflow:hidden;background:var(--deep)}.visual-side:before{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(16,37,72,.38),rgba(16,37,72,.88));z-index:1}.visual-side:after{content:"";position:absolute;inset:0;background:url('images/van.png') center/cover no-repeat;opacity:.7}.visual-copy{position:absolute;z-index:2;left:clamp(34px,8vw,110px);right:clamp(34px,8vw,110px);bottom:70px;color:#fff}.line{width:56px;height:4px;background:var(--orange);margin-bottom:20px}.visual-copy p{max-width:440px;margin:0;font:700 clamp(27px,3vw,38px)/1.15 Archivo,Arial}.visual-copy small{display:block;margin-top:17px;color:#d5deec;font-size:15px;line-height:1.5}@media(max-width:850px){.login-page{grid-template-columns:1fr}.visual-side{display:none}.form-side{min-height:100vh}}@media(max-width:480px){.form-side{padding:38px 21px}.form-wrap h1{margin-top:35px;font-size:30px}.form-meta{display:block}.forgot{display:inline-block;margin-top:10px}}
 </style></head><body><main class="login-page"><section class="form-side"><div class="form-wrap"><a href="home.php" class="logo"><img src="images/logo-mark-CFsHaYkQ.png" alt="<?php echo htmlspecialchars($appName); ?>"></a><h1>Espace vendeur</h1><p class="intro">Connectez-vous pour gérer vos colis et vos encaissements.</p><?php if (isset($error)): ?><div class="error" role="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?><form action="login.php" method="post" class="form"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(public_auth_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>"><div class="field"><label for="username">Adresse e-mail</label><input id="username" name="username" type="email" autocomplete="email" required placeholder="vous@exemple.com" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"></div><div class="field"><label for="password">Mot de passe</label><div class="password-wrap"><input id="password" name="password" type="password" autocomplete="current-password" required placeholder="••••••••"><button class="password-toggle" type="button" aria-label="Afficher le mot de passe" aria-pressed="false"><i class="fa-regular fa-eye"></i></button></div></div><div class="form-meta"><span>Vos accès sont sécurisés par EXLIV Delivery.</span><a href="password.php" class="forgot">Mot de passe oublié ?</a></div><button class="submit" type="submit"><i class="fa-solid fa-right-to-bracket"></i> Se connecter</button></form><p class="signup">Pas encore de compte ? <a href="register.php">Créer un compte vendeur</a></p></div></section><aside class="visual-side"><div class="visual-copy"><div class="line"></div><p>Livraison en 24h, ramassage gratuit et cash à la livraison partout au Maroc.</p><small>Une plateforme pensée pour les vendeurs e-commerce.</small></div></aside></main><script>document.querySelector('.password-toggle').addEventListener('click',function(){var input=document.getElementById('password'),show=input.type==='password';input.type=show?'text':'password';this.setAttribute('aria-pressed',String(show));this.setAttribute('aria-label',show?'Masquer le mot de passe':'Afficher le mot de passe');this.firstElementChild.className=show?'fa-regular fa-eye-slash':'fa-regular fa-eye';});</script></body></html>
