document.addEventListener('DOMContentLoaded', function() {
    // Check for SweetAlert messages from PHP
    if (typeof Swal !== 'undefined') {
        // Check sessionStorage for swal messages
        const swalData = sessionStorage.getItem('swal');
        if (swalData) {
            try {
                const data = JSON.parse(swalData);
                Swal.fire({
                    icon: data.type || 'info',
                    title: data.title || 'Notification',
                    text: data.text || '',
                    timer: 3000,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true
                });
                sessionStorage.removeItem('swal');
            } catch(e) {
                console.log('SweetAlert error:', e);
            }
        }
    }

    // Auto-hide Bootstrap alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
});

// SweetAlert helper functions
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: message,
        timer: 3000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
    });
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: message,
        timer: 3000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
    });
}

function showWarning(message) {
    Swal.fire({
        icon: 'warning',
        title: 'Warning!',
        text: message,
        timer: 3000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
    });
}

function showInfo(message) {
    Swal.fire({
        icon: 'info',
        title: 'Information',
        text: message,
        timer: 3000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
    });
}

function removeItem(key, name) {
    Swal.fire({
        title: 'Remove Item?',
        text: 'Are you sure you want to remove "' + name + '" from cart?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, remove!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../actions/remove-from-cart.php?key=' + key + '&redirect=sales.php';
        }
    });
}

function clearCart() {
    Swal.fire({
        title: 'Clear Cart?',
        text: 'Are you sure you want to remove all items from cart?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, clear all!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../actions/clear-cart.php?redirect=sales.php';
        }
    });
}