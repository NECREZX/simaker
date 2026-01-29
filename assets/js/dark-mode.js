/**
 * Dark Mode Toggle
 */

document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const body = document.body;
    const icon = darkModeToggle?.querySelector('i');
    
    // Check saved dark mode preference
    const savedMode = localStorage.getItem('darkMode');
    if (savedMode === 'enabled') {
        body.classList.add('dark-mode');
        if (icon) {
            icon.classList.remove('bi-moon-stars');
            icon.classList.add('bi-sun');
        }
    }
    
    // Toggle dark mode
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
                if (icon) {
                    icon.classList.remove('bi-moon-stars');
                    icon.classList.add('bi-sun');
                }
            } else {
                localStorage.setItem('darkMode', 'disabled');
                if (icon) {
                    icon.classList.remove('bi-sun');
                    icon.classList.add('bi-moon-stars');
                }
            }
        });
    }
});
