# Database Configuration Guide

## Quick Setup Steps

### Method 1: Using Command Line (Recommended)

1. **Start MySQL/MariaDB:**
   ```bash
   # macOS (Homebrew)
   brew services start mysql
   
   # Linux
   sudo systemctl start mysql
   ```

2. **Import the database schema:**
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   
   Or if no password:
   ```bash
   mysql -u root < database/schema.sql
   ```

3. **Update database credentials** in `config/database.php`:
   ```php
   $this->host = 'localhost';
   $this->dbname = 'attendance_system';
   $this->username = 'root';
   $this->password = 'your_password_here';
   ```

### Method 2: Using PHP Setup Script

1. **Update database credentials** in `database/setup.php`:
   ```php
   $host = 'localhost';
   $username = 'root';
   $password = 'your_password_here';
   ```

2. **Run the setup script:**
   ```bash
   php database/setup.php
   ```
   
   Or access via browser:
   ```
   http://localhost/attendenceapp/database/setup.php
   ```

### Method 3: Manual Setup via MySQL Command Line

1. **Access MySQL:**
   ```bash
   mysql -u root -p
   ```

2. **Create database and import:**
   ```sql
   CREATE DATABASE IF NOT EXISTS attendance_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE attendance_system;
   SOURCE /path/to/attendenceapp/database/schema.sql;
   ```

3. **Verify tables were created:**
   ```sql
   SHOW TABLES;
   ```

4. **Check default admin account:**
   ```sql
   SELECT username, email, role FROM users WHERE role = 'administrator';
   ```

## Default Login Credentials

After setup, you can login with:
- **Username:** `admin`
- **Password:** `admin123`

⚠️ **IMPORTANT:** Change this password immediately after first login!

## Creating Additional Users

### Create a Professor Account

```sql
INSERT INTO users (username, email, password_hash, first_name, last_name, role) 
VALUES ('prof1', 'prof1@university.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'professor');
```

### Create a Student Account

```sql
INSERT INTO users (username, email, password_hash, first_name, last_name, role) 
VALUES ('student1', 'student1@university.dz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', 'student');
```

**Note:** The password hash above is for `admin123`. To create a new password hash, use:
```php
<?php
echo password_hash('your_password', PASSWORD_DEFAULT);
?>
```

## Database Structure

The database includes the following tables:

1. **users** - Stores students, professors, and administrators
2. **courses** - Course information
3. **groups** - Student groups within courses
4. **enrollments** - Student course enrollments
5. **attendance_sessions** - Attendance session records
6. **attendance_records** - Individual attendance records
7. **justifications** - Absence justification requests

## Troubleshooting

### "Access denied" Error
- Check MySQL username and password in `config/database.php`
- Verify MySQL user has proper permissions:
  ```sql
  GRANT ALL PRIVILEGES ON attendance_system.* TO 'your_user'@'localhost';
  FLUSH PRIVILEGES;
  ```

### "Database doesn't exist" Error
- Run the schema.sql file to create the database
- Or manually create: `CREATE DATABASE attendance_system;`

### "Table already exists" Error
- This is normal if you're re-running the setup
- To start fresh, drop the database first:
  ```sql
  DROP DATABASE IF EXISTS attendance_system;
  ```

### Connection Refused
- Ensure MySQL/MariaDB service is running
- Check if MySQL is listening on the correct port (default: 3306)
- Verify firewall settings

## Security Recommendations

1. **Create a dedicated database user** instead of using root:
   ```sql
   CREATE USER 'attendance_user'@'localhost' IDENTIFIED BY 'strong_password';
   GRANT ALL PRIVILEGES ON attendance_system.* TO 'attendance_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

2. **Update `config/database.php`** with the new credentials

3. **Change default admin password** immediately

4. **Restrict file permissions:**
   ```bash
   chmod 600 config/database.php
   ```

5. **Use environment variables** for sensitive data in production

## Testing the Connection

Create a test file `test_db.php`:

```php
<?php
require_once 'config/database.php';
$db = $database->getConnection();
if ($db) {
    echo "Database connection successful!";
} else {
    echo "Database connection failed!";
}
?>
```

Access it via browser to verify the connection works.

