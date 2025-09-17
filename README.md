# Appliances Management System

A comprehensive full-stack web application for managing appliance sales, inventory, customer accounts, and installment payments. Built with PHP, MySQL, HTML, CSS, JavaScript, and AJAX.

## Features

### 🏠 Home Page
- Modern landing page with professional design
- Company branding and introduction
- Navigation to different portals
- Service highlights and product categories

### 🔐 Authentication System
- **Staff/Admin Login**: Secure login for administrative users
- **Customer Portal**: Self-registration and login for customers
- **Role-based Access Control**: Admin, Staff, and Customer roles
- **Password Security**: Hashed passwords with recovery system
- **Session Management**: Secure session handling with timeouts

### 👨‍💼 Admin/Staff Portal
- **Dashboard**: Overview with statistics and charts
- **Inventory Management**: Add, edit, delete, and monitor stock levels
- **Sales Management**: Record sales, process transactions, generate receipts
- **Customer Management**: Maintain customer records and purchase history
- **Installment Tracking**: Monitor payment schedules and due dates
- **Reports & Analytics**: Sales reports and performance dashboards
- **User Management**: Admin-controlled account creation for staff

### 👤 Customer Portal
- **Personal Dashboard**: Welcome page with account overview
- **Purchase History**: View all past transactions and products
- **Installment Status**: Track payment schedules and balances
- **Notifications**: Payment reminders and account updates
- **Profile Management**: Update contact details and passwords

### 💰 Payment System
- **Full Payment**: Complete payment at purchase
- **Installment Plans**: Flexible payment schedules
- **Payment Tracking**: Monitor due dates and payment status
- **Automated Reminders**: Email notifications for due payments

## Technical Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL (via XAMPP)
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **AJAX**: Dynamic content loading and form submissions
- **Charts**: Chart.js for analytics and reports
- **Icons**: Font Awesome 6
- **Security**: Prepared statements, password hashing, session management

## Color Scheme

- **Green**: #C6D870 (Primary accent)
- **Beige**: #E6CFA9 (Secondary accent)
- **Navy Blue**: #113F67 (Primary text/backgrounds)
- **Black**: #000000 (Text and footer)

## Installation

### Prerequisites
- XAMPP (or similar LAMP/WAMP stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Safari, Edge)

### Setup Steps

1. **Download and Install XAMPP**
   - Download from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Install and start Apache and MySQL services

2. **Clone/Copy Project Files**
   ```bash
   # Copy all project files to your XAMPP htdocs directory
   # Example: C:\xampp\htdocs\appliance_store\
   ```

3. **Database Setup**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the database schema:
     ```sql
     # Run the SQL file: database/appliance_store.sql
     ```
   - Or create the database manually using the provided SQL script

4. **Configuration**
   - Update database credentials in `config/database.php` if needed
   - Configure email settings in `config/config.php` for password recovery

5. **Access the Application**
   - Home Page: `http://localhost/appliance_store/`
   - Admin Login: `http://localhost/appliance_store/login.php`
   - Customer Portal: `http://localhost/appliance_store/customer_login.php`

## Default Login Credentials

### Admin Account
- **Username**: `admin`
- **Password**: `admin123`
- **Email**: `admin@appliancestore.com`

### Test Customer Account
You can create customer accounts through the registration page at:
`http://localhost/appliance_store/customer_register.php`

## Directory Structure

```
appliance_store/
├── admin/                  # Admin/Staff portal pages
│   ├── dashboard.php
│   ├── inventory.php
│   ├── sales.php
│   ├── customers.php
│   ├── installments.php
│   ├── transactions.php
│   ├── reports.php
│   ├── users.php
│   ├── settings.php
│   └── profile.php
├── customer/              # Customer portal pages
│   ├── dashboard.php
│   ├── purchases.php
│   ├── installments.php
│   ├── notifications.php
│   └── profile.php
├── assets/               # Static assets
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/          # Product and company images
├── classes/             # PHP classes
│   └── Auth.php
├── config/              # Configuration files
│   ├── config.php
│   └── database.php
├── database/            # Database schema
│   └── appliance_store.sql
├── uploads/             # File uploads directory
├── index.php           # Home page
├── about.php           # About us page
├── login.php           # Staff/Admin login
├── customer_login.php  # Customer login
├── customer_register.php # Customer registration
├── logout.php          # Logout handler
└── README.md           # This file
```

## Key Features Implemented

### 🎨 Modern UI/UX
- Professional design with smooth animations
- Responsive layout for mobile and desktop
- CSS transitions and hover effects
- Modern color scheme and typography
- Interactive elements with visual feedback

### 🔒 Security Features
- Password hashing with PHP's password_hash()
- SQL injection prevention with prepared statements
- Session-based authentication with timeouts
- Role-based access control
- Input sanitization and validation

### 📊 Business Intelligence
- Real-time dashboard statistics
- Sales and inventory analytics
- Chart.js integration for visual reports
- Performance tracking and KPIs
- Low stock alerts and notifications

### 💻 AJAX Integration
- Dynamic form submissions without page reload
- Real-time data updates
- Smooth user interactions
- Progress indicators and loading states
- Error handling and user feedback

### 📱 Responsive Design
- Mobile-first approach
- Flexible grid system
- Responsive navigation
- Touch-friendly interface
- Cross-browser compatibility

## Database Schema

### Core Tables
- **users**: User accounts with role-based access
- **roles**: User roles (admin, staff, customer)
- **products**: Appliance inventory
- **sales**: Sales transactions
- **sale_items**: Individual items in each sale
- **installments**: Payment schedules
- **transactions**: Payment records
- **notifications**: Customer communications
- **inventory_logs**: Stock change tracking

## Usage Examples

### Adding Products (Admin/Staff)
1. Login to admin portal
2. Navigate to Inventory → Add Product
3. Fill in product details (name, brand, price, stock)
4. Set minimum stock levels for alerts
5. Save and manage stock levels

### Recording Sales
1. Access Sales module
2. Select customer (or create new)
3. Add products to sale
4. Choose payment type (full/installment)
5. Generate receipt and set payment schedule

### Customer Experience
1. Register for customer account
2. View purchase history and product details
3. Track installment payments and due dates
4. Receive notifications for upcoming payments
5. Update profile and contact information

## Customization

### Adding New Product Categories
Update the categories in the inventory management system and add corresponding images.

### Modifying Payment Plans
Adjust installment calculation logic in the sales processing system.

### Customizing Notifications
Modify notification templates and triggers in the notification system.

### Styling Changes
Update CSS variables in `assets/css/style.css` to change colors and styling.

## Support and Maintenance

### Regular Tasks
- Monitor low stock levels
- Process pending payments
- Generate monthly reports
- Backup database regularly
- Update product information

### Troubleshooting
- Check XAMPP services are running
- Verify database connection settings
- Clear browser cache for updates
- Check PHP error logs for issues
- Ensure proper file permissions

## Future Enhancements

### Potential Features
- Online payment integration (PayPal, Stripe)
- Email notification system
- Barcode scanning for inventory
- Mobile app development
- Advanced reporting and analytics
- Multi-location support
- Supplier management
- Warranty tracking system

## License

This project is developed for educational and business purposes. Please ensure compliance with local business regulations and data protection laws when implementing in production.

## Contact

For support or questions about this system, please contact your development team or system administrator.

---

**Version**: 1.0.0  
**Last Updated**: 2024  
**Developed with**: Modern web technologies and best practices