# Appliances Management System

A comprehensive web-based appliances management system built with PHP, HTML, CSS, JavaScript, and MySQL. This system provides complete functionality for managing appliance sales, inventory, customer relationships, and installment payments.

## 🌟 Features

### 🏠 **Homepage & Public Pages**
- Modern, responsive design with appliance-themed branding
- Professional homepage with company information
- About Us page with company story, mission, and team
- Contact information and inquiry forms

### 🔐 **Authentication System**
- **Multi-role login system** (Admin, Staff, Customer)
- **Self-registration** for customers
- **Secure password hashing** with bcrypt
- **Password reset** via email tokens
- **Role-based access control**
- **Session management** with timeout protection

### 👨‍💼 **Admin & Staff Portal**
- **Inventory Management**
  - Add, edit, remove products
  - Monitor stock levels and alerts
  - Category management
  - Product specifications and images
  
- **Sales & Transactions**
  - Point-of-sale system
  - Full and installment payment processing
  - Receipt and invoice generation
  - Transaction history and tracking
  
- **Customer Management**
  - Customer database and profiles
  - Purchase history tracking
  - Installment payment schedules
  - Automated payment reminders
  
- **Reports & Analytics**
  - Sales reports and trends
  - Inventory reports
  - Customer analytics
  - Performance dashboards with charts
  
- **System Administration**
  - User account management
  - Role and permission settings
  - System configuration
  - Audit logging

### 👤 **Customer Portal**
- **Personal Dashboard**
  - Purchase history overview
  - Installment payment status
  - Account information
  
- **Payment Management**
  - View payment schedules
  - Track due dates and balances
  - Payment history
  
- **Notifications**
  - Email reminders for payments
  - Account updates and alerts
  
- **Profile Management**
  - Update contact information
  - Change passwords
  - View transaction history

### 🎨 **Design & User Experience**
- **Modern Color Scheme**: Green (#C6D870), Beige (#E6CFA9), Navy Blue (#113F67), Black (#000000)
- **Responsive Design**: Optimized for desktop, tablet, and mobile
- **Smooth Animations**: Hover effects, transitions, and fade-ins
- **Professional UI**: Clean, modern appliance-system aesthetic
- **Accessibility**: User-friendly navigation and forms

## 🛠️ **Technology Stack**

- **Backend**: PHP 7.4+ with PDO
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Database**: MySQL 5.7+ / MariaDB
- **Server**: XAMPP (Apache, MySQL, PHP)
- **Icons**: Font Awesome 6.0
- **Architecture**: MVC-inspired structure

## 📋 **Requirements**

- **XAMPP** (or LAMP/WAMP) with:
  - PHP 7.4 or higher
  - MySQL 5.7 or higher
  - Apache web server
- **Web Browser**: Modern browser with JavaScript enabled
- **Email Server**: For password reset and notifications (optional)

## 🚀 **Installation & Setup**

### 1. **Download and Extract**
```bash
# Extract the project to your XAMPP htdocs directory
# Example: C:\xampp\htdocs\appliances-management\
```

### 2. **Start XAMPP Services**
- Start Apache and MySQL from XAMPP Control Panel
- Ensure both services are running (green status)

### 3. **Database Configuration**
- Open `config/database.php`
- Verify database settings (default: localhost, root, no password)
- Modify if your XAMPP uses different credentials

### 4. **Run Setup Script**
- Open your browser and navigate to: `http://localhost/appliances-management/setup.php`
- This will:
  - Create the database and tables
  - Insert sample data and categories
  - Create default user accounts
  - Set up the system configuration

### 5. **Access the System**
- **Homepage**: `http://localhost/appliances-management/`
- **Login Page**: `http://localhost/appliances-management/login.php`

### 6. **Default Login Accounts**

| Role | Username | Password | Access Level |
|------|----------|----------|--------------|
| Admin | admin | admin123 | Full system access |
| Staff | staff | staff123 | Sales and inventory |
| Customer | customer | customer123 | Customer portal |

### 7. **Security Setup**
- **Delete** `setup.php` after installation
- Change default passwords immediately
- Configure email settings in `config/config.php` for notifications

## 📁 **Project Structure**

```
appliances-management/
├── 📁 assets/
│   ├── 📁 css/
│   │   └── style.css          # Main stylesheet
│   └── 📁 js/
│       └── main.js            # JavaScript functionality
├── 📁 classes/
│   └── Auth.php               # Authentication class
├── 📁 config/
│   ├── config.php             # Main configuration
│   └── database.php           # Database connection
├── 📁 database/
│   └── appliances_management.sql  # Database schema
├── 📁 admin/                  # Admin/Staff portal (to be created)
├── 📁 customer/               # Customer portal (to be created)
├── 📁 uploads/                # File uploads directory
├── index.php                  # Homepage
├── login.php                  # Login page
├── register.php               # Customer registration
├── forgot-password.php        # Password reset
├── about.php                  # About us page
├── logout.php                 # Logout handler
├── setup.php                  # Initial setup script
└── README.md                  # This file
```

## 🗄️ **Database Schema**

### Core Tables
- **users** - User accounts (admin, staff, customers)
- **categories** - Product categories
- **products** - Appliance inventory
- **sales** - Sales transactions
- **sale_items** - Individual items in sales
- **installment_plans** - Payment plan configurations
- **installment_payments** - Individual payment tracking
- **notifications** - System notifications
- **system_settings** - Configuration settings
- **audit_log** - Activity tracking

## 🔧 **Configuration**

### Email Settings
Edit `config/config.php` to configure email notifications:
```php
define('SMTP_HOST', 'your-smtp-server.com');
define('SMTP_USERNAME', 'your-email@domain.com');
define('SMTP_PASSWORD', 'your-app-password');
```

### File Uploads
- Maximum file size: 5MB (configurable)
- Allowed image types: JPG, PNG, GIF, WebP
- Upload directory: `/uploads/` (auto-created)

### Security Settings
- Password minimum length: 8 characters
- Session timeout: 1 hour
- Maximum login attempts: 5
- Lockout duration: 15 minutes

## 📱 **Responsive Design**

The system is fully responsive and optimized for:
- **Desktop**: Full feature access with sidebar navigation
- **Tablet**: Adapted layouts with touch-friendly controls
- **Mobile**: Collapsible menus and stacked layouts

## 🎨 **Customization**

### Color Scheme
The system uses CSS variables for easy color customization:
```css
:root {
    --primary-green: #C6D870;
    --secondary-beige: #E6CFA9;
    --navy-blue: #113F67;
    --black: #000000;
}
```

### Branding
- Update company name in `config/config.php`
- Modify logo and branding in templates
- Customize footer information

## 🔍 **Key Features Detailed**

### Installment Management
- Flexible payment plans (weekly, monthly, quarterly)
- Automatic payment calculation
- Late fee handling
- Payment reminder system
- Balance tracking

### Inventory Control
- Real-time stock monitoring
- Low stock alerts
- Product categorization
- Specification management
- Image upload support

### Reporting System
- Sales analytics with date ranges
- Inventory reports
- Customer payment tracking
- Export capabilities
- Visual dashboards

### Security Features
- Password hashing with bcrypt
- SQL injection protection via PDO
- XSS prevention with input sanitization
- CSRF protection
- Session security
- Audit logging

## 🚨 **Troubleshooting**

### Common Issues

**Database Connection Failed**
- Verify XAMPP MySQL is running
- Check database credentials in `config/database.php`
- Ensure database exists (run setup.php)

**Permission Errors**
- Set proper folder permissions for uploads directory
- Ensure Apache has read/write access

**Login Issues**
- Verify user accounts exist in database
- Check password hashing in users table
- Clear browser cache and cookies

**Email Not Working**
- Configure SMTP settings in config.php
- Check email server credentials
- Verify firewall/antivirus settings

## 🔄 **Updates & Maintenance**

### Regular Maintenance
- Monitor database size and optimize
- Review audit logs for security
- Update user passwords regularly
- Backup database and files
- Check for PHP/MySQL updates

### Performance Optimization
- Enable MySQL query caching
- Optimize database indexes
- Compress CSS/JavaScript files
- Use image optimization
- Implement caching strategies

## 📞 **Support**

For technical support or questions:
- Review this README thoroughly
- Check the troubleshooting section
- Examine error logs in XAMPP
- Verify all requirements are met

## 📄 **License**

This project is developed as a comprehensive appliances management solution. Please ensure you have proper licensing for any third-party components used in production.

## 🎯 **Future Enhancements**

Potential improvements and additions:
- Online payment gateway integration
- Mobile app development
- Advanced reporting with charts
- Inventory forecasting
- Customer loyalty programs
- Multi-location support
- API development for integrations

---

**Happy Managing!** 🏠✨

*This system provides a complete solution for appliance retail businesses with modern web technologies and user-friendly interfaces.*