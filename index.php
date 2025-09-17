<?php
require_once 'config/config.php';

// Check if user is already logged in and redirect accordingly
if (is_logged_in()) {
    $role = $_SESSION['role_name'];
    if ($role === 'admin' || $role === 'staff') {
        redirect('admin/dashboard.php');
    } elseif ($role === 'customer') {
        redirect('customer/dashboard.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Your Trusted Appliance Partner</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <a href="index.php" class="logo">
                <i class="fas fa-home"></i> ApplianceStore
            </a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="login.php">Staff Login</a></li>
                    <li><a href="customer_login.php" class="btn btn-primary">Customer Portal</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <div class="container">
                <h1>Welcome to ApplianceStore</h1>
                <p>Your trusted partner for quality home appliances with flexible payment options. Discover our wide range of premium appliances with professional installation and ongoing support.</p>
                <div class="hero-buttons">
                    <a href="customer_login.php" class="btn btn-lg">Customer Portal</a>
                    <a href="#about" class="btn btn-secondary btn-lg">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" style="padding: 6rem 0; background: white;">
        <div class="container">
            <h2 class="text-center mb-5">Why Choose ApplianceStore?</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Premium Appliances</div>
                    <p class="mt-3">From refrigerators to washing machines, we have everything for your home.</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="stat-number">Flexible</div>
                    <div class="stat-label">Payment Options</div>
                    <p class="mt-3">Full payment or installment plans to fit your budget and needs.</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-number">Expert</div>
                    <div class="stat-label">Installation</div>
                    <p class="mt-3">Professional installation and setup service for all appliances.</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="stat-number">2 Years</div>
                    <div class="stat-label">Warranty</div>
                    <p class="mt-3">Comprehensive warranty coverage on all our appliances.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Categories -->
    <section class="categories" style="padding: 6rem 0; background: var(--light-gray);">
        <div class="container">
            <h2 class="text-center mb-5">Our Product Categories</h2>
            <div class="row">
                <div class="col-4">
                    <div class="card text-center">
                        <div class="category-image" style="height: 200px; background: linear-gradient(135deg, var(--beige), var(--green)); display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; border-radius: 10px;">
                            <i class="fas fa-snowflake" style="font-size: 4rem; color: var(--navy); opacity: 0.8;"></i>
                        </div>
                        <h4>Refrigerators</h4>
                        <p>Energy-efficient refrigerators from top brands with various sizes and features.</p>
                        <div class="placeholder-text" style="color: var(--gray); font-style: italic; margin-top: 1rem;">
                            [Product images will be placed here]
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card text-center">
                        <div class="category-image" style="height: 200px; background: linear-gradient(135deg, var(--green), var(--navy)); display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; border-radius: 10px;">
                            <i class="fas fa-tshirt" style="font-size: 4rem; color: white; opacity: 0.9;"></i>
                        </div>
                        <h4>Washing Machines</h4>
                        <p>Front-load and top-load washing machines with advanced cleaning technology.</p>
                        <div class="placeholder-text" style="color: var(--gray); font-style: italic; margin-top: 1rem;">
                            [Product images will be placed here]
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card text-center">
                        <div class="category-image" style="height: 200px; background: linear-gradient(135deg, var(--navy), var(--beige)); display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; border-radius: 10px;">
                            <i class="fas fa-utensils" style="font-size: 4rem; color: var(--green); opacity: 0.9;"></i>
                        </div>
                        <h4>Kitchen Appliances</h4>
                        <p>Complete kitchen solutions including dishwashers, microwaves, and more.</p>
                        <div class="placeholder-text" style="color: var(--gray); font-style: italic; margin-top: 1rem;">
                            [Product images will be placed here]
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services" style="padding: 6rem 0; background: white;">
        <div class="container">
            <h2 class="text-center mb-5">Our Services</h2>
            <div class="row">
                <div class="col-6">
                    <div class="card">
                        <h4><i class="fas fa-shopping-cart text-green"></i> Sales & Purchase</h4>
                        <p>Browse our extensive catalog of premium appliances. Our knowledgeable staff will help you find the perfect appliance for your needs and budget.</p>
                        <ul>
                            <li>Wide selection of brands</li>
                            <li>Competitive pricing</li>
                            <li>Expert product advice</li>
                            <li>Product demonstrations</li>
                        </ul>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <h4><i class="fas fa-calendar-alt text-green"></i> Installment Plans</h4>
                        <p>Flexible payment options to make premium appliances affordable. Choose from various installment plans that fit your budget.</p>
                        <ul>
                            <li>0% interest options available</li>
                            <li>Flexible payment schedules</li>
                            <li>Online payment tracking</li>
                            <li>Automatic payment reminders</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-6">
                    <div class="card">
                        <h4><i class="fas fa-tools text-green"></i> Installation & Setup</h4>
                        <p>Professional installation service ensures your appliances are properly set up and ready to use safely and efficiently.</p>
                        <ul>
                            <li>Certified technicians</li>
                            <li>Same-day installation</li>
                            <li>Safety inspections</li>
                            <li>Operation training</li>
                        </ul>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <h4><i class="fas fa-headset text-green"></i> Customer Support</h4>
                        <p>Ongoing support to ensure your satisfaction. From maintenance tips to warranty service, we're here to help.</p>
                        <ul>
                            <li>24/7 customer support</li>
                            <li>Warranty service</li>
                            <li>Maintenance guidance</li>
                            <li>Online account management</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about" style="padding: 6rem 0; background: var(--light-gray);">
        <div class="container">
            <div class="row align-center">
                <div class="col-6">
                    <h2>About ApplianceStore</h2>
                    <p class="lead">With over 15 years of experience in the appliance industry, ApplianceStore has been serving families and businesses with quality home appliances and exceptional service.</p>
                    <p>Our mission is to make premium appliances accessible to everyone through flexible payment options and outstanding customer service. We partner with leading brands to bring you the latest in home appliance technology.</p>
                    <div class="about-stats mt-4">
                        <div class="row">
                            <div class="col-6">
                                <h4 class="text-green">15+</h4>
                                <p>Years of Experience</p>
                            </div>
                            <div class="col-6">
                                <h4 class="text-green">10,000+</h4>
                                <p>Happy Customers</p>
                            </div>
                        </div>
                    </div>
                    <a href="about.php" class="btn btn-primary mt-3">Learn More About Us</a>
                </div>
                <div class="col-6">
                    <div class="about-image" style="height: 400px; background: linear-gradient(135deg, var(--navy), var(--green)); border-radius: 15px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.3);"></div>
                        <div style="position: relative; z-index: 2; text-align: center; color: white;">
                            <i class="fas fa-store" style="font-size: 5rem; margin-bottom: 1rem; opacity: 0.9;"></i>
                            <p style="font-size: 1.2rem; margin: 0;">[Company/Store Image Placeholder]</p>
                            <p style="font-size: 0.9rem; opacity: 0.8; margin: 0.5rem 0 0 0;">Your image will be placed here</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta" style="padding: 4rem 0; background: linear-gradient(135deg, var(--navy), var(--green)); color: white; text-align: center;">
        <div class="container">
            <h2>Ready to Get Started?</h2>
            <p class="lead">Join thousands of satisfied customers who trust ApplianceStore for their home appliance needs.</p>
            <div class="cta-buttons mt-4">
                <a href="customer_register.php" class="btn btn-lg" style="background: white; color: var(--navy); margin-right: 1rem;">Create Account</a>
                <a href="customer_login.php" class="btn btn-secondary btn-lg">Login to Portal</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background: var(--black); color: white; padding: 3rem 0;">
        <div class="container">
            <div class="row">
                <div class="col-4">
                    <h4>ApplianceStore</h4>
                    <p>Your trusted partner for quality home appliances with flexible payment options.</p>
                    <div class="social-links mt-3">
                        <a href="#" style="color: var(--green); margin-right: 1rem; font-size: 1.5rem;"><i class="fab fa-facebook"></i></a>
                        <a href="#" style="color: var(--green); margin-right: 1rem; font-size: 1.5rem;"><i class="fab fa-twitter"></i></a>
                        <a href="#" style="color: var(--green); margin-right: 1rem; font-size: 1.5rem;"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-4">
                    <h4>Quick Links</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 0.5rem;"><a href="#home" style="color: white; text-decoration: none;">Home</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="#about" style="color: white; text-decoration: none;">About Us</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="#services" style="color: white; text-decoration: none;">Services</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="customer_login.php" style="color: white; text-decoration: none;">Customer Portal</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="login.php" style="color: white; text-decoration: none;">Staff Login</a></li>
                    </ul>
                </div>
                <div class="col-4">
                    <h4>Contact Info</h4>
                    <div style="margin-bottom: 1rem;">
                        <i class="fas fa-map-marker-alt" style="color: var(--green); margin-right: 0.5rem;"></i>
                        123 Appliance Street, City, State 12345
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <i class="fas fa-phone" style="color: var(--green); margin-right: 0.5rem;"></i>
                        (555) 123-4567
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <i class="fas fa-envelope" style="color: var(--green); margin-right: 0.5rem;"></i>
                        info@appliancestore.com
                    </div>
                    <div>
                        <i class="fas fa-clock" style="color: var(--green); margin-right: 0.5rem;"></i>
                        Mon-Sat: 9AM-7PM, Sun: 10AM-5PM
                    </div>
                </div>
            </div>
            <div style="border-top: 1px solid #333; margin-top: 2rem; padding-top: 2rem; text-align: center;">
                <p>&copy; 2024 ApplianceStore. All rights reserved. | Designed with modern technology and care.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>