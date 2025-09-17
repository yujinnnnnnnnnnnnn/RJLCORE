# AppliancePro - Appliances Management System (PHP + MySQL)

## Requirements
- XAMPP (Apache + MySQL + PHP 8+)
- Enable mod_rewrite in Apache

## Install (XAMPP)
1. Copy this folder into `htdocs` (e.g., `C:\xampp\htdocs\appliancepro` or `/opt/lampp/htdocs/appliancepro`).
2. Create the database:
   - Open phpMyAdmin (`http://localhost/phpmyadmin`)
   - Run SQL from `database/schema.sql`
3. Configure DB credentials in `config/db.php` (host, username, password).
4. Optional: adjust `config/app.php` `base_url` if not using web root (e.g., `/appliancepro/`).
5. Seed sample data:
   - Visit `http://localhost/appliancepro/database/seed.php` once
   - Admin account: `admin@example.com` / `admin123` (delete `database/seed.php` after)
6. Visit the app:
   - Home: `http://localhost/appliancepro/`
   - Admin/Staff: `http://localhost/appliancepro/admin/`
   - Customer: `http://localhost/appliancepro/customer/`

## Cron (Reminders + Dispatch)
- Queue installment reminders (daily):
  - `php /path/to/htdocs/appliancepro/cron/send_reminders.php`
- Send queued notifications (every 5 min):
  - `php /path/to/htdocs/appliancepro/cron/dispatch_notifications.php`

## Features Implemented
- Auth: login, signup (customers), password reset via email
- RBAC: Admin, Staff, Customer with protected routes
- Inventory CRUD (Admin/Staff)
- Sales & POS (single item demo), receipts, auto-installment schedules
- Customer portal: dashboard, purchase history, installments, profile
- Reports: sales chart (Chart.js), low-stock list
- Notifications: due/overdue installment reminders via email + logs

## Theming
- Colors: Green `#C6D870`, Beige `#E6CFA9`, Navy `#113F67`, Black `#000000`
- Responsive layout, hover transitions, subtle animations

## Hardening Tips (Production)
- Set a strong MySQL password and update `config/db.php`
- Configure a real SMTP (PHPMailer) instead of `mail()` in `utils/mail.php`
- Remove `database/seed.php` after initial setup
- Use HTTPS and secure cookies