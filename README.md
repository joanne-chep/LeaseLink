# LeaseLink

LeaseLink is a local property management web application designed to connect landlords and clients in a simple and user-friendly way. 
It allows users to register, log in, and view available property listings with detailed information.

---

## Overview

LeaseLink provides a basic setup for property rental management with features such as:

* Secure user registration and login
* Display of property listings
* Detailed property view
* Responsive front-end design
* Database integration using MySQL and PHP

This version was built and tested locally using XAMPP.

---

## Requirements

To run the project, you need:

* XAMPP (Apache and MySQL)
* A modern web browser such as Chrome, Firefox, Safari, or Edge

---

## Getting Started

1. Start XAMPP and make sure both Apache and MySQL are running.
2. Open phpMyAdmin by going to [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3. Create a new database named leaselink_db and import the file located in the database folder called leaselink.sql.
4. Move the LeaseLink project folder into the XAMPP htdocs directory.
5. Open your browser and go to [http://localhost/LeaseLink](http://localhost/LeaseLink) to access the application.

---

## Testing

You can use the sample accounts included in the database for testing.

Landlord Account
Email: [landlord@example.com]
Password: password123

Client Account
Email: [client@example.com]
Password: password123

You can test the homepage, property listings, property details, and the login or registration functions.

---

## Troubleshooting

If something does not work as expected:

* Make sure Apache and MySQL are running in XAMPP.
* Check that the database name and connection details in backend/connect.php are correct.
* Confirm that the database contains the required tables and sample data.
* Review any error messages in the browser console or XAMPP logs.

---

## Project Structure

Main files and folders include:

* backend (contains PHP files for database and user operations)
* database (contains the leaselink.sql file)
* index.html, listings.html, login.html, property-details.html, and style.css

---

## Future Improvements

Planned updates include:

* Property search and filtering
* User dashboards
* Booking and management systems
* Image uploads for properties

---

## Author and Support

Developed by the LeaseLink Team.
For questions or contributions, please open an issue or submit a pull request on GitHub.


