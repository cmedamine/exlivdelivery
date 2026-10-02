<?php
session_start();
include("config.php");
$cities = [];
try {
    $cities = $bdd->query("SELECT city FROM cities WHERE trash='1' ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
}
$appName = $settings['appname'] ?? 'EXLIV Delivery';
$displayCities = $cities ?: ['Casablanca', 'Rabat', 'Marrakech', 'Fès', 'Tanger', 'Agadir', 'Meknès', 'Oujda', 'Kénitra', 'Tétouan', 'Safi', 'El Jadida'];
?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="EXLIV Delivery : livraison de colis e-commerce et cash à la livraison au Maroc.">
    <title><?php echo htmlspecialchars($appName); ?> | Livraison de colis au Maroc</title>
    <link rel="icon" href="images/logo-mark-CFsHaYkQ.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --navy: #172b56;
            --deep: #102548;
            --orange: #e88a28;
            --ink: #263a61;
            --muted: #66738c;
            --border: #e4e9f1;
            --paper: #f6f8fc
        }

        * {
            box-sizing: border-box
        }

        html {
            scroll-behavior: smooth
        }

        body {
            margin: 0;
            color: var(--ink);
            font-family: Barlow, Arial, sans-serif
        }

        a {
            color: inherit;
            text-decoration: none
        }

        .container {
            width: min(1280px, calc(100% - 40px));
            margin: auto
        }

        .topbar {
            background: var(--deep);
            color: #fff;
            font-size: 12px;
            letter-spacing: .08em;
            text-transform: uppercase
        }

        .topbar .container {
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: space-between
        }

        .topbar a {
            letter-spacing: normal;
            text-transform: none;
            font-size: 13px
        }

        .topbar a:hover,
        .nav a:hover {
            color: var(--orange)
        }

        header {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(255, 255, 255, .97);
            border-bottom: 1px solid var(--border)
        }

        .navwrap {
            height: 78px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px
        }

        .brand img {
            display: block;
            width: auto;
            height: 44px
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 27px;
            font-size: 15px;
            font-weight: 600
        }

        .nav-actions {
            display: flex;
            gap: 10px
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            border: 1px solid transparent;
            padding: 12px 18px;
            font: 600 15px Barlow;
            border-radius: 3px;
            transition: .2s
        }

        .btn-outline {
            border-color: #cbd3e1;
            background: #fff
        }

        .btn-outline:hover {
            border-color: var(--orange);
            color: var(--orange)
        }

        .btn-orange {
            background: var(--orange);
            color: #fff
        }

        .btn-orange:hover {
            background: #cf7318;
            transform: translateY(-1px)
        }

        .hero {
            overflow: hidden;
            background: linear-gradient(135deg, var(--deep), #223d70);
            color: #fff
        }

        .hero .container {
            min-height: 530px;
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            align-items: center;
            gap: 35px
        }

        .badge {
            display: inline-flex;
            gap: 9px;
            align-items: center;
            padding: 7px 12px;
            border-radius: 99px;
            background: rgba(255, 255, 255, .11);
            font-size: 13px
        }

        .badge b {
            width: 8px;
            height: 8px;
            border-radius: 99px;
            background: var(--orange)
        }

        h1,
        h2,
        h3 {
            font-family: Archivo, Arial, sans-serif
        }

        .hero h1 {
            max-width: 680px;
            margin: 18px 0 0;
            font-size: clamp(38px, 5vw, 64px);
            line-height: 1.06;
            letter-spacing: -.035em
        }

        .hero h1 em {
            font-style: normal;
            color: var(--orange)
        }

        .hero p {
            max-width: 610px;
            margin: 23px 0;
            font-size: 18px;
            line-height: 1.55;
            color: #d4dcea
        }

        .hero .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px
        }

        .hero .btn-outline {
            color: #fff;
            background: transparent;
            border-color: rgba(255, 255, 255, .35)
        }

        .hero .btn-outline:hover {
            background: rgba(255, 255, 255, .1);
            color: #fff
        }

        .checks {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 28px;
            color: #d4dcea;
            font-size: 14px
        }

        .checks i {
            color: var(--orange);
            margin-right: 5px
        }

        .hero-art {
            align-self: end;
            text-align: center
        }

        .hero-art img {
            display: block;
            width: min(100%, 520px);
            margin: auto;
            filter: drop-shadow(0 24px 24px rgba(0, 0, 0, .28))
        }

        .stats {
            border-bottom: 1px solid var(--border)
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            border-inline: 1px solid var(--border)
        }

        .stat {
            padding: 30px 20px;
            text-align: center;
            border-right: 1px solid var(--border)
        }

        .stat:last-child {
            border: 0
        }

        .stat strong {
            display: block;
            font: 700 37px Archivo;
            color: var(--orange)
        }

        .stat span {
            font-size: 14px;
            color: var(--muted)
        }

        section {
            padding: 88px 0
        }

        .eyebrow {
            width: 56px;
            height: 4px;
            background: var(--orange);
            margin-bottom: 18px
        }

        .section-title {
            max-width: 650px
        }

        .section-title h2 {
            margin: 0;
            font-size: clamp(30px, 4vw, 43px);
            letter-spacing: -.03em
        }

        .section-title p,
        .section-copy {
            font-size: 17px;
            color: var(--muted);
            line-height: 1.55
        }

        .services,
        .cities {
            background: var(--paper)
        }

        .cards {
            margin-top: 38px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px
        }

        .card {
            background: #fff;
            border: 1px solid var(--border);
            padding: 30px;
            box-shadow: 0 8px 24px rgba(17, 39, 78, .05)
        }

        .icon {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            background: #fff3e6;
            color: var(--orange);
            border-radius: 50%;
            font-size: 19px
        }

        .card h3 {
            margin: 20px 0 10px;
            font-size: 20px
        }

        .card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.55
        }

        .tracking {
            display: grid;
            grid-template-columns: .9fr 1.1fr;
            gap: 70px;
            align-items: center
        }

        .tracking-form {
            display: flex;
            gap: 10px;
            margin-top: 25px
        }

        .tracking-form input {
            min-width: 0;
            flex: 1;
            border: 1px solid #cbd3e1;
            border-radius: 3px;
            padding: 14px;
            font: 16px Barlow
        }

        .tracking-form input:focus {
            outline: 2px solid rgba(232, 138, 40, .25);
            border-color: var(--orange)
        }

        .tracking-card {
            border: 1px solid var(--border);
            padding: 31px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(17, 39, 78, .05)
        }

        .tracking-card>p {
            margin: 0;
            color: var(--muted);
            line-height: 1.55
        }

        .tracking-list {
            display: grid;
            gap: 19px;
            margin-top: 25px
        }

        .track-row {
            display: flex;
            gap: 13px;
            align-items: center
        }

        .track-row i {
            display: grid;
            place-items: center;
            width: 39px;
            height: 39px;
            border-radius: 50%;
            background: #fff3e6;
            color: var(--orange)
        }

        .track-row b,
        .track-row small {
            display: block
        }

        .track-row small {
            color: var(--muted);
            margin-top: 3px
        }

        .coverage-grid {
            display: grid;
            grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
            gap: 70px;
            align-items: center
        }

        .coverage-map {
            display: block;
            width: 100%;
            max-width: 430px;
            max-height: 560px;
            margin: auto
        }

        .coverage-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px
        }

        .city-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 30px
        }

        .city {
            background: #fff;
            border: 1px solid var(--border);
            padding: 10px 14px;
            font-weight: 600
        }

        .city i {
            color: var(--orange);
            margin-right: 6px
        }

        .calc-grid,.split-grid{display:grid;grid-template-columns:1.35fr 1fr;gap:28px}.panel{border:1px solid var(--border);background:#fff;padding:30px}.quote{background:var(--deep);color:#fff}.form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}.field-label{display:block;margin:16px 0 7px;font-weight:600}.control{width:100%;border:1px solid #cbd3e1;padding:13px;font:15px Barlow;color:var(--ink)}.process-grid,.testimonials{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}.step,.testimonial{border:1px solid var(--border);background:#fff;padding:25px}.step strong{font:700 40px Archivo;color:rgba(232,138,40,.25)}.step h3{margin:15px 0 8px;font-size:18px}.step p,.testimonial p{margin:0;color:var(--muted);line-height:1.55}.why{background:var(--deep);color:#fff}.benefits{display:grid;grid-template-columns:repeat(2,1fr);gap:18px;align-content:center}.benefit i{color:var(--orange);margin-right:9px}.testimonial .quote-mark{color:rgba(232,138,40,.35);font-size:35px}.testimonial footer{margin-top:19px;font-size:14px;font-weight:600}.signup-box{text-align:center;border:1px solid var(--border);padding:48px;background:#fff}.signup-box p{max-width:600px;margin:14px auto 26px;color:var(--muted)}

        .cta {
            padding: 78px 0;
            background: linear-gradient(135deg, var(--deep), #223d70);
            color: #fff;
            text-align: center
        }

        .cta h2 {
            margin: 0;
            font-size: clamp(30px, 4vw, 44px)
        }

        .cta p {
            margin: 14px auto 26px;
            max-width: 570px;
            color: #d4dcea;
            font-size: 17px
        }

        .footer {
            padding: 55px 0 22px;
            background: var(--deep);
            color: #fff
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.25fr repeat(3, 1fr);
            gap: 40px
        }

        .footer .brand {
            display: inline-block;
            background: #fff;
            padding: 9px 12px
        }

        .footer .brand img {
            height: 36px
        }

        .footer p,
        .footer li {
            color: #bac5d7;
            font-size: 14px;
            line-height: 1.65
        }

        .footer h3 {
            font: 700 14px Archivo;
            color: var(--orange);
            text-transform: uppercase;
            letter-spacing: .08em
        }

        .footer ul {
            padding: 0;
            list-style: none
        }

        .footer li {
            margin: 8px 0
        }

        .footer li a:hover {
            color: var(--orange)
        }

        .copyright {
            margin-top: 42px;
            padding-top: 18px;
            border-top: 1px solid rgba(255, 255, 255, .12);
            display: flex;
            justify-content: space-between;
            color: #9eacc3;
            font-size: 12px
        }

        @media(max-width:940px) {
            .nav {
                display: none
            }

            .hero .container,
            .tracking,
            .coverage-grid,.calc-grid,.split-grid {
                grid-template-columns: 1fr
            }

            .hero .container {
                padding-top: 70px
            }

            .hero-art {
                display: none
            }

            .cards {
                grid-template-columns: repeat(2, 1fr)
            }

            .process-grid {grid-template-columns:repeat(2,1fr)}

            .footer-grid {
                grid-template-columns: repeat(2, 1fr)
            }
        }

        @media(max-width:620px) {
            .container {
                width: min(100% - 28px, 1280px)
            }

            .topbar {
                display: none
            }

            .navwrap {
                height: 68px
            }

            .brand img {
                height: 36px
            }

            .nav-actions .btn-outline {
                display: none
            }

            .nav-actions .btn {
                padding: 10px 12px;
                font-size: 13px
            }

            .hero .container {
                min-height: 520px;
                padding: 55px 0
            }

            .stat-grid {
                grid-template-columns: repeat(2, 1fr)
            }

            .stat:nth-child(2) {
                border-right: 0
            }

            .stat:nth-child(-n+2) {
                border-bottom: 1px solid var(--border)
            }

            section {
                padding: 62px 0
            }

            .cards {
                grid-template-columns: 1fr
            }

            .form-grid,.process-grid,.testimonials,.benefits {grid-template-columns:1fr}

            .tracking-form {
                flex-direction: column
            }

            .footer-grid {
                grid-template-columns: 1fr
            }

            .copyright {
                display: block
            }

            .copyright span {
                display: block;
                margin: 7px 0
            }
        }
    </style>
</head>

<body>
    <div class="topbar">
        <div class="container"><span>Rapidité, fiabilité, cash à la livraison</span><a href="tel:0788859293"><i class="fa-solid fa-phone"></i> 0788859293</a></div>
    </div>
    <header>
        <div class="container navwrap"><a class="brand" href="home.php"><img src="images/logo-mark-CFsHaYkQ.png" alt="<?php echo htmlspecialchars($appName); ?>"></a>
            <nav class="nav"><a href="home.php">Accueil</a><a href="#services">Services</a><a href="#tarifs">Tarifs</a><a href="track.php">Suivi de colis</a><a href="#contact">Contact</a></nav>
            <div class="nav-actions"><?php if (isset($_SESSION['id'])): ?><a class="btn btn-orange" href="index.php">Mon compte</a><?php else: ?><a class="btn btn-outline" href="login.php">Connexion</a><a class="btn btn-orange" href="register.php">Devenir vendeur</a><?php endif; ?></div>
        </div>
    </header>
    <main>
        <section class="hero">
            <div class="container">
                <div><span class="badge"><b></b> Livraison active dans plus de 45 villes au Maroc</span>
                    <h1>Livraison de colis e-commerce <em>partout au Maroc</em></h1>
                    <p>Ramassage gratuit, livraison express et encaissement cash à la livraison pour les vendeurs e-commerce et les entreprises marocaines.</p>
                    <div class="actions"><a class="btn btn-orange" href="register.php">Devenir vendeur <i class="fa-solid fa-arrow-right"></i></a><a class="btn btn-outline" href="#tarifs">Voir les tarifs</a></div>
                    <div class="checks"><span><i class="fa-solid fa-circle-check"></i> Sans engagement</span><span><i class="fa-solid fa-circle-check"></i> Ramassage gratuit</span><span><i class="fa-solid fa-circle-check"></i> Versement 48h</span></div>
                </div>
                <div class="hero-art"><img src="images/courier.png" alt="Livreur EXLIV Delivery"></div>
            </div>
        </section>
        <section class="stats">
            <div class="container stat-grid">
                <div class="stat"><strong>+45</strong><span>Villes desservies</span></div>
                <div class="stat"><strong>24h</strong><span>Livraison express</span></div>
                <div class="stat"><strong>48h</strong><span>Versement COD</span></div>
                <div class="stat"><strong>3×</strong><span>Tentatives de livraison</span></div>
            </div>
        </section>
        <section id="services" class="services">
            <div class="container">
                <div class="section-title">
                    <div class="eyebrow"></div>
                    <h2>Nos services de livraison</h2>
                    <p>Des solutions logistiques pensées pour les vendeurs e-commerce au Maroc.</p>
                </div>
                <div class="cards">
                    <article class="card">
                        <div class="icon"><i class="fa-solid fa-box"></i></div>
                        <h3>Livraison e-commerce</h3>
                        <p>Vos commandes sont livrées rapidement avec suivi à chaque étape.</p>
                    </article>
                    <article class="card">
                        <div class="icon"><i class="fa-solid fa-bolt"></i></div>
                        <h3>Livraison express</h3>
                        <p>Une livraison en 24h dans les grandes villes du Maroc.</p>
                    </article>
                    <article class="card">
                        <div class="icon"><i class="fa-solid fa-wallet"></i></div>
                        <h3>Cash à la livraison</h3>
                        <p>Nous encaissons pour vous et versons vos fonds sous 48h.</p>
                    </article>
                    <article class="card">
                        <div class="icon"><i class="fa-solid fa-truck"></i></div>
                        <h3>Ramassage gratuit</h3>
                        <p>Nos livreurs récupèrent vos colis directement chez vous.</p>
                    </article>
                    <article class="card">
                        <div class="icon"><i class="fa-solid fa-warehouse"></i></div>
                        <h3>Stockage</h3>
                        <p>Une solution souple pour garder vos produits disponibles.</p>
                    </article>
                    <article class="card">
                        <div class="icon"><i class="fa-solid fa-rotate-left"></i></div>
                        <h3>Gestion des retours</h3>
                        <p>Nous prenons aussi en charge vos retours et réexpéditions.</p>
                    </article>
                </div>
            </div>
        </section>
        <section class="cities" id="calculateur"><div class="container"><div class="section-title"><div class="eyebrow"></div><h2>Calculateur de prix</h2><p>Estimez le coût de votre livraison selon votre profil, la destination, le poids et le type de livraison.</p></div><div class="calc-grid" style="margin-top:32px"><div class="panel"><label class="field-label">Type de client</label><div class="form-grid"><button class="control quote-client" type="button" data-extra="0">Particulier</button><button class="control quote-client" type="button" data-extra="5">Professionnel</button><button class="control quote-client" type="button" data-extra="10">Vendeur e-commerce</button></div><div class="form-grid"><div><label class="field-label" for="quote-city">Ville de destination</label><select class="control" id="quote-city"><?php foreach ($displayCities as $city): ?><option value="<?php echo htmlspecialchars($city); ?>"><?php echo htmlspecialchars($city); ?></option><?php endforeach; ?></select></div><div><label class="field-label" for="quote-weight">Poids du colis (kg)</label><input class="control" id="quote-weight" type="number" min="1" step=".5" value="1"></div></div><label class="field-label" for="quote-type">Type de livraison</label><select class="control" id="quote-type"><option value="0">Livraison standard</option><option value="10">Livraison express</option></select></div><div class="panel quote"><small>ESTIMATION DE VOTRE LIVRAISON</small><p id="quote-price" style="margin:18px 0 6px;font:700 48px Archivo;color:var(--orange)">25 DH</p><p id="quote-detail" style="color:#d4dcea">Livraison vers Casablanca — Particulier</p><a class="btn btn-orange" style="margin-top:22px" href="register.php">Créer un compte vendeur</a></div></div></div></section>
        <section>
            <div class="container tracking">
                <div>
                    <div class="eyebrow"></div>
                    <h2>Suivi de colis en temps réel</h2>
                    <p class="section-copy">Entrez votre numéro de suivi pour connaître immédiatement l'état de votre colis EXLIV Delivery.</p>
                    <form action="track.php" method="post" class="tracking-form"><input type="text" name="tracking_code" required placeholder="Ex : EXL10078459321" aria-label="Numéro de suivi"><button class="btn btn-orange" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Suivre mon colis</button></form>
                </div>
                <div class="tracking-card">
                    <p>Le statut détaillé de votre colis s'affichera ici : ramassage, transit, mise en livraison et livraison finale.</p>
                    <div class="tracking-list">
                        <div class="track-row"><i class="fa-solid fa-warehouse"></i>
                            <div><b>Colis ramassé</b><small>Entrepôt EXLIV Delivery</small></div>
                        </div>
                        <div class="track-row"><i class="fa-solid fa-truck"></i>
                            <div><b>En transit</b><small>Hub régional</small></div>
                        </div>
                        <div class="track-row"><i class="fa-solid fa-location-dot"></i>
                            <div><b>En cours de livraison</b><small>Agence de destination</small></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="cities"><div class="container"><div class="section-title"><div class="eyebrow"></div><h2>Comment ça marche</h2><p>Un processus simple et transparent, de la commande jusqu'au versement de vos fonds.</p></div><div class="process-grid" style="margin-top:32px"><article class="step"><strong>01</strong><h3>Vous créez la commande</h3><p>Ajoutez vos colis depuis votre espace vendeur en quelques secondes.</p></article><article class="step"><strong>02</strong><h3>Ramassage gratuit</h3><p>Nos livreurs récupèrent les colis chez vous, sans frais supplémentaires.</p></article><article class="step"><strong>03</strong><h3>Livraison en 24h</h3><p>Le colis est livré au client final avec encaissement du montant COD.</p></article><article class="step"><strong>04</strong><h3>Versement rapide</h3><p>Vos fonds sont transférés sous 48h après la livraison.</p></article></div></div></section>
        <section id="tarifs">
            <div class="container coverage-grid">
                <div>
                    <img class="coverage-map" src="images/morocco-coverage.svg" alt="Carte de couverture EXLIV Delivery au Maroc">
                </div>
                <div>
                    <div class="eyebrow"></div>
                    <h2>Couverture nationale</h2>
                    <p class="section-copy">Nous livrons dans les grandes villes du Maroc en 24h et assurons les liaisons interurbaines en 48-72h maximum.</p>
                    <div class="city-list"><?php foreach (array_slice($displayCities, 0, 12) as $city): ?><span class="city"><i class="fa-solid fa-location-dot"></i><?php echo htmlspecialchars($city); ?></span><?php endforeach; ?></div>
                    <div class="coverage-actions"><a class="btn btn-orange" href="register.php">Consulter les tarifs</a><a class="btn btn-outline" href="#contact">Demander un devis</a></div>
                </div>
            </div>
        </section>
        <section class="cta">
            <div class="container">
                <h2>Prêt à expédier avec EXLIV ?</h2>
                <p>Créez votre compte vendeur gratuitement et commencez à livrer dès aujourd'hui.</p><a class="btn btn-orange" href="register.php">Créer un compte vendeur <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </section>
        <section class="why"><div class="container split-grid"><div><div class="eyebrow"></div><h2>Pourquoi choisir EXLIV Delivery ?</h2><p style="color:#d4dcea;line-height:1.55">Nous combinons réseau logistique, technologie et support humain pour vous faire gagner du temps et de l'argent.</p></div><div class="benefits"><div class="benefit"><i class="fa-solid fa-circle-check"></i>Ramassage gratuit chez le vendeur</div><div class="benefit"><i class="fa-solid fa-circle-check"></i>Encaissement cash à la livraison</div><div class="benefit"><i class="fa-solid fa-circle-check"></i>Jusqu'à 3 tentatives de livraison</div><div class="benefit"><i class="fa-solid fa-circle-check"></i>Suivi en temps réel du colis</div><div class="benefit"><i class="fa-solid fa-circle-check"></i>Versement des fonds sous 48h</div><div class="benefit"><i class="fa-solid fa-circle-check"></i>Couverture nationale en 24-72h</div></div></div></section>
        <section><div class="container"><div class="section-title"><div class="eyebrow"></div><h2>Ils nous font confiance</h2><p>Retour d'expérience de vendeurs et entreprises qui utilisent EXLIV Delivery chaque jour.</p></div><div class="testimonials" style="margin-top:32px;grid-template-columns:repeat(3,1fr)"><article class="testimonial"><div class="quote-mark">“</div><p>EXLIV a changé notre activité. Le taux de livraison est excellent et le versement COD est très rapide.</p><footer>Yassine B. <span style="color:var(--muted);font-weight:400">— Vendeur mode en ligne</span></footer></article><article class="testimonial"><div class="quote-mark">“</div><p>Un service client réactif et des livreurs professionnels. Je recommande à tous les vendeurs marocains.</p><footer>Fatima E. <span style="color:var(--muted);font-weight:400">— Fondatrice e-commerce</span></footer></article><article class="testimonial"><div class="quote-mark">“</div><p>Nous avons réduit nos retours grâce au suivi et aux relances de livraison.</p><footer>Karim L. <span style="color:var(--muted);font-weight:400">— Responsable logistique</span></footer></article></div></div></section>
        <section class="cities" id="inscription"><div class="container"><div class="signup-box"><div class="icon" style="margin:auto"><i class="fa-solid fa-clock"></i></div><h2>Prêt à expédier vos premiers colis ?</h2><p>Ouvrez un compte vendeur gratuit en 2 minutes et bénéficiez du ramassage gratuit dès votre première expédition.</p><a class="btn btn-orange" href="register.php">Créer un compte vendeur</a></div></div></section>
    </main>
    <footer id="contact" class="footer">
        <div class="container footer-grid">
            <div><a class="brand" href="home.php"><img src="images/logo-mark-CFsHaYkQ.png" alt="<?php echo htmlspecialchars($appName); ?>"></a>
                <p>Société marocaine de livraison de colis e-commerce avec paiement cash à la livraison.</p>
            </div>
            <div>
                <h3>Navigation</h3>
                <ul>
                    <li><a href="home.php">Accueil</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#tarifs">Tarifs</a></li>
                    <li><a href="track.php">Suivi de colis</a></li>
                </ul>
            </div>
            <div>
                <h3>Espace vendeur</h3>
                <ul>
                    <li><a href="login.php">Connexion</a></li>
                    <li><a href="register.php">Créer un compte</a></li>
                </ul>
            </div>
            <div>
                <h3>Contact</h3>
                <ul>
                    <li><i class="fa-solid fa-phone"></i> <a href="tel:0788859293">0788859293</a></li>
                    <li><i class="fa-solid fa-envelope"></i> contact@exlivdelivery.ma</li>
                    <li><i class="fa-solid fa-location-dot"></i> Casablanca, Maroc</li>
                </ul>
            </div>
        </div>
        <div class="container copyright"><span>© <?php echo date('Y'); ?> <?php echo htmlspecialchars($appName); ?>. Tous droits réservés.</span><span>Rapidité, fiabilité, cash à la livraison</span></div>
    </footer>
<script>var clientExtra=0,clientLabel='Particulier';function updateQuote(){var weight=Math.max(1,Number(document.getElementById('quote-weight').value)||1),express=Number(document.getElementById('quote-type').value),total=25+clientExtra+express+Math.max(0,weight-5)*4;document.getElementById('quote-price').textContent=total+' DH';document.getElementById('quote-detail').textContent='Livraison vers '+document.getElementById('quote-city').value+' — '+clientLabel;}['quote-city','quote-weight','quote-type'].forEach(function(id){document.getElementById(id).addEventListener('input',updateQuote);});document.querySelectorAll('.quote-client').forEach(function(button){button.addEventListener('click',function(){clientExtra=Number(this.dataset.extra);clientLabel=this.textContent;document.querySelectorAll('.quote-client').forEach(function(item){item.style.borderColor='#cbd3e1';item.style.background='#fff';});this.style.borderColor='#e88a28';this.style.background='#fff3e6';updateQuote();});});</script>
</body>

</html>
