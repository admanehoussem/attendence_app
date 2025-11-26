# Student Attendance Management System

A web-based attendance management system for Algiers University, built with PHP, MySQL/MariaDB, and jQuery.

## Features

### For Professors
- View list of courses and sessions
- Create and manage attendance sessions
- Mark student attendance (present, absent, late, excused)
- Track participation scores and behavior notes
- View attendance summaries per course/group

### For Students
- View enrolled courses
- Check attendance status per course
- Submit absence justifications with file uploads
- View attendance statistics

### For Administrators
- View system-wide statistics and charts
- Manage student accounts (add, remove)
- Import/export student lists in Excel format
- Monitor overall attendance rates

## Technologies Used

- **Frontend:** HTML5, CSS3, jQuery, Chart.js
- **Backend:** PHP 7.4+
- **Database:** MySQL/MariaDB
- **Design:** Mobile-first responsive design

## Database Configuration Steps

### Step 1: Install MySQL/MariaDB

**On macOS (using Homebrew):**
```bash
brew install mysql
# or
brew install mariadb
```

**On Linux (Ubuntu/Debian):**
```bash
sudo apt-get update
sudo apt-get install mysql-server
# or
sudo apt-get install mariadb-server
```

**On Windows:**
Download and install from:
- MySQL: https://dev.mysql.com/downloads/mysql/
- MariaDB: https://mariadb.org/download/

### Step 2: Start MySQL/MariaDB Service

**On macOS:**
```bash
brew services start mysql
# or
brew services start mariadb
```

**On Linux:**
```bash
sudo systemctl start mysql
# or
sudo systemctl start mariadb
```

**On Windows:**
Start MySQL/MariaDB service from Services panel or use MySQL Workbench.

### Step 3: Access MySQL/MariaDB

Open terminal/command prompt and run:
```bash
mysql -u root -p
```

Enter your root password when prompted. If you haven't set a password, you might be able to access without `-p`:
```bash
mysql -u root
```

### Step 4: Create Database and Import Schema

1. While in MySQL/MariaDB command line, run:
```sql
source /path/to/attendenceapp/database/schema.sql;
```

Or manually:
```sql
CREATE DATABASE IF NOT EXISTS attendance_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE attendance_system;
```

2. Then copy and paste the contents of `database/schema.sql` file, or import it:
```bash
mysql -u root -p attendance_system < database/schema.sql
```

### Step 5: Configure Database Connection

Edit the file `config/database.php` and update these values:

```php
$this->host = 'localhost';        // Database host (usually 'localhost')
$this->dbname = 'attendance_system';  // Database name
$this->username = 'root';          // Your MySQL username
$this->password = '';              // Your MySQL password
```

### Step 6: Create Database User (Optional but Recommended)

For better security, create a dedicated database user:

```sql
CREATE USER 'attendance_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON attendance_system.* TO 'attendance_user'@'localhost';
FLUSH PRIVILEGES;
```

Then update `config/database.php`:
```php
$this->username = 'attendance_user';
$this->password = 'your_secure_password';
```

### Step 7: Set Up Web Server

**Using XAMPP/WAMP/MAMP:**
1. Place the project folder in the web server directory:
   - XAMPP: `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/htdocs/` (macOS)
   - WAMP: `C:\wamp64\www\`
   - MAMP: `/Applications/MAMP/htdocs/`

2. Access via: `http://localhost/attendenceapp/`

**Using PHP Built-in Server:**
```bash
cd /path/to/attendenceapp
php -S localhost:8000
```

Access via: `http://localhost:8000/`

### Step 8: Set File Permissions

Create the uploads directory and set permissions:

```bash
mkdir -p uploads/justifications
chmod 755 uploads/justifications
```

### Step 9: Default Login Credentials

After importing the schema, you can login with:
- **Username:** admin
- **Password:** admin123

**Important:** Change the default password after first login!

## Project Structure

```
attendenceapp/
├── api/                    # API endpoints
│   ├── auth.php
│   ├── courses.php
│   ├── sessions.php
│   ├── attendance.php
│   ├── justifications.php
│   ├── students.php
│   ├── statistics.php
│   ├── import_export.php
│   └── upload.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       ├── common.js
│       ├── auth.js
│       ├── professor.js
│       ├── professor-session.js
│       ├── professor-summary.js
│       ├── student.js
│       ├── student-attendance.js
│       ├── admin-statistics.js
│       └── admin-students.js
├── config/
│   ├── config.php
│   └── database.php
├── database/
│   └── schema.sql
├── professor/
│   ├── home.php
│   ├── session.php
│   └── summary.php
├── student/
│   ├── home.php
│   └── attendance.php
├── admin/
│   ├── home.php
│   ├── statistics.php
│   └── students.php
├── uploads/
│   └── justifications/     # Created automatically
├── index.php
├── login.php
├── logout.php
└── README.md
```

## Troubleshooting

### Database Connection Error
- Verify MySQL/MariaDB is running
- Check database credentials in `config/database.php`
- Ensure database `attendance_system` exists
- Check user permissions

### File Upload Issues
- Ensure `uploads/justifications/` directory exists and is writable
- Check PHP `upload_max_filesize` and `post_max_size` settings

### Session Issues
- Ensure PHP sessions are enabled
- Check `session.save_path` in php.ini

## Development Notes

- The system uses PDO for database operations with prepared statements for security
- All user inputs are sanitized
- Password hashing uses PHP's `password_hash()` function
- File uploads are validated for type and size
- The design is mobile-first and responsive

## License

This project is created for educational purposes as part of Advanced Web Programming course.

# attendence_app
