<?php
/**
 * EXLIV Delivery - Landing Page Publique
 * Page d'accueil publique avec tarifs et liens vers login/inscription/tracking
 */

session_start();
include("config.php");

// Récupérer les tarifs depuis la base de données
$shippingFees = [];
$cities = [];
try {
    $back = $bdd->query("SELECT * FROM shippingfees WHERE trash='1' ORDER BY city");
    $shippingFees = $back->fetchAll();
    
    $back = $bdd->query("SELECT city FROM cities WHERE trash='1' ORDER BY city");
    $cities = $back->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Ignorer si les tables n'existent pas encore
}

// Récupérer les paramètres de l'application
$appName = $settings['appname'] ?? 'EXLIV Delivery';
$appLogo = $settings['logo'] ?? '';
$currency = $settings['currency'] ?? 'MAD';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $appName; ?> - Livraison Express au Maroc</title>
    <meta name="description" content="<?php echo $appName; ?> - Service de livraison rapide et fiable au Maroc. Suivez vos colis en temps réel.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        /* Header */
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo img {
            height: 40px;
        }
        .nav {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        .nav a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }
        .nav a:hover {
            color: #667eea;
        }
        .btn {
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
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
        .btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 2px solid #e0e0e0;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 150px 20px 100px;
            text-align: center;
        }
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .hero p {
            font-size: 20px;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        .hero-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .hero-buttons .btn {
            padding: 15px 35px;
            font-size: 16px;
        }
        .hero-buttons .btn-primary {
            background: white;
            color: #667eea;
        }
        .hero-buttons .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        /* Tracking Section */
        .tracking-section {
            background: #f8f9fa;
            padding: 60px 20px;
            margin-top: -50px;
        }
        .tracking-container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .tracking-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
        }
        .tracking-form {
            display: flex;
            gap: 10px;
        }
        .tracking-form input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
        }
        .tracking-form input:focus {
            outline: none;
            border-color: #667eea;
        }
        .tracking-form button {
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .tracking-form button:hover {
            transform: translateY(-2px);
        }
        
        /* Services Section */
        .services {
            padding: 80px 20px;
            background: white;
        }
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        .section-title h2 {
            font-size: 36px;
            color: #333;
            margin-bottom: 15px;
        }
        .section-title p {
            color: #666;
            font-size: 18px;
        }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .service-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.3s;
        }
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .service-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
            color: white;
        }
        .service-card h3 {
            font-size: 22px;
            color: #333;
            margin-bottom: 15px;
        }
        .service-card p {
            color: #666;
            line-height: 1.6;
        }
        
        /* Pricing Section */
        .pricing {
            padding: 80px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .pricing-card {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .pricing-card.featured {
            border: 3px solid #667eea;
        }
        .pricing-card.featured::before {
            content: 'Populaire';
            position: absolute;
            top: 20px;
            right: -30px;
            background: #667eea;
            color: white;
            padding: 5px 30px;
            font-size: 12px;
            font-weight: 600;
            transform: rotate(45deg);
        }
        .pricing-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .pricing-card h3 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }
        .pricing-price {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 20px;
        }
        .pricing-price span {
            font-size: 16px;
            color: #666;
            font-weight: normal;
        }
        .pricing-features {
            list-style: none;
            margin-bottom: 30px;
            text-align: left;
        }
        .pricing-features li {
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
            color: #666;
        }
        .pricing-features li:last-child {
            border-bottom: none;
        }
        .pricing-features li i {
            color: #4CAF50;
            margin-right: 10px;
        }
        
        /* Cities Section */
        .cities {
            padding: 80px 20px;
            background: white;
        }
        .cities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .city-item {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s;
        }
        .city-item:hover {
            background: #667eea;
            color: white;
        }
        
        /* CTA Section */
        .cta {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
        }
        .cta h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }
        .cta p {
            font-size: 18px;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        /* Footer */
        .footer {
            background: #1a1a2e;
            color: white;
            padding: 60px 20px 30px;
        }
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }
        .footer-section h3 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #667eea;
        }
        .footer-section p {
            color: #aaa;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .footer-section ul {
            list-style: none;
        }
        .footer-section ul li {
            margin-bottom: 10px;
        }
        .footer-section ul li a {
            color: #aaa;
            text-decoration: none;
            transition: color 0.3s;
        }
        .footer-section ul li a:hover {
            color: #667eea;
        }
        .footer-section .social-links {
            display: flex;
            gap: 15px;
        }
        .footer-section .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        .footer-section .social-links a:hover {
            background: #667eea;
        }
        .footer-bottom {
            max-width: 1200px;
            margin: 40px auto 0;
            padding-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            color: #aaa;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 15px;
            }
            .nav {
                flex-direction: column;
                gap: 15px;
            }
            .hero h1 {
                font-size: 32px;
            }
            .hero p {
                font-size: 16px;
            }
            .tracking-form {
                flex-direction: column;
            }
            .services-grid,
            .pricing-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="home.php" class="logo">
                <?php if ($appLogo): ?>
                    <img src="<?php echo htmlspecialchars($appLogo); ?>" alt="<?php echo $appName; ?>">
                <?php else: ?>
                    <i class="fa fa-truck"></i>
                <?php endif; ?>
                <?php echo $appName; ?>
            </a>
            <nav class="nav">
                <a href="#services">Services</a>
                <a href="#pricing">Tarifs</a>
                <a href="#cities">Villes</a>
                <a href="track.php">Suivi</a>
                <?php if (isset($_SESSION['id'])): ?>
                    <a href="index.php" class="btn btn-primary">Mon Compte</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary">Connexion</a>
                    <a href="register.php" class="btn btn-primary">Inscription</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    
    <!-- Hero Section -->
    <section class="hero">
        <h1>Livraison Express au Maroc</h1>
        <p>Envoyez vos colis partout au Maroc en toute sécurité et rapidité</p>
        <div class="hero-buttons">
            <a href="register.php" class="btn btn-primary">Commencer</a>
            <a href="track.php" class="btn btn-secondary">Suivre un colis</a>
        </div>
    </section>
    
    <!-- Tracking Section -->
    <section class="tracking-section">
        <div class="tracking-container">
            <h2>📦 Suivre votre colis</h2>
            <form action="track.php" method="POST" class="tracking-form">
                <input type="text" name="tracking_code" placeholder="Entrez votre numéro de suivi..." required>
                <button type="submit">🔍 Rechercher</button>
            </form>
        </div>
    </section>
    
    <!-- Services Section -->
    <section class="services" id="services">
        <div class="section-title">
            <h2>Nos Services</h2>
            <p>Des solutions de livraison adaptées à vos besoins</p>
        </div>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fa fa-bolt"></i>
                </div>
                <h3>Livraison Rapide</h3>
                <p>Livraison express en 12-24h pour vos commandes urgentes</p>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fa fa-box"></i>
                </div>
                <h3>Colis Particulier</h3>
                <p>Service dédié pour vos envois personnels en 24-48h</p>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fa fa-truck"></i>
                </div>
                <h3>Livraison Standard</h3>
                <p>Livraison économique en 48-72h pour vos commandes</p>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fa fa-map-marker-alt"></i>
                </div>
                <h3>Couverture Nationale</h3>
                <p>Livraison dans toutes les villes du Maroc</p>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fa fa-mobile-alt"></i>
                </div>
                <h3>Suivi en Temps Réel</h3>
                <p>Trackez votre colis à chaque étape de sa livraison</p>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fa fa-shield-alt"></i>
                </div>
                <h3>Assurance incluse</h3>
                <p>Vos colis sont assurés pendant le transport</p>
            </div>
        </div>
    </section>
    
    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <div class="section-title">
            <h2>Nos Tarifs</h2>
            <p>Des prix compétitifs pour tous vos envois</p>
        </div>
        <div class="pricing-grid">
            <div class="pricing-card">
                <div class="pricing-icon">📦</div>
                <h3>Colis Normal</h3>
                <div class="pricing-price">25 <?php echo $currency; ?><span>/colis</span></div>
                <ul class="pricing-features">
                    <li><i class="fa fa-check"></i> Livraison en 48-72h</li>
                    <li><i class="fa fa-check"></i> Suivi en temps réel</li>
                    <li><i class="fa fa-check"></i> Assurance incluse</li>
                    <li><i class="fa fa-check"></i> Notification SMS</li>
                </ul>
                <a href="register.php" class="btn btn-secondary">Choisir</a>
            </div>
            <div class="pricing-card featured">
                <div class="pricing-icon">⚡</div>
                <h3>Colis Rapide</h3>
                <div class="pricing-price">40 <?php echo $currency; ?><span>/colis</span></div>
                <ul class="pricing-features">
                    <li><i class="fa fa-check"></i> Livraison en 12-24h</li>
                    <li><i class="fa fa-check"></i> Priorité absolue</li>
                    <li><i class="fa fa-check"></i> Suivi en temps réel</li>
                    <li><i class="fa fa-check"></i> Assurance incluse</li>
                    <li><i class="fa fa-check"></i> Notification SMS</li>
                </ul>
                <a href="register.php" class="btn btn-primary">Choisir</a>
            </div>
            <div class="pricing-card">
                <div class="pricing-icon">👤</div>
                <h3>Colis Particulier</h3>
                <div class="pricing-price">35 <?php echo $currency; ?><span>/colis</span></div>
                <ul class="pricing-features">
                    <li><i class="fa fa-check"></i> Livraison en 24-48h</li>
                    <li><i class="fa fa-check"></i> Service personnalisé</li>
                    <li><i class="fa fa-check"></i> Suivi en temps réel</li>
                    <li><i class="fa fa-check"></i> Assurance incluse</li>
                </ul>
                <a href="register.php" class="btn btn-secondary">Choisir</a>
            </div>
        </div>
    </section>
    
    <!-- Cities Section -->
    <section class="cities" id="cities">
        <div class="section-title">
            <h2>Villes Desservies</h2>
            <p>Nous livrons dans toutes les villes du Maroc</p>
        </div>
        <div class="cities-grid">
            <?php if (!empty($cities)): ?>
                <?php foreach (array_slice($cities, 0, 20) as $city): ?>
                    <div class="city-item">
                        <i class="fa fa-map-marker-alt"></i> <?php echo htmlspecialchars($city); ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="city-item"><i class="fa fa-map-marker-alt"></i> Casablanca</div>
                <div class="city-item"><i class="fa fa-map-marker-alt"></i> Rabat</div>
                <div class="city-item"><i class="fa fa-map-marker-alt"></i> Marrakech</div>
                <div class="city-item"><i class="fa fa-map-marker-alt"></i> Fès</div>
                <div class="city-item"><i class="fa fa-map-marker-alt"></i> Tanger</div>
                <div class="city-item"><i class="fa fa-map-marker-alt"></i> Agadir</div>
                <div class="city-item"><i class="fa fa-map-marker-alt"></i> Meknès</div>
                <div class="city-item"><i class="fa fa-map-marker-alt"></i> Oujda</div>
                <div class="city-item"><i class="fa fa-map-marker-alt"></i> Kenitra</div>
                <div class="city-item"><i class="fa fa-map-marker-alt"></i> Tétouan</div>
                <div class="city-item"><i class="fa fa-map-marker-alt"></i> Safi</div>
                <div class="city-item"><i class="fa fa-map-marker-alt"></i> El Jadida</div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta">
        <h2>Prêt à envoyer votre colis?</h2>
        <p>Créez votre compte gratuitement et commencez à expédier dès maintenant</p>
        <a href="register.php" class="btn btn-primary" style="background: white; color: #667eea;">Créer un compte</a>
    </section>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3><?php echo $appName; ?></h3>
                <p>Votre partenaire de confiance pour la livraison de colis au Maroc. Rapide, sécurisé et abordable.</p>
                <div class="social-links">
                    <a href="#"><i class="fa fa-facebook-f"></i></a>
                    <a href="#"><i class="fa fa-twitter"></i></a>
                    <a href="#"><i class="fa fa-instagram"></i></a>
                    <a href="#"><i class="fa fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Liens Rapides</h3>
                <ul>
                    <li><a href="home.php">Accueil</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#pricing">Tarifs</a></li>
                    <li><a href="track.php">Suivi de colis</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Mon Compte</h3>
                <ul>
                    <li><a href="login.php">Connexion</a></li>
                    <li><a href="register.php">Inscription</a></li>
                    <li><a href="#">Mot de passe oublié</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p><i class="fa fa-phone"></i> +212 600 000 000</p>
                <p><i class="fa fa-envelope"></i> contact@exlivdelivery.com</p>
                <p><i class="fa fa-map-marker-alt"></i> Casablanca, Maroc</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo $appName; ?>. Tous droits réservés.</p>
        </div>
    </footer>
    
    <script>
        // Smooth scroll pour les liens d'ancrage
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>
