PDMS - PHP + MySQL Sample System
================================

What's included:
- config.php : edit DB credentials
- index.php : login page
- dashboard.php : main app
- logout.php
- actions/     : POST handlers for add/delete/update
- assets/style.css : basic styling
- install.sql : database creation + sample admin (password: admin123)
- README.txt

Setup:
1. Create a MySQL database or let install.sql create one.
2. Import install.sql into your MySQL server (phpMyAdmin or mysql CLI).
   Example: mysql -u root -p < install.sql
3. Edit config.php to set your DB credentials if needed.
4. Put the project into your web server docroot (e.g., /var/www/html/pdms_full_system)
5. Access index.php in your browser.

Notes:
- The default admin user has password 'admin123' stored as MD5 for legacy compatibility.
- New users are stored with password_hash. Login checks both md5 and password_hash for backwards compatibility.
- This is a simple demo—do not use in production without securing it (prepared statements, CSRF tokens, input validation).
