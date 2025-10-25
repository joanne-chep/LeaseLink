# LeaseLink Setup Guide

## Prerequisites
- XAMPP installed and running
- Web browser (Chrome, Firefox, Safari, etc.)

## Setup Instructions

### 1. Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services
3. Ensure both services are running (green status)

### 2. Database Setup
1. Open your web browser and go to: `http://localhost/phpmyadmin`
2. Click on "Import" tab
3. Click "Choose File" and select `database/leaselink.sql`
4. Click "Go" to import the database
5. Verify that `leaselink_db` database is created with the following tables:
   - `landlords`
   - `clients`
   - `properties`

### 3. Project Setup
1. Ensure your project is in the correct directory: `C:\xampp\htdocs\LeaseLink`
2. The project structure should be:
   ```
   LeaseLink/
   ├── assets/
   │   ├── aboutus.webp
   │   └── perfecthome.webp
   ├── backend/
   │   ├── connect.php
   │   ├── get_properties.php
   │   ├── get_property.php
   │   ├── login.php
   │   └── register.php
   ├── css/
   │   ├── global.css
   │   ├── login.css
   │   ├── properties.css
   │   └── utils.css
   ├── database/
   │   └── leaselink.sql
   ├── forgot-password.html
   ├── index.html
   ├── login.html
   ├── properties.html
   ├── property-details.html
   ├── SETUP_GUIDE.md
   └── test_database.php
   ```

### 4. Access the Application
1. Open your web browser
2. Navigate to: `http://localhost/LeaseLink`
3. You should see the LeaseLink homepage

## Testing the Application

### Test User Accounts
The database includes sample accounts for testing:

**Landlord Account:**
- Email: `landlord@example.com`
- Password: `password123`

**Client Account:**
- Email: `client@example.com`
- Password: `password123`

### Test Features
1. **Homepage**: Navigate to `http://localhost/LeaseLink`
2. **Properties**: Click "Properties" to view property listings
3. **Property Details**: Click "View Details" on any property
4. **Login/Register**: Click "Login/Register" to test authentication
5. **Registration**: Create new accounts with different roles

## Troubleshooting

### Common Issues

#### 1. Database Connection Error
- **Error**: "Connection failed"
- **Solution**: 
  - Ensure MySQL is running in XAMPP
  - Check database credentials in `backend/connect.php`
  - Verify database `leaselink_db` exists

#### 2. Properties Not Loading
- **Error**: Properties page shows loading spinner indefinitely
- **Solution**:
  - Check if database has data in `properties` table
  - Verify `backend/get_properties.php` is accessible
  - Check browser console for JavaScript errors

#### 3. Login Not Working
- **Error**: "Invalid password" or "User not found"
- **Solution**:
  - Use the test accounts provided above
  - Ensure database was imported correctly
  - Check if password hashing is working

#### 4. Property Details Not Loading
- **Error**: Property details page shows error
- **Solution**:
  - Verify property ID in URL
  - Check if `backend/get_property.php` is working
  - Ensure database has property data

### Database Verification
To verify your database setup:
1. Go to `http://localhost/phpmyadmin`
2. Select `leaselink_db` database
3. Check these tables have data:
   - `landlords` (should have 1 record)
   - `clients` (should have 1 record)
   - `properties` (should have 3 records)

### File Permissions
If you encounter permission issues:
1. Ensure XAMPP has read/write access to the project folder
2. Check that PHP files are executable
3. Verify file paths are correct

## Development Notes

### Current Features (Sprint 1)
- ✅ User registration and login
- ✅ Property listings display
- ✅ Property details view
- ✅ Responsive design
- ✅ Database integration

### Future Features (Planned)
- Property search and filtering
- User dashboard
- Property management for landlords
- Booking system
- Image upload functionality

## Support
If you encounter issues:
1. Check XAMPP error logs
2. Verify all services are running
3. Check browser console for errors
4. Ensure all files are in the correct locations
