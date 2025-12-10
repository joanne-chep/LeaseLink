
function performLogout() {
    fetch('backend/logout.php', { method: 'POST' })
        .then(() => {
            // Clear all client-side storage
            localStorage.clear();
            sessionStorage.clear();
            
            // Clear any cached data
            if ('caches' in window) {
                caches.keys().then(names => {
                    names.forEach(name => caches.delete(name));
                });
            }
            
            // Use replace to prevent back button from returning to logged-in page
            window.location.replace('index.html');
        })
        .catch(error => {
            console.error('Logout error:', error);
            // Even if logout fails, clear client-side state and redirect
            localStorage.clear();
            sessionStorage.clear();
            window.location.replace('index.html');
        });
}

