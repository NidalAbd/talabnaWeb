// SweetAlert2 Configuration
// This file provides global configuration for SweetAlert2

// Set default configuration for SweetAlert2
if (typeof Swal !== 'undefined') {
    // Default configuration
    Swal.mixin({
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, proceed!',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        allowOutsideClick: false,
        allowEscapeKey: false
    });

    // Custom success configuration
    window.showSuccessAlert = function(message, title = 'Success!') {
        return Swal.fire({
            title: title,
            text: message,
            icon: 'success',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    };

    // Custom error configuration
    window.showErrorAlert = function(message, title = 'Error!') {
        return Swal.fire({
            title: title,
            text: message,
            icon: 'error',
            confirmButtonColor: '#d33'
        });
    };

    // Custom warning configuration
    window.showWarningAlert = function(message, title = 'Warning!') {
        return Swal.fire({
            title: title,
            text: message,
            icon: 'warning',
            confirmButtonColor: '#ffc107'
        });
    };

    // Custom confirmation dialog
    window.showConfirmDialog = function(message, title = 'Are you sure?') {
        return Swal.fire({
            title: title,
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed!',
            cancelButtonText: 'Cancel'
        });
    };

    // Custom loading dialog
    window.showLoadingDialog = function(message = 'Processing...') {
        return Swal.fire({
            title: message,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    };
} 