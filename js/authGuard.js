
(function() {
    'use strict';
    
    
    window.addEventListener('pageshow', function(event) {
        if (event.persisted || performance.getEntriesByType('navigation')[0]?.type === 'back_forward') {
            // Page was loaded from cache
            window.location.reload();
        }
    });
    
    /
    window.addEventListener('load', function() {
        if (performance.navigation.type === 2) {
            
            window.location.reload();
        }
    });
    
    
    async function checkAuthAndRedirect() {
        const currentPage = window.location.pathname.split('/').pop();
        const accessRules = {
            'index.html': ['client', 'unauthenticated'],
            'properties.html': ['client', 'unauthenticated'],
            'property-details.html': ['client', 'landlord', 'admin', 'unauthenticated'],
            'client-dashboard.html': ['client'],
            'landlord-dashboard.html': ['landlord'],
            'admin-dashboard.html': ['admin'],
            'add-property.html': ['landlord'],
            'landlord-profile.html': ['landlord'],
            'login.html': ['unauthenticated'],
            'forgot-password.html': ['unauthenticated']
        };

        const allowedRolesForPage = accessRules[currentPage];
        if (!allowedRolesForPage) {
            return; 
        }

       
        let sessionData = { isLoggedIn: false, userType: null };
        try {
            
            const response = await fetch('backend/get_session_info.php');
            if (response.ok) {
                sessionData = await response.json();
            }
        } catch (error) {
            console.error('Error fetching session info:', error);
        }

        const isLoggedIn = sessionData.isLoggedIn;
        const userType = sessionData.userType;

        // 1. Handle unauthenticated users trying to access restricted pages
        if (!isLoggedIn) {
            if (!allowedRolesForPage.includes('unauthenticated')) {
                window.location.href = 'login.html'; 
                return;
            }
        } else {
            // 2. Handle authenticated users trying to access login/register pages
            if (allowedRolesForPage.includes('unauthenticated')) {
                if (currentPage === 'login.html' || currentPage === 'forgot-password.html') {
                    let redirectUrl = 'index.html';
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
                // Redirect landlords/admins away from public home/properties pages
                if ((currentPage === 'index.html' || currentPage === 'properties.html') && (userType === 'landlord' || userType === 'admin')) {
                    let redirectUrl = userType === 'landlord' ? 'landlord-dashboard.html' : 'admin-dashboard.html';
                    window.location.href = redirectUrl;
                    return;
                }
            }

            // 3. Authenticated user trying to access a page they are not allowed to
            if (!allowedRolesForPage.includes(userType)) {
                let redirectUrl = 'index.html'; 
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

    // Call the async route guard check immediately
    checkAuthAndRedirect();
    
    // Recheck when page becomes visible (handles tab switching and back button)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            checkAuthAndRedirect();
        }
    });
})();