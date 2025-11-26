/**
 * Common JavaScript Functions
 */

// Compute API base dynamically so pages in root and subfolders work.
// Pages under `admin/`, `professor/`, `student/` use '../api/',
// root pages (e.g. `login.php`, `index.php`) use 'api/'.
let API_BASE = 'api/';
const _path = window.location.pathname || '';
if (_path.includes('/admin/') || _path.includes('/professor/') || _path.includes('/student/') || _path.includes('/api/')) {
    API_BASE = '../api/';
}

// API Helper Functions
function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    
    return fetch(API_BASE + endpoint, options)
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Request failed');
                });
            }
            return response.json();
        });
}

// Show/Hide Messages
function showError(element, message) {
    const errorEl = typeof element === 'string' ? document.getElementById(element) : element;
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.style.display = 'block';
        setTimeout(() => {
            errorEl.style.display = 'none';
        }, 5000);
    }
}

function showSuccess(element, message) {
    const successEl = typeof element === 'string' ? document.getElementById(element) : element;
    if (successEl) {
        successEl.textContent = message;
        successEl.style.display = 'block';
        setTimeout(() => {
            successEl.style.display = 'none';
        }, 5000);
    }
}

// Format Date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatDateTime(dateString, timeString) {
    const date = new Date(dateString + 'T' + timeString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Close modal with X button
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.close').forEach(closeBtn => {
        closeBtn.onclick = function() {
            this.closest('.modal').style.display = 'none';
        };
    });
});

