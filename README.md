# LeaseLink

LeaseLink is a comprehensive property management web application designed to connect landlords and clients in a simple and user-friendly way. 
It provides a complete platform for property listings, bookings, viewing requests, and administrative management.

---

## Overview

LeaseLink provides a full-featured property rental management system with:

* **User Management**: Secure registration and login for clients, landlords, and administrators
* **Property Listings**: Display and manage property listings with multiple images
* **Property Approval Workflow**: Admin approval required before properties are visible to the public
* **Viewing Requests**: Clients can request property viewings, landlords can approve/deny
* **Booking System**: Clients can book properties, landlords can approve/reject bookings
* **Admin Dashboard**: Complete administrative control over users, properties, and system statistics
* **Responsive Design**: Modern, mobile-friendly interface
* **Database Integration**: MySQL database with PHP backend

This version was built and tested locally using XAMPP.

---

## Requirements

To run the project, you need:

* XAMPP (Apache and MySQL)
* A modern web browser such as Chrome, Firefox, Safari, or Edge

---

## Getting Started

Run this link on your browser:   http://169.239.251.102:341/~ajak.panchol/uploads/LeaseLink_/index.html

---

## Testing

You can use the sample accounts included in the database for testing:

**Landlord Account**
- Email: `landlord@example.com`
- Password: `password123`
- Features: Add properties, manage viewing requests, approve/reject bookings

**Client Account**
- Email: `client@example.com`
- Password: `password123`
- Features: Browse properties, request viewings, book properties

**Admin Account**
- Email: `admin@example.com`
- Password: `password123`
- Features: Manage all users, approve/deactivate properties, view system statistics

### Test Features

* **Homepage**: Browse available properties
* **Property Listings**: View all approved properties
* **Property Details**: View detailed property information with image gallery
* **User Registration**: Create new client or landlord accounts
* **Property Management**: Landlords can add properties (requires admin approval)
* **Viewing Requests**: Clients can request property viewings
* **Booking System**: Clients can book properties, landlords can manage bookings
* **Admin Dashboard**: Complete administrative control panel

---

## Troubleshooting

If something does not work as expected:

* **Database Connection Issues**:
  - Make sure Apache and MySQL are running in XAMPP
  - Check database connection settings in `backend/connect.php` (default port: 3307)
  - Verify database name is `leaselink_db`
  - Ensure you imported `leaselink_migration.sql` (not the old `leaselink.sql`)

* **Properties Not Showing**:
  - Properties need admin approval before appearing in public listings
  - Check property status in admin dashboard
  - Verify `get_properties.php` only shows properties with status `available`

* **Image Upload Issues**:
  - Ensure `assets/property_images/` directory exists and has write permissions
  - Check file size limits (max 5MB per image)
  - Verify image formats (JPG, JPEG, PNG, GIF only)

* **Authentication Issues**:
  - Clear browser localStorage if login problems persist
  - Verify user accounts exist in database
  - Check `authGuard.js` is loaded on all pages

* **General Issues**:
  - Check browser console for JavaScript errors
  - Review XAMPP error logs
  - Verify all PHP files are accessible
  - Ensure file paths are correct (especially image paths)

For more detailed troubleshooting, see `SETUP_GUIDE.md`.

---

## Project Structure

```
LeaseLink/
├── assets/
│   ├── property_images/     # Uploaded property images
│   ├── aboutus.webp
│   └── perfecthome.webp
├── backend/
│   ├── connect.php          # Database connection
│   ├── login.php            # User authentication
│   ├── register.php         # User registration
│   ├── get_properties.php   # Fetch all approved properties
│   ├── get_property.php     # Fetch single property details
│   ├── add_property.php     # Add new property (landlord)
│   ├── book_property.php    # Create booking (client)
│   ├── request_tour.php     # Create viewing request (client)
│   ├── update_booking_status.php      # Update booking status (landlord)
│   ├── update_view_request_status.php # Update viewing request status (landlord)
│   ├── get_client_bookings.php        # Get client bookings
│   ├── get_client_view_requests.php   # Get client viewing requests
│   ├── get_landlord_properties.php    # Get landlord properties
│   ├── get_landlord_bookings.php      # Get landlord bookings
│   ├── get_landlord_view_requests.php # Get landlord viewing requests
│   ├── get_admin_stats.php            # Get admin statistics
│   ├── get_all_users.php              # Get all users (admin)
│   ├── get_all_properties.php         # Get all properties (admin)
│   ├── update_user.php                # Update user (admin)
│   ├── delete_user.php                 # Delete user (admin)
│   └── update_property_status.php      # Update property status (admin)
├── css/
│   ├── global.css           # Global styles
│   ├── login.css            # Login/register styles
│   ├── properties.css       # Property listing styles
│   └── utils.css            # Utility styles
├── database/
│   ├── leaselink.sql        # Original database schema
│   ├── leaselink_migration.sql # Latest database schema (use this)
│   └── leaselink_schema.puml  # UML class diagram (PlantUML)
├── js/
│   └── authGuard.js         # Route protection and authentication
├── index.html               # Homepage
├── login.html               # Login/Register page
├── properties.html          # Property listings page
├── property-details.html    # Property details page
├── add-property.html        # Add property form (landlord)
├── client-dashboard.html    # Client dashboard
├── landlord-dashboard.html  # Landlord dashboard
├── admin-dashboard.html     # Admin dashboard
├── forgot-password.html     # Password reset page
├── README.md                # This file
└── SETUP_GUIDE.md           # Detailed setup instructions
```

---

## Key Features

### User Roles

**Client (Tenant)**
- Browse approved properties
- Request property viewings
- Book properties
- View booking history and viewing requests

**Landlord**
- Add new properties (requires admin approval)
- Manage viewing requests (approve/deny)
- Manage bookings (approve/reject)
- View all their listed properties

**Admin**
- View system statistics (users, properties, bookings, viewings)
- Manage all users (view, edit, delete)
- Approve/deactivate properties
- Full system oversight

### Property Workflow

1. **Landlord adds property** → Status: `pending`
2. **Admin approves property** → Status: `available` (visible to public)
3. **Client views property** → Can request viewing or book directly
4. **Landlord manages requests** → Approves/denies viewing requests and bookings

## Future Improvements

Planned updates include:

* Property search and filtering
* Advanced property filtering (price range, location, amenities)
* Email notifications
* Payment integration
* Review and rating system enhancements
* Mobile app development

---

##Link To The Slides: prsenattion slide
https://www.canva.com/design/DAG6NRfMNdE/bCRnTUEVRactvkf3pzNTtg/edit?utm_content=DAG6NRfMNdE&utm_campaign=designshare&utm_medium=link2&utm_source=sharebutton


## Author and Support

Developed by the LeaseLink Team.
For questions or contributions, please open an issue or submit a pull request on GitHub.


