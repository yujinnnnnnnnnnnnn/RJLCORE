<?php
/**
 * About Us Page for Appliances Management System
 */

require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - <?php echo APP_NAME; ?></title>
    <meta name="description" content="Learn about our company, mission, and commitment to providing premium appliances with excellent customer service.">
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
                        <a href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php#products">Products</a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a href="about.php" class="active">About</a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php#contact">Contact</a>
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
        <!-- Page Header -->
        <section class="page-header">
            <div class="container">
                <h1 class="page-title">About Us</h1>
                <p class="page-subtitle">Your trusted partner in premium home appliances</p>
            </div>
        </section>

        <!-- Company Story Section -->
        <section class="company-story">
            <div class="container">
                <div class="story-grid">
                    <div class="story-content">
                        <h2>Our Story</h2>
                        <p>
                            Founded in 2019, <?php echo APP_NAME; ?> began with a simple mission: to make premium home appliances 
                            accessible to everyone through flexible payment solutions and exceptional customer service.
                        </p>
                        <p>
                            What started as a small family business has grown into a trusted name in the appliance industry, 
                            serving thousands of satisfied customers across the region. We believe that everyone deserves 
                            quality appliances that make their daily life easier and more comfortable.
                        </p>
                        <p>
                            Our commitment to innovation, quality, and customer satisfaction has made us a leader in 
                            appliance retail and financing solutions.
                        </p>
                    </div>
                    <div class="story-image">
                        <div class="image-placeholder">
                            <i class="fas fa-store"></i>
                            <span>Our Store Front</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mission & Vision Section -->
        <section class="mission-vision">
            <div class="container">
                <div class="mission-vision-grid">
                    <div class="mission-card">
                        <div class="card-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3>Our Mission</h3>
                        <p>
                            To provide high-quality home appliances with flexible payment options, 
                            making modern living accessible to all families while delivering exceptional 
                            customer service and support.
                        </p>
                    </div>
                    
                    <div class="vision-card">
                        <div class="card-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3>Our Vision</h3>
                        <p>
                            To be the leading appliance retailer known for innovation, customer satisfaction, 
                            and community impact, helping families create comfortable and efficient homes 
                            through quality products and services.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Values Section -->
        <section class="values-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Our Core Values</h2>
                    <p class="section-subtitle">
                        These principles guide everything we do and shape our relationship with customers
                    </p>
                </div>
                
                <div class="values-grid">
                    <div class="value-item fade-in">
                        <div class="value-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h4>Integrity</h4>
                        <p>We conduct business with honesty, transparency, and ethical practices in all our interactions.</p>
                    </div>
                    
                    <div class="value-item fade-in">
                        <div class="value-icon">
                            <i class="fas fa-medal"></i>
                        </div>
                        <h4>Quality</h4>
                        <p>We only offer products that meet our high standards and provide long-lasting value to our customers.</p>
                    </div>
                    
                    <div class="value-item fade-in">
                        <div class="value-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Customer Focus</h4>
                        <p>Our customers are at the heart of everything we do, and we strive to exceed their expectations.</p>
                    </div>
                    
                    <div class="value-item fade-in">
                        <div class="value-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h4>Innovation</h4>
                        <p>We continuously improve our services and embrace new technologies to better serve our customers.</p>
                    </div>
                    
                    <div class="value-item fade-in">
                        <div class="value-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4>Community</h4>
                        <p>We are committed to giving back to our community and supporting local initiatives.</p>
                    </div>
                    
                    <div class="value-item fade-in">
                        <div class="value-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <h4>Accessibility</h4>
                        <p>We believe quality appliances should be accessible to all through flexible payment solutions.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="team-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Meet Our Team</h2>
                    <p class="section-subtitle">
                        Dedicated professionals committed to your satisfaction
                    </p>
                </div>
                
                <div class="team-grid">
                    <div class="team-member fade-in">
                        <div class="member-photo">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="member-info">
                            <h4>John Anderson</h4>
                            <p class="member-role">Founder & CEO</p>
                            <p class="member-description">
                                With 15+ years in the appliance industry, John leads our company with 
                                passion for customer service and innovation.
                            </p>
                            <div class="member-social">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="team-member fade-in">
                        <div class="member-photo">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="member-info">
                            <h4>Sarah Mitchell</h4>
                            <p class="member-role">Operations Manager</p>
                            <p class="member-description">
                                Sarah ensures smooth operations and exceptional customer experience 
                                across all our services and processes.
                            </p>
                            <div class="member-social">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="team-member fade-in">
                        <div class="member-photo">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <div class="member-info">
                            <h4>Michael Rodriguez</h4>
                            <p class="member-role">Technical Director</p>
                            <p class="member-description">
                                Michael oversees our technical services, installation teams, and 
                                ensures all products meet our quality standards.
                            </p>
                            <div class="member-social">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="team-member fade-in">
                        <div class="member-photo">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <div class="member-info">
                            <h4>Emma Thompson</h4>
                            <p class="member-role">Customer Relations Manager</p>
                            <p class="member-description">
                                Emma leads our customer support team and ensures every customer 
                                receives personalized attention and care.
                            </p>
                            <div class="member-social">
                                <a href="#"><i class="fab fa-linkedin"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section class="statistics-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number">5,000+</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-number">10,000+</div>
                        <div class="stat-label">Products Delivered</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-number">5+</div>
                        <div class="stat-label">Years of Experience</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-number">4.9/5</div>
                        <div class="stat-label">Customer Rating</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Information -->
        <section class="contact-info-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Visit Our Store</h2>
                    <p class="section-subtitle">
                        Come see our showroom and speak with our knowledgeable staff
                    </p>
                </div>
                
                <div class="contact-info-grid">
                    <div class="contact-info-card">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h4>Our Location</h4>
                        <p>
                            123 Main Street<br>
                            Downtown District<br>
                            City, State 12345
                        </p>
                    </div>
                    
                    <div class="contact-info-card">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4>Store Hours</h4>
                        <p>
                            Monday - Friday: 9:00 AM - 8:00 PM<br>
                            Saturday: 9:00 AM - 6:00 PM<br>
                            Sunday: 11:00 AM - 5:00 PM
                        </p>
                    </div>
                    
                    <div class="contact-info-card">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h4>Contact Information</h4>
                        <p>
                            Phone: +1 (555) 123-4567<br>
                            Email: info@appliances.com<br>
                            Support: support@appliances.com
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2>Ready to Find Your Perfect Appliance?</h2>
                    <p>Browse our extensive collection or visit our showroom to see products in person</p>
                    <div class="cta-actions">
                        <a href="index.php#products" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag"></i>
                            Browse Products
                        </a>
                        <a href="index.php#contact" class="btn btn-outline btn-lg">
                            <i class="fas fa-map-marker-alt"></i>
                            Visit Our Store
                        </a>
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
                        <li><a href="index.php">Home</a></li>
                        <li><a href="index.php#products">Products</a></li>
                        <li><a href="index.php#services">Services</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="index.php#contact">Contact</a></li>
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
        // Add about page specific styles
        document.head.insertAdjacentHTML('beforeend', `
            <style>
                /* Company Story Section */
                .company-story {
                    padding: 5rem 0;
                    background: var(--white);
                }
                
                .story-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 4rem;
                    align-items: center;
                }
                
                .story-content h2 {
                    font-size: 2.5rem;
                    margin-bottom: 2rem;
                    color: var(--navy-blue);
                }
                
                .story-content p {
                    font-size: 1.1rem;
                    line-height: 1.8;
                    margin-bottom: 1.5rem;
                    color: var(--dark-gray);
                }
                
                .story-image {
                    text-align: center;
                }
                
                .image-placeholder {
                    background: var(--gradient-primary);
                    border-radius: 15px;
                    padding: 4rem 2rem;
                    color: var(--navy-blue);
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 1rem;
                }
                
                .image-placeholder i {
                    font-size: 5rem;
                    opacity: 0.8;
                }
                
                .image-placeholder span {
                    font-size: 1.2rem;
                    font-weight: 600;
                }
                
                /* Mission & Vision Section */
                .mission-vision {
                    padding: 5rem 0;
                    background: var(--light-gray);
                }
                
                .mission-vision-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 3rem;
                }
                
                .mission-card,
                .vision-card {
                    background: var(--white);
                    padding: 3rem;
                    border-radius: 15px;
                    text-align: center;
                    box-shadow: var(--shadow-sm);
                    transition: all var(--transition-normal);
                }
                
                .mission-card:hover,
                .vision-card:hover {
                    transform: translateY(-5px);
                    box-shadow: var(--shadow-lg);
                }
                
                .card-icon {
                    font-size: 4rem;
                    color: var(--primary-green);
                    margin-bottom: 2rem;
                }
                
                .mission-card h3,
                .vision-card h3 {
                    font-size: 2rem;
                    margin-bottom: 1.5rem;
                    color: var(--navy-blue);
                }
                
                .mission-card p,
                .vision-card p {
                    font-size: 1.1rem;
                    line-height: 1.7;
                    color: var(--medium-gray);
                }
                
                /* Values Section */
                .values-section {
                    padding: 5rem 0;
                    background: var(--white);
                }
                
                .values-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 2rem;
                }
                
                .value-item {
                    text-align: center;
                    padding: 2rem;
                    border-radius: 10px;
                    transition: all var(--transition-normal);
                }
                
                .value-item:hover {
                    background: var(--light-gray);
                    transform: translateY(-5px);
                }
                
                .value-icon {
                    font-size: 3rem;
                    color: var(--primary-green);
                    margin-bottom: 1.5rem;
                }
                
                .value-item h4 {
                    font-size: 1.3rem;
                    margin-bottom: 1rem;
                    color: var(--navy-blue);
                }
                
                .value-item p {
                    color: var(--medium-gray);
                    line-height: 1.6;
                }
                
                /* Team Section */
                .team-section {
                    padding: 5rem 0;
                    background: var(--light-gray);
                }
                
                .team-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                    gap: 2rem;
                }
                
                .team-member {
                    background: var(--white);
                    border-radius: 15px;
                    padding: 2rem;
                    text-align: center;
                    box-shadow: var(--shadow-sm);
                    transition: all var(--transition-normal);
                }
                
                .team-member:hover {
                    transform: translateY(-5px);
                    box-shadow: var(--shadow-lg);
                }
                
                .member-photo {
                    width: 120px;
                    height: 120px;
                    border-radius: 50%;
                    background: var(--gradient-primary);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 1.5rem;
                    font-size: 3rem;
                    color: var(--navy-blue);
                }
                
                .member-info h4 {
                    font-size: 1.3rem;
                    margin-bottom: 0.5rem;
                    color: var(--navy-blue);
                }
                
                .member-role {
                    color: var(--primary-green);
                    font-weight: 600;
                    margin-bottom: 1rem;
                }
                
                .member-description {
                    color: var(--medium-gray);
                    line-height: 1.6;
                    margin-bottom: 1.5rem;
                }
                
                .member-social {
                    display: flex;
                    justify-content: center;
                    gap: 1rem;
                }
                
                .member-social a {
                    color: var(--medium-gray);
                    font-size: 1.2rem;
                    transition: color var(--transition-fast);
                }
                
                .member-social a:hover {
                    color: var(--primary-green);
                }
                
                /* Statistics Section */
                .statistics-section {
                    padding: 5rem 0;
                    background: var(--gradient-navy);
                    color: var(--white);
                }
                
                .stats-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 2rem;
                }
                
                .stat-card {
                    text-align: center;
                    padding: 2rem;
                    background: rgba(255, 255, 255, 0.1);
                    border-radius: 15px;
                    backdrop-filter: blur(10px);
                    transition: all var(--transition-normal);
                }
                
                .stat-card:hover {
                    transform: translateY(-5px);
                    background: rgba(255, 255, 255, 0.15);
                }
                
                .stat-icon {
                    font-size: 3rem;
                    color: var(--primary-green);
                    margin-bottom: 1rem;
                }
                
                .stat-number {
                    font-size: 2.5rem;
                    font-weight: bold;
                    margin-bottom: 0.5rem;
                    color: var(--white);
                }
                
                .stat-label {
                    font-size: 1rem;
                    opacity: 0.8;
                }
                
                /* Contact Info Section */
                .contact-info-section {
                    padding: 5rem 0;
                    background: var(--white);
                }
                
                .contact-info-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 2rem;
                }
                
                .contact-info-card {
                    text-align: center;
                    padding: 2rem;
                    background: var(--light-gray);
                    border-radius: 15px;
                    transition: all var(--transition-normal);
                }
                
                .contact-info-card:hover {
                    transform: translateY(-5px);
                    box-shadow: var(--shadow-md);
                    background: var(--white);
                }
                
                .info-icon {
                    font-size: 3rem;
                    color: var(--primary-green);
                    margin-bottom: 1.5rem;
                }
                
                .contact-info-card h4 {
                    font-size: 1.3rem;
                    margin-bottom: 1rem;
                    color: var(--navy-blue);
                }
                
                .contact-info-card p {
                    color: var(--medium-gray);
                    line-height: 1.6;
                }
                
                /* CTA Section */
                .cta-section {
                    padding: 5rem 0;
                    background: var(--gradient-primary);
                    color: var(--navy-blue);
                    text-align: center;
                }
                
                .cta-content h2 {
                    font-size: 2.5rem;
                    margin-bottom: 1rem;
                    color: var(--navy-blue);
                }
                
                .cta-content p {
                    font-size: 1.2rem;
                    margin-bottom: 2rem;
                    opacity: 0.8;
                }
                
                .cta-actions {
                    display: flex;
                    gap: 1rem;
                    justify-content: center;
                    flex-wrap: wrap;
                }
                
                /* Responsive Design */
                @media (max-width: 768px) {
                    .story-grid {
                        grid-template-columns: 1fr;
                        gap: 2rem;
                    }
                    
                    .mission-vision-grid {
                        grid-template-columns: 1fr;
                        gap: 2rem;
                    }
                    
                    .values-grid {
                        grid-template-columns: 1fr;
                    }
                    
                    .team-grid {
                        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    }
                    
                    .stats-grid {
                        grid-template-columns: repeat(2, 1fr);
                    }
                    
                    .contact-info-grid {
                        grid-template-columns: 1fr;
                    }
                    
                    .cta-actions {
                        flex-direction: column;
                        align-items: center;
                    }
                    
                    .story-content h2 {
                        font-size: 2rem;
                    }
                    
                    .mission-card h3,
                    .vision-card h3 {
                        font-size: 1.5rem;
                    }
                    
                    .section-title {
                        font-size: 2rem;
                    }
                }
                
                @media (max-width: 480px) {
                    .stats-grid {
                        grid-template-columns: 1fr;
                    }
                    
                    .image-placeholder {
                        padding: 2rem 1rem;
                    }
                    
                    .image-placeholder i {
                        font-size: 3rem;
                    }
                    
                    .mission-card,
                    .vision-card {
                        padding: 2rem;
                    }
                }
            </style>
        `);
    </script>
</body>
</html>