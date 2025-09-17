## Appliance Management System (PHP + MySQL)

Requirements: XAMPP (Apache, MySQL), PHP 8.1+

### Quick Start
1. Copy the `appliance-system` folder into your XAMPP `htdocs`.
2. Create a MySQL database: `appliance_system`.
3. Import `database/schema.sql` into the database.
4. Create `.env` in project root:
```
APP_NAME=Appliance Management System
APP_ENV=local
APP_DEBUG=true
APP_URL=
APP_TIMEZONE=UTC

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=appliance_system
DB_USERNAME=root
DB_PASSWORD=
```
5. Start Apache and MySQL in XAMPP. Visit `http://localhost/appliance-system/public/`.

### Default Routes
- `/home` Home page
- `/about` About page
- `/login` Login
- `/register` Customer sign up
- `/dashboard` Admin/Staff dashboard (requires login)
- `/customer` Customer dashboard (requires login)

### Notes
- Update `config/app.php` theme colors to match your branding.
- `.htaccess` under `public/` enables pretty URLs.

