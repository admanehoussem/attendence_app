# Quick Start Guide

## Database Setup (5 Minutes)

### Step 1: Start MySQL/MariaDB
```bash
# macOS
brew services start mysql

# Linux
sudo systemctl start mysql
```

### Step 2: Import Database
```bash
mysql -u root -p < database/schema.sql
```

Or use the setup script:
```bash
php database/setup.php
```

### Step 3: Configure Database
Edit `config/database.php`:
```php
$this->username = 'root';
$this->password = 'your_password';
```

### Step 4: Start Web Server

**Option A: PHP Built-in Server**
```bash
cd /path/to/attendenceapp
php -S localhost:8000
```

**Option B: XAMPP/WAMP/MAMP**
- Place project in `htdocs` folder
- Access via `http://localhost/attendenceapp/`

### Step 5: Login
- URL: `http://localhost:8000/login.php` (or your server URL)
- Username: `admin`
- Password: `admin123`

## First Steps After Login

1. **As Administrator:**
   - Go to "Student Management"
   - Add students manually or import from Excel
   - Create professor accounts (via database or admin panel)

2. **As Professor:**
   - View your courses
   - Click "Create New Session" to start attendance
   - Mark attendance for students
   - View attendance summaries

3. **As Student:**
   - View enrolled courses
   - Check your attendance status
   - Submit justifications for absences

## Creating Test Data

### Add a Course (via Database)
```sql
USE attendance_system;
INSERT INTO courses (code, name, professor_id) 
VALUES ('CS101', 'Introduction to Programming', 1);
```

### Enroll a Student
```sql
INSERT INTO enrollments (student_id, course_id) 
VALUES (2, 1);
```

## Troubleshooting

**Can't connect to database?**
- Check MySQL is running: `mysql -u root -p`
- Verify credentials in `config/database.php`

**File upload not working?**
- Create directory: `mkdir -p uploads/justifications`
- Set permissions: `chmod 755 uploads/justifications`

**Sessions not loading?**
- Check browser console for errors
- Verify API endpoints are accessible
- Check database has data

## Next Steps

- Read `README.md` for full documentation
- Read `DATABASE_SETUP.md` for detailed database setup
- Customize the system for your needs

