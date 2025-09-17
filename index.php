<?php
/**
 * Homepage for Appliances Management System
 */

require_once 'config/config.php';

// Get some sample data for the homepage
$featured_categories = fetchAll("SELECT * FROM categories ORDER BY category_name LIMIT 6");
$total_products = fetchOne("SELECT COUNT(*) as count FROM products WHERE status = 'active'")['count'] ?? 0;
$total_customers = fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'customer' AND status = 'active'")['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Your Trusted Appliance Partner</title>
    <meta name="description" content="Premium appliances with flexible payment options. Browse our wide selection of home appliances with installment plans available.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo">
                    <i class="fas fa-plug"></i>
                    <span><?php echo APP_NAME; ?></span>
                </a>
                
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="#home" class="active">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="#products">Products</a>
                    </li>
                    <li class="nav-item">
                        <a href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a href="#contact">Contact</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <?php $user = getCurrentUser(); ?>
                        <li class="nav-item dropdown">
                            <a href="#" class="dropdown-toggle">
                                <i class="fas fa-user-circle"></i>
                                <?php echo htmlspecialchars($user['first_name']); ?>
                            </a>
                            <div class="dropdown-menu">
                                <?php if ($user['role'] === 'customer'): ?>
                                    <a href="customer/dashboard.php">Dashboard</a>
                                <?php else: ?>
                                    <a href="admin/dashboard.php">Admin Panel</a>
                                <?php endif; ?>
                                <a href="profile.php">Profile</a>
                                <a href="logout.php">Logout</a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="login.php" class="btn btn-outline">
                                <i class="fas fa-sign-in-alt"></i>
                                Sign In
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="register.php" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i>
                                Sign Up
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <div class="menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Hero Section -->
        <section id="home" class="hero-section">
            <div class="hero-background">
                <div class="hero-overlay"></div>
                <div class="hero-animation">
                    <div class="floating-appliance">
                        <i class="fas fa-tv"></i>
                    </div>
                    <div class="floating-appliance">
                        <i class="fas fa-blender"></i>
                    </div>
                    <div class="floating-appliance">
                        <i class="fas fa-wind"></i>
                    </div>
                    <div class="floating-appliance">
                        <i class="fas fa-temperature-low"></i>
                    </div>
                </div>
            </div>
            
            <div class="container">
                <div class="hero-content">
                    <div class="hero-text">
                        <h1 class="hero-title">
                            Premium Appliances for Modern Living
                        </h1>
                        <p class="hero-subtitle">
                            Discover our extensive collection of high-quality home appliances with flexible payment options. 
                            From kitchen essentials to entertainment systems, we have everything you need.
                        </p>
                        
                        <div class="hero-features">
                            <div class="feature-item">
                                <i class="fas fa-credit-card"></i>
                                <span>Flexible Payment Plans</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Extended Warranty</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-truck"></i>
                                <span>Free Delivery</span>
                            </div>
                        </div>
                        
                        <div class="hero-actions">
                            <a href="#products" class="btn btn-primary btn-lg">
                                <i class="fas fa-shopping-bag"></i>
                                Shop Now
                            </a>
                            <a href="#services" class="btn btn-outline btn-lg">
                                <i class="fas fa-info-circle"></i>
                                Learn More
                            </a>
                        </div>
                    </div>
                    
                    <div class="hero-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo number_format($total_products); ?>+</div>
                            <div class="stat-label">Products Available</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo number_format($total_customers); ?>+</div>
                            <div class="stat-label">Happy Customers</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">5</div>
                            <div class="stat-label">Years Experience</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">Support Available</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section id="products" class="categories-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Our Product Categories</h2>
                    <p class="section-subtitle">
                        Explore our wide range of appliances designed to make your life easier and more comfortable
                    </p>
                </div>
                
                <div class="categories-grid">
                    <?php foreach ($featured_categories as $category): ?>
                        <div class="category-card fade-in">
                            <div class="category-icon">
                                <?php
                                $icons = [
                                    'Refrigerators' => 'fas fa-temperature-low',
                                    'Washing Machines' => 'fas fa-tshirt',
                                    'Air Conditioners' => 'fas fa-wind',
                                    'Kitchen Appliances' => 'fas fa-blender',
                                    'Television & Audio' => 'fas fa-tv',
                                    'Small Appliances' => 'fas fa-plug'
                                ];
                                $icon = $icons[$category['category_name']] ?? 'fas fa-plug';
                                ?>
                                <i class="<?php echo $icon; ?>"></i>
                            </div>
                            <div class="category-content">
                                <h3 class="category-title"><?php echo htmlspecialchars($category['category_name']); ?></h3>
                                <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                                <a href="products.php?category=<?php echo $category['category_id']; ?>" class="category-link">
                                    View Products <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="services-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Why Choose Us</h2>
                    <p class="section-subtitle">
                        We provide comprehensive appliance solutions with customer-first approach
                    </p>
                </div>
                
                <div class="services-grid">
                    <div class="service-card fade-in">
                        <div class="service-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h3>Flexible Payment Plans</h3>
                        <p>Choose from various payment options including installments to suit your budget. No hidden fees, transparent pricing.</p>
                    </div>
                    
                    <div class="service-card fade-in">
                        <div class="service-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h3>Professional Installation</h3>
                        <p>Our certified technicians ensure proper installation and setup of all appliances with comprehensive testing.</p>
                    </div>
                    
                    <div class="service-card fade-in">
                        <div class="service-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Extended Warranty</h3>
                        <p>Comprehensive warranty coverage with quick repair and replacement services for complete peace of mind.</p>
                    </div>
                    
                    <div class="service-card fade-in">
                        <div class="service-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>24/7 Customer Support</h3>
                        <p>Round-the-clock customer support to help you with any questions or issues you might have.</p>
                    </div>
                    
                    <div class="service-card fade-in">
                        <div class="service-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3>Free Delivery & Setup</h3>
                        <p>Complimentary delivery and professional setup service for all major appliance purchases.</p>
                    </div>
                    
                    <div class="service-card fade-in">
                        <div class="service-icon">
                            <i class="fas fa-medal"></i>
                        </div>
                        <h3>Quality Guarantee</h3>
                        <p>We only stock premium brands and products that meet our strict quality standards and customer expectations.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2>Ready to Upgrade Your Home?</h2>
                    <p>Join thousands of satisfied customers who trust us for their appliance needs</p>
                    <div class="cta-actions">
                        <?php if (isLoggedIn()): ?>
                            <?php $user = getCurrentUser(); ?>
                            <?php if ($user['role'] === 'customer'): ?>
                                <a href="customer/dashboard.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-tachometer-alt"></i>
                                    Go to Dashboard
                                </a>
                            <?php else: ?>
                                <a href="admin/dashboard.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-cogs"></i>
                                    Admin Panel
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus"></i>
                                Create Account
                            </a>
                            <a href="login.php" class="btn btn-outline btn-lg">
                                <i class="fas fa-sign-in-alt"></i>
                                Customer Login
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="contact-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Get In Touch</h2>
                    <p class="section-subtitle">
                        Have questions? We're here to help you find the perfect appliances for your home
                    </p>
                </div>
                
                <div class="contact-grid">
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Visit Our Store</h4>
                                <p>123 Main Street<br>City, State 12345</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Call Us</h4>
                                <p>+1 (555) 123-4567<br>Mon-Sat: 9AM-8PM</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h4>Email Us</h4>
                                <p>info@appliances.com<br>support@appliances.com</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-form-container">
                        <form class="contact-form">
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" placeholder="Your Name" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <input type="email" class="form-control" placeholder="Your Email" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Subject" required>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control" rows="5" placeholder="Your Message" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i>
                                Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-plug"></i>
                        <span><?php echo APP_NAME; ?></span>
                    </div>
                    <p>Your trusted partner for premium home appliances with flexible payment solutions.</p>
                </div>
                
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#products">Products</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Customer Portal</h4>
                    <ul>
                        <li><a href="login.php">Sign In</a></li>
                        <li><a href="register.php">Create Account</a></li>
                        <li><a href="customer/dashboard.php">Dashboard</a></li>
                        <li><a href="forgot-password.php">Reset Password</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Staff Portal</h4>
                    <ul>
                        <li><a href="login.php">Staff Login</a></li>
                        <li><a href="admin/dashboard.php">Admin Panel</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-copyright">
                    <p>&copy; 2024 <?php echo APP_NAME; ?>. All rights reserved.</p>
                </div>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        // Add homepage specific styles and animations
        document.head.insertAdjacentHTML('beforeend', `
            <style>
                /* Hero Section */
                .hero-section {
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    position: relative;
                    overflow: hidden;
                    background: linear-gradient(135deg, var(--navy-blue), var(--primary-green));
                }
                
                .hero-background {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    z-index: 1;
                }
                
                .hero-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(17, 63, 103, 0.8);
                }
                
                .hero-animation {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    z-index: 2;
                }
                
                .floating-appliance {
                    position: absolute;
                    font-size: 4rem;
                    color: rgba(198, 216, 112, 0.1);
                    animation: float 8s ease-in-out infinite;
                }
                
                .floating-appliance:nth-child(1) {
                    top: 20%;
                    left: 10%;
                    animation-delay: 0s;
                }
                
                .floating-appliance:nth-child(2) {
                    top: 60%;
                    right: 15%;
                    animation-delay: 2s;
                }
                
                .floating-appliance:nth-child(3) {
                    bottom: 30%;
                    left: 20%;
                    animation-delay: 4s;
                }
                
                .floating-appliance:nth-child(4) {
                    top: 40%;
                    right: 30%;
                    animation-delay: 6s;
                }
                
                .hero-content {
                    position: relative;
                    z-index: 3;
                    color: var(--white);
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 4rem;
                }
                
                .hero-text {
                    flex: 1;
                    max-width: 600px;
                }
                
                .hero-title {
                    font-size: 3.5rem;
                    font-weight: bold;
                    margin-bottom: 1.5rem;
                    line-height: 1.2;
                    color: var(--white);
                    animation: fadeInUp 1s ease-out;
                }
                
                .hero-subtitle {
                    font-size: 1.2rem;
                    margin-bottom: 2rem;
                    opacity: 0.9;
                    line-height: 1.6;
                    animation: fadeInUp 1s ease-out 0.2s both;
                }
                
                .hero-features {
                    display: flex;
                    gap: 2rem;
                    margin-bottom: 2rem;
                    animation: fadeInUp 1s ease-out 0.4s both;
                }
                
                .feature-item {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    font-size: 0.9rem;
                    opacity: 0.8;
                }
                
                .feature-item i {
                    color: var(--primary-green);
                    font-size: 1.2rem;
                }
                
                .hero-actions {
                    display: flex;
                    gap: 1rem;
                    animation: fadeInUp 1s ease-out 0.6s both;
                }
                
                .hero-stats {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 2rem;
                    animation: fadeInUp 1s ease-out 0.8s both;
                }
                
                .stat-item {
                    text-align: center;
                    padding: 1.5rem;
                    background: rgba(255, 255, 255, 0.1);
                    border-radius: 10px;
                    backdrop-filter: blur(10px);
                }
                
                .stat-number {
                    font-size: 2.5rem;
                    font-weight: bold;
                    color: var(--primary-green);
                    margin-bottom: 0.5rem;
                }
                
                .stat-label {
                    font-size: 0.9rem;
                    opacity: 0.8;
                }
                
                /* Categories Section */
                .categories-section {
                    padding: 5rem 0;
                    background: var(--light-gray);
                }
                
                .section-header {
                    text-align: center;
                    margin-bottom: 4rem;
                }
                
                .section-title {
                    font-size: 2.5rem;
                    margin-bottom: 1rem;
                    color: var(--navy-blue);
                }
                
                .section-subtitle {
                    font-size: 1.1rem;
                    color: var(--medium-gray);
                    max-width: 600px;
                    margin: 0 auto;
                }
                
                .categories-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 2rem;
                }
                
                .category-card {
                    background: var(--white);
                    border-radius: 15px;
                    padding: 2rem;
                    text-align: center;
                    box-shadow: var(--shadow-sm);
                    transition: all var(--transition-normal);
                    position: relative;
                    overflow: hidden;
                }
                
                .category-card:before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 4px;
                    background: var(--gradient-primary);
                }
                
                .category-card:hover {
                    transform: translateY(-10px);
                    box-shadow: var(--shadow-lg);
                }
                
                .category-icon {
                    font-size: 4rem;
                    color: var(--primary-green);
                    margin-bottom: 1.5rem;
                }
                
                .category-title {
                    font-size: 1.5rem;
                    margin-bottom: 1rem;
                    color: var(--navy-blue);
                }
                
                .category-description {
                    color: var(--medium-gray);
                    margin-bottom: 1.5rem;
                    line-height: 1.6;
                }
                
                .category-link {
                    color: var(--navy-blue);
                    font-weight: 600;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                    transition: color var(--transition-fast);
                }
                
                .category-link:hover {
                    color: var(--primary-green);
                }
                
                /* Services Section */
                .services-section {
                    padding: 5rem 0;
                    background: var(--white);
                }
                
                .services-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 2rem;
                }
                
                .service-card {
                    text-align: center;
                    padding: 2rem;
                    border-radius: 15px;
                    background: var(--light-gray);
                    transition: all var(--transition-normal);
                }
                
                .service-card:hover {
                    transform: translateY(-5px);
                    box-shadow: var(--shadow-md);
                    background: var(--white);
                }
                
                .service-icon {
                    font-size: 3rem;
                    color: var(--primary-green);
                    margin-bottom: 1.5rem;
                }
                
                .service-card h3 {
                    font-size: 1.3rem;
                    margin-bottom: 1rem;
                    color: var(--navy-blue);
                }
                
                .service-card p {
                    color: var(--medium-gray);
                    line-height: 1.6;
                }
                
                /* CTA Section */
                .cta-section {
                    padding: 5rem 0;
                    background: var(--gradient-navy);
                    color: var(--white);
                    text-align: center;
                }
                
                .cta-content h2 {
                    font-size: 2.5rem;
                    margin-bottom: 1rem;
                    color: var(--white);
                }
                
                .cta-content p {
                    font-size: 1.2rem;
                    margin-bottom: 2rem;
                    opacity: 0.9;
                }
                
                .cta-actions {
                    display: flex;
                    gap: 1rem;
                    justify-content: center;
                    flex-wrap: wrap;
                }
                
                /* Contact Section */
                .contact-section {
                    padding: 5rem 0;
                    background: var(--light-gray);
                }
                
                .contact-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 4rem;
                    align-items: start;
                }
                
                .contact-info {
                    display: flex;
                    flex-direction: column;
                    gap: 2rem;
                }
                
                .contact-item {
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                    padding: 1.5rem;
                    background: var(--white);
                    border-radius: 10px;
                    box-shadow: var(--shadow-sm);
                }
                
                .contact-icon {
                    font-size: 2rem;
                    color: var(--primary-green);
                    min-width: 60px;
                    text-align: center;
                }
                
                .contact-details h4 {
                    margin-bottom: 0.5rem;
                    color: var(--navy-blue);
                }
                
                .contact-details p {
                    color: var(--medium-gray);
                    margin: 0;
                }
                
                .contact-form-container {
                    background: var(--white);
                    padding: 2rem;
                    border-radius: 15px;
                    box-shadow: var(--shadow-sm);
                }
                
                .contact-form .form-group {
                    margin-bottom: 1.5rem;
                }
                
                /* Footer */
                .footer {
                    background: var(--navy-blue);
                    color: var(--white);
                    padding: 3rem 0 1rem;
                }
                
                .footer-content {
                    display: grid;
                    grid-template-columns: 2fr 1fr 1fr 1fr;
                    gap: 2rem;
                    margin-bottom: 2rem;
                }
                
                .footer-section {
                    max-width: 300px;
                }
                
                .footer-logo {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    font-size: 1.5rem;
                    font-weight: bold;
                    margin-bottom: 1rem;
                    color: var(--primary-green);
                }
                
                .footer-section p {
                    opacity: 0.8;
                    line-height: 1.6;
                }
                
                .footer-links h4 {
                    margin-bottom: 1rem;
                    color: var(--primary-green);
                    font-size: 1.1rem;
                }
                
                .footer-links ul {
                    list-style: none;
                    padding: 0;
                }
                
                .footer-links ul li {
                    margin-bottom: 0.5rem;
                }
                
                .footer-links ul li a {
                    color: var(--white);
                    text-decoration: none;
                    opacity: 0.8;
                    transition: opacity var(--transition-fast);
                }
                
                .footer-links ul li a:hover {
                    opacity: 1;
                    color: var(--primary-green);
                }
                
                .footer-bottom {
                    border-top: 1px solid rgba(255, 255, 255, 0.1);
                    padding-top: 1rem;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .footer-copyright p {
                    margin: 0;
                    opacity: 0.6;
                }
                
                .footer-social {
                    display: flex;
                    gap: 1rem;
                }
                
                .footer-social a {
                    color: var(--white);
                    font-size: 1.2rem;
                    opacity: 0.6;
                    transition: opacity var(--transition-fast);
                }
                
                .footer-social a:hover {
                    opacity: 1;
                    color: var(--primary-green);
                }
                
                /* Dropdown Menu */
                .dropdown {
                    position: relative;
                }
                
                .dropdown-menu {
                    position: absolute;
                    top: 100%;
                    right: 0;
                    background: var(--white);
                    border-radius: 5px;
                    box-shadow: var(--shadow-lg);
                    min-width: 180px;
                    opacity: 0;
                    visibility: hidden;
                    transform: translateY(-10px);
                    transition: all var(--transition-fast);
                    z-index: 1000;
                }
                
                .dropdown:hover .dropdown-menu {
                    opacity: 1;
                    visibility: visible;
                    transform: translateY(0);
                }
                
                .dropdown-menu a {
                    display: block;
                    padding: 0.75rem 1rem;
                    color: var(--navy-blue);
                    text-decoration: none;
                    border-bottom: 1px solid #e9ecef;
                    transition: background-color var(--transition-fast);
                }
                
                .dropdown-menu a:last-child {
                    border-bottom: none;
                }
                
                .dropdown-menu a:hover {
                    background-color: var(--light-gray);
                    color: var(--primary-green);
                }
                
                /* Responsive Design */
                @media (max-width: 768px) {
                    .hero-content {
                        flex-direction: column;
                        text-align: center;
                        gap: 2rem;
                    }
                    
                    .hero-title {
                        font-size: 2.5rem;
                    }
                    
                    .hero-features {
                        flex-direction: column;
                        gap: 1rem;
                        align-items: center;
                    }
                    
                    .hero-actions {
                        flex-direction: column;
                        align-items: center;
                    }
                    
                    .hero-stats {
                        grid-template-columns: repeat(2, 1fr);
                        gap: 1rem;
                    }
                    
                    .categories-grid,
                    .services-grid {
                        grid-template-columns: 1fr;
                    }
                    
                    .contact-grid {
                        grid-template-columns: 1fr;
                        gap: 2rem;
                    }
                    
                    .footer-content {
                        grid-template-columns: 1fr;
                        text-align: center;
                    }
                    
                    .footer-bottom {
                        flex-direction: column;
                        gap: 1rem;
                        text-align: center;
                    }
                    
                    .cta-actions {
                        flex-direction: column;
                        align-items: center;
                    }
                    
                    .floating-appliance {
                        display: none;
                    }
                }
            </style>
        `);
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Contact form submission
        document.querySelector('.contact-form').addEventListener('submit', function(e) {
            e.preventDefault();
            AppliancesApp.utils.showToast('Thank you for your message! We will get back to you soon.', 'success');
            this.reset();
        });
    </script>
</body>
</html>