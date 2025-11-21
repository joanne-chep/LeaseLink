// js/authGuard.js
// This script automatically runs route guard checks when included on any page

(function() {
    'use strict';
    
    function checkAuthAndRedirect() {
        const userType = localStorage.getItem('userType');
        const isLoggedIn = !!localStorage.getItem('userName'); // Check if userName exists
        const currentPage = window.location.pathname.split('/').pop(); // Get current page filename

        // Define page access rules
        const accessRules = {
            'index.html': ['client', 'unauthenticated'],
            'properties.html': ['client', 'unauthenticated'],
            'property-details.html': ['client', 'landlord', 'admin', 'unauthenticated'], // All can view property details
            'client-dashboard.html': ['client'],
            'landlord-dashboard.html': ['landlord'],
            'admin-dashboard.html': ['admin'],
            'add-property.html': ['landlord'],
            'login.html': ['unauthenticated'],
            'forgot-password.html': ['unauthenticated']
        };

        const allowedRolesForPage = accessRules[currentPage];

        // If page is not in access rules, assume it's public (or an error page)
        if (!allowedRolesForPage) {
            return; 
        }

        // Handle unauthenticated users trying to access restricted pages
        if (!isLoggedIn) {
            if (!allowedRolesForPage.includes('unauthenticated')) {
                window.location.href = 'login.html'; // Redirect to login if not allowed
                return;
            }
        } else {
            // Handle authenticated users
            if (allowedRolesForPage.includes('unauthenticated')) {
                // Authenticated user trying to access login/register pages - redirect them
                if (currentPage === 'login.html' || currentPage === 'forgot-password.html') {
                    let redirectUrl = 'index.html'; // Default to home

                    if (userType === 'client') {
                        redirectUrl = 'client-dashboard.html';
                    } else if (userType === 'landlord') {
                        redirectUrl = 'landlord-dashboard.html';
                    } else if (userType === 'admin') {
                        redirectUrl = 'admin-dashboard.html';
                    }

                    window.location.href = redirectUrl;
                    return;
                }
                // For index.html and properties.html, allow clients to access them
                // Only redirect landlords and admins away from these pages
                if ((currentPage === 'index.html' || currentPage === 'properties.html') && (userType === 'landlord' || userType === 'admin')) {
                    let redirectUrl = 'index.html'; // Default to home

                    if (userType === 'landlord') {
                        redirectUrl = 'landlord-dashboard.html';
                    } else if (userType === 'admin') {
                        redirectUrl = 'admin-dashboard.html';
                    }

                    window.location.href = redirectUrl;
                    return;
                }
            }

            // Authenticated user trying to access a page they are not allowed to based on userType
            if (!allowedRolesForPage.includes(userType)) {
                // Redirect to their appropriate dashboard
                let redirectUrl = 'index.html'; // Fallback
                if (userType === 'client') {
                    redirectUrl = 'client-dashboard.html';
                } else if (userType === 'landlord') {
                    redirectUrl = 'landlord-dashboard.html';
                } else if (userType === 'admin') {
                    redirectUrl = 'admin-dashboard.html';
                }
                window.location.href = redirectUrl;
                return;
            }
        }
    }

    // Automatically execute the route guard check when script loads
    // This runs immediately, before DOM is ready, to prevent unauthorized content from flashing
    checkAuthAndRedirect();
})();
