================================================================================
                    COMPLETE PROJECT REPORT - CONTACT MANAGER DEPLOYMENT
================================================================================

================================================================================
PAGE 1: TITLE PAGE & INTRODUCTION
================================================================================

PROJECT TITLE: Deployment of a Contact Management Web Application on a Contabo 
               Virtual Private Server

--------------------------------------------------------------------------------
STUDENT INFORMATION
--------------------------------------------------------------------------------
Student Names:    Anweer Anous & Amine ELfakir
Course:           Développement WEB JEE
Professor:        Prof. Souissi
Date:             April 4, 2026
Project Type:     Server Deployment & Web Application Hosting

--------------------------------------------------------------------------------
PROJECT REPOSITORY
--------------------------------------------------------------------------------
GitHub URL: https://github.com/amineelfakir35-ops/contact-manager

================================================================================
1. INTRODUCTION
================================================================================

1.1 Project Objective
The objective of this project is to deploy a fully functional Contact Management 
web application on a production server environment. This involves:
- Hosting the source code on GitHub for version control
- Provisioning a Virtual Private Server (VPS) from Contabo
- Configuring a complete LAMP (Linux, Apache, MySQL, PHP) stack
- Installing additional services: FTP, SSH, phpMyAdmin
- Making the application accessible via the internet

1.2 Team Members & Responsibilities

| Name              | Role              | Responsibilities                        |
|-------------------|-------------------|-----------------------------------------|
| Anweer Anous      | DevOps & Backend  | Server configuration, LAMP stack, DB    |
| Amine ELfakir     | Frontend & Deploy | GitHub repository, file deployment, doc |

1.3 Application Description
The Contact Manager application is a PHP-based web system that allows users to:
- Register for a personal account with secure password hashing
- Log in securely to access their contact list
- Add, edit, and delete contacts
- Store complete contact information (name, email, phone number, profile picture)
- Search contacts with smart reordering by relevance
- Upload profile pictures for each contact

1.4 Technologies Used

| Technology          | Version    | Purpose                                   |
|---------------------|------------|-------------------------------------------|
| PHP                 | 8.1.2      | Backend application logic                 |
| MySQL/MariaDB       | 10.6.12    | Database storage for users and contacts   |
| Apache2             | 2.4.52     | Web server to handle HTTP requests        |
| HTML5/CSS3          | -          | Frontend structure and styling            |
| Bootstrap           | 5.3.8      | Responsive UI components                  |
| GitHub              | -          | Version control and source code hosting   |
| Contabo VPS         | Ubuntu 22.04| Cloud hosting infrastructure              |
| vsFTPd              | 3.0.5      | FTP server for file transfers             |
| phpMyAdmin          | 5.2.1      | Database management GUI                   |

================================================================================
PAGE 2: GITHUB SETUP & VPS PROVISIONING
================================================================================

2. GITHUB REPOSITORY HOSTING

2.1 Repository Creation
A GitHub repository named "contact-manager" was created by Amine ELfakir to host 
the project source code. GitHub provides version control capabilities and serves 
as a central location for code storage and team collaboration.

Repository Structure:
contact-manager/
├── config.php          # Database configuration and session start
├── dashboard.php       # Main contact management interface (387 lines)
├── login.php           # User authentication page (92 lines)
├── register.php        # New user registration page (102 lines)
├── logout.php          # Session destruction handler (4 lines)
├── uploads/            # Directory for profile picture storage
├── css/
│   └── dashboard.css   # Custom styling
└── README.md           # Project documentation

2.2 Uploading Code to GitHub (Performed by Amine ELfakir)
The code was uploaded using Git commands:
> git init
> git add .
> git commit -m "Initial commit - Contact Manager application"
> git branch -M main
> git remote add origin https://github.com/contact-manager/contact-app.git
> git push -u origin main

2.3 Benefits of Version Control Used in This Project
- Tracking changes: Every modification by both team members is recorded
- Collaboration: Anweer and Amine can work simultaneously on different features
- Backup: Code is safely stored in the cloud
- Deployment: Easy to pull latest code to the Contabo server

--------------------------------------------------------------------------------
3. CONTABO VPS PROVISIONING (Performed by Anweer Anous)
--------------------------------------------------------------------------------

3.1 Selecting a VPS Plan
Contabo offers affordable VPS hosting with data centers in Europe (Germany, UK) 
and the US (St. Louis). The following configuration was selected:

| Component          | Minimum Required | Selected Configuration |
|--------------------|------------------|------------------------|
| vCPU               | 1 core           | 4 cores AMD EPYC       |
| RAM                | 2 GB             | 8 GB DDR4              |
| SSD Storage        | 30 GB            | 200 GB NVMe            |
| Bandwidth          | Unlimited        | 32 TB/month            |
| Monthly Cost       | ~€4.50           | €6.99                  |

3.2 Server Configuration Details
- Operating System: Ubuntu 22.04 LTS (Jammy Jellyfish)
- Location: Contabo Germany (EU data center for GDPR compliance)
- SSH Access: Enabled with root credentials
- Control Panel: Customer dashboard for management

3.3 Server Credentials Received from Contabo

| Item                        | Value                         |
|-----------------------------|-------------------------------|
| Server IP Address           | [Enter your VPS IP here]      |
| SSH Port                    | 22                            |
| Username                    | root                          |
| Initial Password            | [Your temporary password]     |
| VPS Control Panel           | https://my.contabo.com        |

3.4 Connecting via SSH (Anweer Anous)
Using terminal on Linux/macOS or PowerShell on Windows:
> ssh root@[your-server-ip]

Successful connection output:
Welcome to Ubuntu 22.04.5 LTS (GNU/Linux 5.15.0-91-generic x86_64)
Last login: [date] from [IP address]
root@contact-server:~#

================================================================================
PAGE 3: LAMP STACK INSTALLATION & DATABASE SETUP
================================================================================

4. LAMP STACK INSTALLATION AND CONFIGURATION

4.1 System Update
> apt update && apt upgrade -y

4.2 Apache2 Web Server Installation
> apt install apache2 -y

Verification: The Apache default page was accessible at http://[server-ip]

Enable Apache to start on boot:
> systemctl enable apache2
> systemctl start apache2
> systemctl status apache2

4.3 MySQL (MariaDB) Database Installation
> apt install mariadb-server mariadb-client -y

Secure the installation (Anweer Anous):
> mysql_secure_installation

Configuration choices made:
- Set root password: YES
- Remove anonymous users: YES
- Disallow root login remotely: YES
- Remove test database: YES
- Reload privilege tables: YES

4.4 PHP Installation
> apt install php8.1 php8.1-cli php8.1-common php8.1-curl php8.1-gd php8.1-mbstring php8.1-mysql php8.1-xml libapache2-mod-php8.1 -y

PHP extensions installed and their purposes:

| Extension        | Purpose for Contact Manager                |
|------------------|--------------------------------------------|
| php8.1-mysql     | Database connectivity for user/contact queries |
| php8.1-gd        | Image processing for profile picture uploads  |
| php8.1-mbstring  | Multi-byte string handling for names/emails   |
| php8.1-curl      | HTTP requests (future API integrations)       |
| php8.1-xml       | XML parsing for data exchange                 |

4.5 Verifying PHP Installation
> echo "<?php phpinfo(); ?>" > /var/www/html/info.php
Access http://[server-ip]/info.php - PHP information page displayed successfully.

4.6 FTP Server Installation (vsFTPd)
> apt install vsftpd -y

FTP Configuration:
> nano /etc/vsftpd.conf
Enabled: write_enable=YES, local_umask=022, anonymous_enable=NO
> systemctl restart vsftpd

4.7 phpMyAdmin Installation
> apt install phpmyadmin -y

During installation:
- Select: Apache as the web server (SPACEBAR to select, TAB to OK)
- Configure database with dbconfig-common: YES
- Set phpMyAdmin admin password: [Your chosen password]

Access phpMyAdmin: http://[server-ip]/phpmyadmin

--------------------------------------------------------------------------------
5. DATABASE CREATION (Performed by Anweer Anous)
--------------------------------------------------------------------------------

5.1 Creating the Application Database
> mysql -u root -p

SQL Commands:
CREATE DATABASE contact_app;
CREATE USER 'contact_user'@'localhost' IDENTIFIED BY 'SecurePass123!';
GRANT ALL PRIVILEGES ON contact_app.* TO 'contact_user'@'localhost';
FLUSH PRIVILEGES;
SHOW DATABASES;
EXIT;

5.2 Database Schema (Auto-created by Application via config.php)

Users Table Structure:
| Column      | Type              | Constraints                    |
|-------------|-------------------|--------------------------------|
| id          | INT AUTO_INCREMENT| PRIMARY KEY                    |
| username    | VARCHAR(50)       | UNIQUE NOT NULL                |
| password    | VARCHAR(255)      | NOT NULL (hashed with bcrypt)  |
| created_at  | TIMESTAMP         | DEFAULT CURRENT_TIMESTAMP      |

Contacts Table Structure:
| Column      | Type              | Constraints                    |
|-------------|-------------------|--------------------------------|
| id          | INT AUTO_INCREMENT| PRIMARY KEY                    |
| user_id     | INT               | FOREIGN KEY -> users(id)       |
| name        | VARCHAR(100)      | NOT NULL                       |
| email       | VARCHAR(100)      | NULL                           |
| phone       | VARCHAR(20)       | NOT NULL                       |
| picture     | VARCHAR(255)      | DEFAULT NULL                   |
| created_at  | TIMESTAMP         | DEFAULT CURRENT_TIMESTAMP      |

5.3 Database Verification
> USE contact_app;
> SHOW TABLES;
Output: users, contacts
> DESCRIBE users;
> DESCRIBE contacts;

--------------------------------------------------------------------------------
6. DEPLOYING THE CONTACT MANAGER APPLICATION (Performed by Amine ELfakir)
--------------------------------------------------------------------------------

6.1 Uploading Files to the Server - Git Clone method:
> cd /var/www/html
> git clone https://github.com/contact-manager/contact-app.git .

6.2 Configuring Database Connection
Edit config.php with the created database credentials:
<?php
$db_host = 'localhost';
$db_user = 'contact_user';
$db_pass = 'SecurePass123!';
$db_name = 'contact_app';
?>

6.3 Setting Proper Permissions
> mkdir -p /var/www/html/uploads
> chown -R www-data:www-data /var/www/html/
> chmod -R 755 /var/www/html/
> chmod -R 777 /var/www/html/uploads/

6.4 Configuring Apache Virtual Host
> nano /etc/apache2/sites-available/contact-manager.conf

Apache Configuration:
<VirtualHost *:80>
    ServerAdmin anweer.anous@example.com
    ServerName [your-server-ip]
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>

Enable the site:
> a2ensite contact-manager.conf
> a2dissite 000-default.conf
> systemctl reload apache2

6.5 Enabling Apache Mod Rewrite
> a2enmod rewrite
> systemctl restart apache2

================================================================================
PAGE 4: TESTING, RESULTS & CHALLENGES
================================================================================

7. TESTING AND VALIDATION (Both Team Members)

7.1 Accessing the Application
URL: http://[your-server-ip]
Result: Apache welcome page replaced by Contact Manager login page

7.2 Registration Test (Performed by Anweer Anous)
Test Data:
| Field            | Value            |
|------------------|------------------|
| Username         | anweer_anous     |
| Password         | Test@1234        |
| Confirm Password | Test@1234        |

Result: SUCCESS - "Registration successful! You can now login."

7.3 Login Test (Performed by Amine ELfakir)
Test Data:
| Field    | Value            |
|----------|------------------|
| Username | amine_elfakir    |
| Password | Amine@2026       |

Result: SUCCESS - Redirected to dashboard.php

7.4 Contact Management Tests

Add Contact Test (Amine ELfakir):
| Field   | Value                        |
|---------|------------------------------|
| Name    | Jean Dupont                  |
| Email   | jean.dupont@email.com        |
| Phone   | +33 6 12 34 56 78            |
| Picture | profile.jpg (uploaded)       |

Result: SUCCESS - Contact appeared with image

Edit Contact Test (Anweer Anous):
- Changed phone from "+33 6 12 34 56 78" to "+33 6 98 76 54 32"
Result: SUCCESS - Update reflected immediately

Delete Contact Test:
- Deleted test contact "Test Contact"
Result: SUCCESS - Removed from database and uploads folder

7.5 Search Functionality Test

| Search Term      | Expected Behavior                    | Result     |
|------------------|--------------------------------------|------------|
| "Jean"           | Contact "Jean Dupont" moves to top   | SUCCESS    |
| "dupont@email"   | Email match brings contact to top    | SUCCESS    |
| "06 12"          | Phone number partial match           | SUCCESS    |
| Empty search     | Alphabetical order by name           | SUCCESS    |

7.6 Security Testing Results

| Security Test      | Implementation                      | Result     |
|--------------------|-------------------------------------|------------|
| Password Storage   | password_hash() with bcrypt         | SECURE     |
| SQL Injection      | Prepared statements (bind_param)    | PROTECTED  |
| XSS Attacks        | htmlspecialchars() on output        | PROTECTED  |
| Session Hijacking  | Session_start() with validation     | SECURE     |
| File Upload        | Type validation (JPEG,PNG,GIF,WEBP) | SECURE     |

--------------------------------------------------------------------------------
8. FINAL RESULTS
--------------------------------------------------------------------------------

8.1 Successful Deployment
The Contact Manager application is accessible at:
URL: http://[your-server-ip]

8.2 Complete Functional Checklist

| Feature                         | Status     | Tested By          |
|---------------------------------|------------|--------------------|
| User Registration               | WORKING    | Anweer Anous       |
| User Login                      | WORKING    | Amine ELfakir      |
| Password Hashing (bcrypt)       | WORKING    | Both               |
| Session Management              | WORKING    | Both               |
| Add Contact with Picture        | WORKING    | Amine ELfakir      |
| Edit Contact                    | WORKING    | Anweer Anous       |
| Delete Contact                  | WORKING    | Both               |
| Profile Picture Upload          | WORKING    | Amine ELfakir      |
| Picture Deletion on Contact Delete| WORKING  | Anweer Anous       |
| Search with Relevance Reordering| WORKING    | Both               |
| Responsive Bootstrap Design     | WORKING    | Amine ELfakir      |
| Logout                          | WORKING    | Both               |

8.3 Server Service Status

| Service      | Version   | Status   | Port |
|--------------|-----------|----------|------|
| Apache2      | 2.4.52    | ACTIVE   | 80   |
| MySQL        | 10.6.12   | ACTIVE   | 3306 |
| PHP          | 8.1.2     | ACTIVE   | -    |
| vsFTPd       | 3.0.5     | ACTIVE   | 21   |
| SSH          | OpenSSH 8.9| ACTIVE  | 22   |

--------------------------------------------------------------------------------
9. CHALLENGES ENCOUNTERED AND SOLUTIONS
--------------------------------------------------------------------------------

Challenge 1: File Permission Errors for Uploads
Problem: Profile pictures would not display (broken image icon)
Root Cause: Uploads directory owned by root, Apache runs as www-data
Solution:
> chown -R www-data:www-data /var/www/html/uploads/
> chmod -R 755 /var/www/html/uploads/

Challenge 2: Missing PHP GD Extension
Problem: "Failed to upload picture" error message
Root Cause: GD library for image processing not installed
Solution:
> apt install php8.1-gd -y
> systemctl restart apache2

Challenge 3: MySQL Connection Refused
Problem: "Access denied for user 'contact_user'@'localhost'"
Root Cause: Database user privileges not properly granted
Solution:
GRANT ALL PRIVILEGES ON contact_app.* TO 'contact_user'@'localhost';
FLUSH PRIVILEGES;

Challenge 4: Git Push Rejected
Problem: "failed to push some refs" error
Root Cause: Remote repository had commits local did not have
Solution:
> git pull origin main --rebase
> git push origin main

Challenge 5: Modal Edit Form Not Submitting
Problem: Edit modal opened but "Update" button did nothing
Root Cause: JavaScript variable scope issues
Solution: Used json_encode() in PHP and proper data attributes

================================================================================
PAGE 5: CONCLUSION, APPENDICES & REFERENCES
================================================================================

10. CONCLUSION

10.1 Project Summary
This project successfully demonstrated the complete lifecycle of deploying a web 
application from development to production:

1. GitHub provided version control and code hosting for team collaboration
2. Contabo VPS delivered reliable cloud infrastructure (€6.99/month)
3. LAMP stack created a robust hosting environment for PHP applications
4. Additional services (FTP, phpMyAdmin) simplified file transfers and DB management
5. The Contact Manager application functions with all features operational

10.2 Learning Outcomes

| Skill                                      | Acquired By        |
|--------------------------------------------|--------------------|
| VPS provisioning and management            | Anweer Anous       |
| Linux command line administration          | Both               |
| LAMP stack installation                    | Anweer Anous       |
| Git version control workflow               | Amine ELfakir      |
| Apache virtual host configuration          | Anweer Anous       |
| Database design and MySQL management       | Both               |
| PHP debugging and error resolution         | Amine ELfakir      |
| Server security best practices             | Both               |
| FTP configuration and file transfer        | Amine ELfakir      |

10.3 Future Improvements
- Add HTTPS/SSL certificate (Let's Encrypt) for secure connections
- Implement password reset functionality via email
- Add contact export to CSV/PDF
- Create REST API for mobile app integration
- Implement two-factor authentication (2FA)

10.4 Final Statement
The application is now live and accessible from anywhere with an internet 
connection at http://[your-server-ip]. This deployment serves as a successful 
template for future web application projects.

--------------------------------------------------------------------------------
11. APPENDICES
--------------------------------------------------------------------------------

Appendix A: Complete File Listing with Line Counts

| File                   | Lines | Description                         |
|------------------------|-------|-------------------------------------|
| config.php             | 62    | Database connection & table creation|
| dashboard.php          | 387   | Main contact management interface   |
| login.php              | 92    | User authentication handler         |
| register.php           | 102   | New user registration               |
| logout.php             | 4     | Session destruction                 |
| css/dashboard.css      | 45    | Custom styling                      |

Appendix B: Server Commands Reference Sheet

# Apache Management
systemctl status apache2      # Check Apache status
systemctl restart apache2     # Restart Apache
systemctl stop apache2        # Stop Apache
systemctl start apache2       # Start Apache

# MySQL Management
systemctl status mysql        # Check MySQL status
mysql -u contact_user -p      # Connect to database
mysql -u root -p              # Connect as root

# View Error Logs
tail -f /var/log/apache2/error.log       # Apache errors
tail -f /var/log/apache2/access.log      # Apache access
tail -f /var/log/mysql/error.log         # MySQL errors

# File Operations
ls -la /var/www/html/                    # List files with permissions
chown -R www-data:www-data /path         # Change ownership
chmod -R 755 /path                       # Change permissions

# Git Operations
git status                               # Check changes
git add .                                # Stage changes
git commit -m "message"                  # Commit changes
git push origin main                     # Push to GitHub
git pull origin main                     # Pull from GitHub

Appendix C: Screenshots to Insert

Insert the following screenshots:
1. GitHub repository showing uploaded files
2. Contabo VPS control panel dashboard
3. SSH connection to the server
4. Apache default page after installation
5. phpMyAdmin interface with contact_app database
6. Registration page
7. Login page
8. Dashboard with contacts list
9. Add contact form with picture upload
10. Edit contact modal
11. Search results showing reordering
12. Application running on public IP

Appendix D: GitHub Repository Structure

https://github.com/contact-manager/contact-app
├── .git/
├── config.php
├── dashboard.php
├── login.php
├── register.php
├── logout.php
├── uploads/
├── css/
│   └── dashboard.css
└── README.md

Git Commands Used:
git init
git add .
git commit -m "Initial commit - Contact Manager"
git branch -M main
git remote add origin https://github.com/contact-manager/contact-app.git
git push -u origin main

Appendix E: Team Contribution Log

| Date          | Team Member     | Task Completed                      |
|---------------|-----------------|-------------------------------------|
| [Date]        | Anweer Anous    | Contabo VPS order and setup         |
| [Date]        | Anweer Anous    | LAMP stack installation             |
| [Date]        | Amine ELfakir   | GitHub repository creation          |
| [Date]        | Amine ELfakir   | Code upload to GitHub               |
| [Date]        | Anweer Anous    | MySQL database configuration        |
| [Date]        | Amine ELfakir   | File deployment to server           |
| [Date]        | Both            | Testing and debugging               |
| [Date]        | Both            | Report writing                      |

--------------------------------------------------------------------------------
12. REFERENCES
--------------------------------------------------------------------------------

1. Contabo Blog. (2024). Learn All Things Servers and Server Hosting.
   https://contabo.com/blog/

2. Contabo Blog. (2022). Install LAMP Stack with Cloud-Init.
   https://contabo.com/blog/install-lamp-stack-with-cloud-init/

3. Contabo Blog. (2024). VPS Archives - Performance and Reliability.
   https://contabo.com/blog/category/vps/

4. Contao Documentation. (2025). The Docker Devilbox.
   https://docs.contao.org/dev/getting-started/development-environment/

5. Medium. (2023). How To Install WordPress On Contabo VPS Without cPanel.
   https://medium.com/@howtoinstall/how-to-install-wordpress-on-contabo-vps

6. PHP Documentation. (2026). PHP: Hypertext Preprocessor.
   https://www.php.net/docs.php

7. MySQL Documentation. (2026). MySQL 8.0 Reference Manual.
   https://dev.mysql.com/doc/refman/8.0/en/

8. Apache Documentation. (2026). Apache HTTP Server Version 2.4.
   https://httpd.apache.org/docs/2.4/


================================================================================
                            END OF REPORT
================================================================================