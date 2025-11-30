# LeaseLink Setup Guide

## Prerequisites
- XAMPP installed and running
- Web browser

## Setup Instructions

### 1. Start XAMPP Services
- Open XAMPP Control Panel
- Start Apache and MySQL services

### 2. Database Setup
1. Go to `http://localhost/phpmyadmin`
2. Click "Import" tab
3. Select `database/leaselink_migration.sql` (use migration file, not leaselink.sql)
4. Click "Go" to import
5. Verify `leaselink_db` database is created with tables: users, properties, view_requests, bookings, reviews, property_images, amenities, property_amenities

### 3. Database Connection
- Open `backend/connect.php`
- Verify settings match your XAMPP MySQL configuration (default: localhost:3307, user: root, password: empty)
- Adjust port if needed (XAMPP often uses 3307, standard MySQL uses 3306)

### 4. Project Setup
- Ensure project is in `C:\xampp\htdocs\LeaseLink`
- Create `assets/property_images/` directory with write permissions

### 5. Access Application
- Navigate to `http://localhost/LeaseLink`
- Homepage should load

## Testing

### Test Accounts
- **Landlord**: `landlord@example.com` / `password123`
- **Client**: `client@example.com` / `password123`
- **Admin**: `admin@example.com` / `password123`

### Key Features to Test
- Browse properties (only approved properties are visible)
- Register new accounts (client or landlord)
- Add property as landlord (requires admin approval)
- Request viewing or book property as client
- Approve properties as admin (Admin Dashboard → Property Moderation)
- Manage viewing requests and bookings as landlord

## Troubleshooting

### Database Connection Error
- Ensure MySQL is running in XAMPP
- Check credentials in `backend/connect.php`
- Verify `leaselink_db` database exists

### Properties Not Loading
- Properties must have status `available` to be visible (new properties are `pending`)
- Login as admin and approve pending properties
- Check browser console for errors

### Login Not Working
- Use test accounts provided above
- Verify database was imported correctly

### Image Upload Not Working
- Ensure `assets/property_images/` directory exists with write permissions
- File size limit: 5MB per image
- Allowed formats: JPG, JPEG, PNG, GIF

### Property Approval
- New properties start as `pending` and require admin approval
- Login as admin → Admin Dashboard → Property Moderation → Approve

### Database Verification
- Go to `http://localhost/phpmyadmin`
- Select `leaselink_db` database
- Verify tables exist: users (3 records), properties (3 records), property_images, amenities, view_requests, bookings, reviews
- If you see `landlords` or `clients` tables, you imported the wrong SQL file - use `leaselink_migration.sql`

### File Permissions
- Ensure XAMPP has read/write access to project folder
- `assets/property_images/` must have write permissions
- Windows: Right-click folder → Properties → Security → Enable "Write"
- Linux/Mac: `chmod 755 assets/property_images/` or `chmod 777 assets/property_images/`

## Property Approval Workflow

1. Landlord adds property → Status: `pending`
2. Property not visible → Only admins can see pending properties
3. Admin approves property → Status: `available`
4. Property becomes visible → Appears in public listings
5. Clients can interact → Can request viewings or book

Note: New properties require admin approval before appearing in listings.

## Support

- Check XAMPP error logs: `C:\xampp\apache\logs\error.log` and `C:\xampp\mysql\data\*.err`
- Verify Apache and MySQL services are running
- Check browser console for JavaScript errors (F12)
- Verify database connection in `backend/connect.php`
- Ensure `leaselink_migration.sql` was imported (not `leaselink.sql`)
- Verify `assets/property_images/` directory has write permissions

For more details, see `README.md`.
