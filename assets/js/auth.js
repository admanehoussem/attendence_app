/**
 * Authentication JavaScript
 */

$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        const username = $('#username').val();
        const password = $('#password').val();
        
        if (!username || !password) {
            showError('loginError', 'Please enter both username and password');
            return;
        }
        
        apiCall('auth.php?action=login', 'POST', {
            username: username,
            password: password
        })
        .then(data => {
            if (data.success) {
                window.location.href = 'index.php';
            } else {
                showError('loginError', data.message || 'Login failed');
            }
        })
        .catch(error => {
            showError('loginError', error.message || 'Login failed');
        });
    });
});

