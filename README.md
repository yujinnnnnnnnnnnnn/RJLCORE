# Appliances Management System (PHP, MySQL, AJAX)

A modern, role-based appliances store management system with Admin/Staff and Customer portals. Built with PHP 8, MySQL, HTML/CSS/JS, and AJAX. Includes inventory, sales (full & installment), receipts (PDF), email notifications, analytics (Chart.js), and responsive UI.

## Tech Stack
- PHP 8+
- MySQL (XAMPP)
- HTML, CSS, JavaScript, AJAX
- PHPMailer, FPDF, Dotenv, UUID
- Chart.js (CDN)

## Quick Start (XAMPP - Windows)
1. Start Apache and MySQL in XAMPP.
2. Clone this repo into `htdocs` (e.g., `C:\xampp\htdocs\appliances`).
3. Copy `.env.example` to `.env` and update DB and mail settings.
4. Create database and import schema:
   - Open `phpMyAdmin` → create database `appliances_db` (utf8mb4)
   - Import `database/schema.sql`
5. Install PHP dependencies:
   - Open terminal in project folder and run `composer install`
6. Visit `http://localhost/appliances/public/`.

## Default Roles
- Admin, Staff, Customer. Create first Admin via DB or Admin Settings after initial setup.

## Security Notes
- Uses prepared statements (PDO) and secure sessions.
- Passwords hashed with `password_hash()` (bcrypt/Argon2 depending on PHP).
- CSRF tokens added on sensitive AJAX forms.

## Structure
```
public/          # Public web root (index, assets)
app/             # Core libs (config, db, auth, mail, pdf)
views/           # Shared templates
admin/           # Admin/Staff portal pages
customer/        # Customer portal pages
api/             # AJAX endpoints
database/        # SQL schema and seeds
```

## Theming
- Colors: Green `#C6D870`, Beige `#E6CFA9`, Navy `#113F67`, Black `#000000`
- Smooth transitions, hover effects, and page fade-ins.

## SMTP & Password Reset
- Configure SMTP in `.env` to send reset links and reminders.
- If not configured, app will log emails to `storage/mail.log`.

## PDF Receipts
- Uses FPDF. Receipts printable from Sales module and Customer purchases.

## Development
- Enable `APP_DEBUG=true` for verbose errors (do not use in production).
- Run `composer dump-autoload` after adding new classes.

## License
MIT