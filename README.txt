SUVASH BASKOTA PREMIUM PORTFOLIO
================================

REQUIREMENTS
- XAMPP (Apache + MySQL)
- PHP 8+ recommended
- MySQL/MariaDB

INSTALL
1. Copy the folder "suvash-premium-portfolio" to:
   C:\xampp\htdocs\suvash-portfolio

2. Start Apache and MySQL in XAMPP.

3. Open phpMyAdmin:
   http://localhost/phpmyadmin

4. Create/import the database:
   Import database.sql
   (It creates database "suvash_portfolio".)

5. IMPORTANT:
   Open config/config.php and confirm:
   DB_HOST=localhost
   DB_NAME=suvash_portfolio
   DB_USER=root
   DB_PASS=

   If your XAMPP MySQL has a password, put it in DB_PASS.

6. Open:
   http://localhost/suvash-portfolio/

ADMIN
Admin URL:
http://localhost/suvash-portfolio/admin/login.php

Username:
admin

Password:
admin123

For production, immediately change the admin password in the database.
The website uses password_hash/password_verify for login.

FEATURES
- Premium responsive personal portfolio
- PHP + MySQL
- Secure PDO prepared statements
- Admin Login/Logout
- Dashboard statistics
- Blog Add/Edit/Delete
- Draft/Published blog status
- Blog cover upload
- Certificate upload/delete
- Contact form saved to MySQL
- Admin message inbox
- CV download
