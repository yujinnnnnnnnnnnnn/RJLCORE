<?php
require_once 'config/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - <?php echo APP_NAME; ?></title>
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
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php" class="active">About</a></li>
                    <li><a href="index.php#services">Services</a></li>
                    <li><a href="login.php">Staff Login</a></li>
                    <li><a href="customer_login.php" class="btn btn-primary">Customer Portal</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" style="padding: 4rem 0;">
        <div class="hero-content">
            <div class="container">
                <h1>About ApplianceStore</h1>
                <p>Your trusted partner for quality home appliances since 2009</p>
            </div>
        </div>
    </section>

    <!-- Company Overview -->
    <section style="padding: 6rem 0; background: white;">
        <div class="container">
            <div class="row align-center">
                <div class="col-6">
                    <div class="company-image" style="height: 500px; background: linear-gradient(135deg, var(--navy), var(--green)); border-radius: 20px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.3);"></div>
                        <div style="position: relative; z-index: 2; text-align: center; color: white;">
                            <i class="fas fa-building" style="font-size: 6rem; margin-bottom: 2rem; opacity: 0.9;"></i>
                            <h3 style="color: white; margin-bottom: 1rem;">[Company Building Image]</h3>
                            <p style="opacity: 0.8;">Your company photo will be placed here</p>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <h2>Our Story</h2>
                    <p class="lead">Founded in 2009, ApplianceStore began as a small family business with a simple mission: to make quality home appliances accessible and affordable for everyone.</p>
                    
                    <p>What started as a modest showroom has grown into a comprehensive appliance destination, serving thousands of satisfied customers across the region. We've built our reputation on three core principles: quality products, exceptional service, and flexible payment options.</p>
                    
                    <p>Today, we partner with leading appliance manufacturers to bring you the latest in home technology, from energy-efficient refrigerators to smart washing machines. Our experienced team provides expert guidance to help you find the perfect appliances for your home and lifestyle.</p>

                    <div class="company-values mt-4">
                        <h4>Our Values</h4>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin-bottom: 1rem; display: flex; align-items: center;">
                                <i class="fas fa-check-circle" style="color: var(--green); margin-right: 1rem;"></i>
                                <span><strong>Quality First:</strong> We only stock appliances from trusted brands</span>
                            </li>
                            <li style="margin-bottom: 1rem; display: flex; align-items: center;">
                                <i class="fas fa-check-circle" style="color: var(--green); margin-right: 1rem;"></i>
                                <span><strong>Customer Service:</strong> Your satisfaction is our priority</span>
                            </li>
                            <li style="margin-bottom: 1rem; display: flex; align-items: center;">
                                <i class="fas fa-check-circle" style="color: var(--green); margin-right: 1rem;"></i>
                                <span><strong>Accessibility:</strong> Flexible payment plans for everyone</span>
                            </li>
                            <li style="margin-bottom: 1rem; display: flex; align-items: center;">
                                <i class="fas fa-check-circle" style="color: var(--green); margin-right: 1rem;"></i>
                                <span><strong>Reliability:</strong> Professional installation and ongoing support</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section style="padding: 6rem 0; background: var(--light-gray);">
        <div class="container">
            <div class="row">
                <div class="col-6">
                    <div class="card text-center">
                        <div style="background: linear-gradient(135deg, var(--navy), var(--green)); color: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; font-size: 2rem;">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3>Our Mission</h3>
                        <p>To provide high-quality home appliances with exceptional service and flexible payment options, making modern living accessible to families and businesses in our community.</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card text-center">
                        <div style="background: linear-gradient(135deg, var(--green), var(--beige)); color: var(--navy); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; font-size: 2rem;">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3>Our Vision</h3>
                        <p>To be the leading appliance retailer in the region, known for our comprehensive product selection, innovative financing solutions, and commitment to customer satisfaction.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section style="padding: 6rem 0; background: white;">
        <div class="container">
            <h2 class="text-center mb-5">Meet Our Team</h2>
            <div class="row">
                <div class="col-4">
                    <div class="card text-center">
                        <div class="team-image" style="width: 150px; height: 150px; background: linear-gradient(135deg, var(--navy), var(--green)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; color: white; font-size: 3rem;">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h4>John Smith</h4>
                        <p class="text-gray">General Manager</p>
                        <p>With over 15 years in the appliance industry, John leads our team with expertise and dedication to customer service.</p>
                        <div class="placeholder-text" style="color: var(--gray); font-style: italic; margin-top: 1rem; font-size: 0.9rem;">
                            [Manager photo placeholder]
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card text-center">
                        <div class="team-image" style="width: 150px; height: 150px; background: linear-gradient(135deg, var(--green), var(--beige)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; color: var(--navy); font-size: 3rem;">
                            <i class="fas fa-user"></i>
                        </div>
                        <h4>Sarah Johnson</h4>
                        <p class="text-gray">Sales Manager</p>
                        <p>Sarah's product knowledge and friendly approach help customers find the perfect appliances for their needs and budget.</p>
                        <div class="placeholder-text" style="color: var(--gray); font-style: italic; margin-top: 1rem; font-size: 0.9rem;">
                            [Sales Manager photo placeholder]
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card text-center">
                        <div class="team-image" style="width: 150px; height: 150px; background: linear-gradient(135deg, var(--beige), var(--navy)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; color: white; font-size: 3rem;">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h4>Mike Rodriguez</h4>
                        <p class="text-gray">Installation Supervisor</p>
                        <p>Mike and his certified team ensure every appliance is properly installed and ready for years of reliable service.</p>
                        <div class="placeholder-text" style="color: var(--gray); font-style: italic; margin-top: 1rem; font-size: 0.9rem;">
                            [Installation Supervisor photo placeholder]
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics -->
    <section style="padding: 6rem 0; background: linear-gradient(135deg, var(--navy), var(--green)); color: white;">
        <div class="container">
            <h2 class="text-center mb-5" style="color: white;">Our Achievements</h2>
            <div class="stats-grid">
                <div class="stat-card" style="background: rgba(255,255,255,0.1); color: white;">
                    <div class="stat-number">15+</div>
                    <div class="stat-label">Years of Experience</div>
                </div>
                <div class="stat-card" style="background: rgba(255,255,255,0.1); color: white;">
                    <div class="stat-number">10,000+</div>
                    <div class="stat-label">Happy Customers</div>
                </div>
                <div class="stat-card" style="background: rgba(255,255,255,0.1); color: white;">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Products Available</div>
                </div>
                <div class="stat-card" style="background: rgba(255,255,255,0.1); color: white;">
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Customer Satisfaction</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Location & Contact -->
    <section style="padding: 6rem 0; background: white;">
        <div class="container">
            <h2 class="text-center mb-5">Visit Our Store</h2>
            <div class="row">
                <div class="col-6">
                    <div class="card">
                        <h3><i class="fas fa-map-marker-alt text-green"></i> Store Location</h3>
                        <div class="store-info">
                            <div style="margin-bottom: 1.5rem;">
                                <h4>ApplianceStore Showroom</h4>
                                <p>
                                    <i class="fas fa-map-marker-alt" style="color: var(--green); margin-right: 0.5rem;"></i>
                                    123 Appliance Street<br>
                                    Downtown District<br>
                                    City, State 12345
                                </p>
                            </div>
                            
                            <div style="margin-bottom: 1.5rem;">
                                <h4>Store Hours</h4>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                    <span>Monday - Friday:</span><span>9:00 AM - 7:00 PM</span>
                                    <span>Saturday:</span><span>9:00 AM - 6:00 PM</span>
                                    <span>Sunday:</span><span>10:00 AM - 5:00 PM</span>
                                </div>
                            </div>
                            
                            <div>
                                <h4>Contact Information</h4>
                                <p>
                                    <i class="fas fa-phone" style="color: var(--green); margin-right: 0.5rem;"></i>
                                    <strong>Phone:</strong> (555) 123-4567<br>
                                    
                                    <i class="fas fa-envelope" style="color: var(--green); margin-right: 0.5rem;"></i>
                                    <strong>Email:</strong> info@appliancestore.com<br>
                                    
                                    <i class="fas fa-globe" style="color: var(--green); margin-right: 0.5rem;"></i>
                                    <strong>Website:</strong> www.appliancestore.com
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="store-map" style="height: 400px; background: linear-gradient(135deg, var(--beige), var(--green)); border-radius: 15px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.1);"></div>
                        <div style="position: relative; z-index: 2; text-align: center; color: var(--navy);">
                            <i class="fas fa-map" style="font-size: 5rem; margin-bottom: 1rem; opacity: 0.8;"></i>
                            <h4>Interactive Map</h4>
                            <p style="opacity: 0.8;">[Google Maps integration or store map will be placed here]</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta" style="padding: 4rem 0; background: linear-gradient(135deg, var(--green), var(--beige)); text-align: center;">
        <div class="container">
            <h2>Ready to Upgrade Your Home?</h2>
            <p class="lead">Visit our showroom or browse our customer portal to explore our extensive collection of quality appliances.</p>
            <div class="cta-buttons mt-4">
                <a href="customer_register.php" class="btn btn-primary btn-lg" style="margin-right: 1rem;">Create Account</a>
                <a href="customer_login.php" class="btn btn-secondary btn-lg">Customer Portal</a>
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
                        <a href="#" style="color: var(--green); margin-right: 1rem; font-size: 1.5rem;"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-4">
                    <h4>Quick Links</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 0.5rem;"><a href="index.php" style="color: white; text-decoration: none;">Home</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="about.php" style="color: white; text-decoration: none;">About Us</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="index.php#services" style="color: white; text-decoration: none;">Services</a></li>
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