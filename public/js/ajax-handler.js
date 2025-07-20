/**
 * Universal AJAX Handler for Laravel Admin Panel
 * Handles all CRUD operations, status changes, ban/unban users, and any other requests
 */

class AjaxHandler {
    constructor() {
        console.log('AjaxHandler initialized');
        this.init();
    }

    init() {
        console.log('Initializing AJAX handlers...');
        
        // Handle all AJAX forms
        $(document).on('submit', '.ajax-form', this.handleFormSubmit.bind(this));
        console.log('AJAX form handler attached');
        
        // Handle AJAX buttons
        $(document).on('click', '.ajax-btn', this.handleButtonClick.bind(this));
        console.log('AJAX button handler attached');
        
        // Handle AJAX links
        $(document).on('click', '.ajax-link', this.handleLinkClick.bind(this));
        console.log('AJAX link handler attached');
        
        console.log('All AJAX handlers initialized successfully');
    }

    /**
     * Universal AJAX Request Handler
     */
    handleAjaxRequest(url, method, data, successMessage, confirmMessage = null, options = {}) {
        console.log('=== AJAX Request Details ===');
        console.log('URL:', url);
        console.log('Method:', method);
        console.log('Data:', data);
        console.log('Success Message:', successMessage);
        console.log('Confirm Message:', confirmMessage);
        console.log('Options:', options);
        
        return new Promise((resolve, reject) => {
            const request = () => {
                console.log('Executing AJAX request...');
                
                // Determine if data is a string (form serialized) or object
                const isFormData = typeof data === 'string';
                console.log('Is form data:', isFormData);
                
                const ajaxOptions = {
                    url: url,
                    type: method,
                    data: isFormData ? data : {
                        ...data,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function(xhr) {
                        console.log('AJAX request starting...');
                        console.log('Request headers:', xhr.getAllResponseHeaders());
                    },
                    success: (response) => {
                        console.log('=== AJAX Success Response ===');
                        console.log('Full response:', response);
                        console.log('Response type:', typeof response);
                        console.log('Response data:', response.data);
                        console.log('Update options:', options);
                        
                        this.showSuccess(successMessage, options);
                        
                        // Handle dynamic updates if specified
                        if (options.updateRow && response.data) {
                            console.log('Updating row with data:', response.data);
                            this.updateTableRow(response.data, options.updateRow);
                        } else if (options.updateElement && response.data) {
                            console.log('Updating element with data:', response.data);
                            this.updateElement(response.data, options.updateElement);
                        } else if (options.removeRow && response.data) {
                            console.log('Removing row with data:', response.data);
                            this.removeTableRow(response.data, options.removeRow);
                        } else if (options.reloadAfterSuccess !== false) {
                            console.log('Reloading page after success...');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            console.log('No reload specified, staying on page');
                        }
                        
                        resolve(response);
                    },
                    error: (xhr, status, error) => {
                        console.log('=== AJAX Error Response ===');
                        console.log('Status:', status);
                        console.log('Error:', error);
                        console.log('Response Text:', xhr.responseText);
                        console.log('Status Code:', xhr.status);
                        console.log('Response Headers:', xhr.getAllResponseHeaders());
                        
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            console.log('Parsed error response:', errorResponse);
                        } catch (e) {
                            console.log('Could not parse error response as JSON');
                        }
                        
                        this.showError(xhr, options);
                        reject(xhr);
                    }
                };

                console.log('AJAX options:', ajaxOptions);

                $.ajax(ajaxOptions);
            };

            if (confirmMessage) {
                console.log('Showing confirmation dialog...');
                this.showConfirm(confirmMessage, request);
            } else {
                console.log('No confirmation needed, executing request directly');
                request();
            }
        });
    }

    /**
     * Handle form submissions
     */
    handleFormSubmit(e) {
        console.log('=== Form Submit Event ===');
        e.preventDefault();
        const form = $(e.target);
        console.log('Form element:', form);
        console.log('Form HTML:', form.prop('outerHTML'));
        
        const url = form.data('url');
        const method = form.data('method') || 'POST';
        const confirmMessage = form.data('confirm');
        const successMessage = form.data('success') || 'Operation completed successfully!';
        const reloadAfterSuccess = form.data('reload') !== false; // Default to true
        const updateRow = form.data('update-row');
        const updateElement = form.data('update-element');
        const removeRow = form.data('remove-row');
        
        console.log('Form data attributes:', {
            url, method, confirmMessage, successMessage, 
            reloadAfterSuccess, updateRow, updateElement, removeRow
        });
        console.log('Form serialized data:', form.serialize());
        
        this.handleAjaxRequest(
            url, 
            method, 
            form.serialize(), 
            successMessage, 
            confirmMessage,
            { 
                reloadAfterSuccess,
                updateRow,
                updateElement,
                removeRow
            }
        );
    }

    /**
     * Handle button clicks
     */
    handleButtonClick(e) {
        console.log('=== Button Click Event ===');
        e.preventDefault();
        const button = $(e.target);
        console.log('Button element:', button);
        console.log('Button HTML:', button.prop('outerHTML'));
        
        const url = button.data('url');
        const method = button.data('method') || 'POST';
        const confirmMessage = button.data('confirm');
        const successMessage = button.data('success') || 'Operation completed successfully!';
        const reloadAfterSuccess = button.data('reload') !== false;
        const updateRow = button.data('update-row');
        const updateElement = button.data('update-element');
        const removeRow = button.data('remove-row');
        
        console.log('Button data attributes:', {
            url, method, confirmMessage, successMessage, 
            reloadAfterSuccess, updateRow, updateElement, removeRow
        });
        console.log('Button data:', button.data('data'));
        
        this.handleAjaxRequest(
            url, 
            method, 
            button.data('data') || {}, 
            successMessage, 
            confirmMessage,
            { 
                reloadAfterSuccess,
                updateRow,
                updateElement,
                removeRow
            }
        );
    }

    /**
     * Handle link clicks
     */
    handleLinkClick(e) {
        console.log('=== Link Click Event ===');
        e.preventDefault();
        const link = $(e.target);
        console.log('Link element:', link);
        console.log('Link HTML:', link.prop('outerHTML'));
        
        const url = link.attr('href');
        const method = link.data('method') || 'GET';
        const confirmMessage = link.data('confirm');
        const successMessage = link.data('success') || 'Operation completed successfully!';
        const reloadAfterSuccess = link.data('reload') !== false;
        const updateRow = link.data('update-row');
        const updateElement = link.data('update-element');
        const removeRow = link.data('remove-row');
        
        console.log('Link data attributes:', {
            url, method, confirmMessage, successMessage, 
            reloadAfterSuccess, updateRow, updateElement, removeRow
        });
        console.log('Link data:', link.data('data'));
        
        this.handleAjaxRequest(
            url, 
            method, 
            link.data('data') || {}, 
            successMessage, 
            confirmMessage,
            { 
                reloadAfterSuccess,
                updateRow,
                updateElement,
                removeRow
            }
        );
    }

    /**
     * Update table row with new data
     */
    updateTableRow(data, rowSelector) {
        const row = $(rowSelector);
        if (row.length === 0) {
            console.log('Row not found:', rowSelector);
            return;
        }

        console.log('Updating row with data:', data);
        console.log('Row found:', row.length, 'elements');

        // Update name fields
        if (data.name !== undefined) {
            const nameCell = row.find('td:nth-child(2)'); // Name column
            if (nameCell.length > 0) {
                nameCell.text(data.name);
            }
        }

        // Update email fields
        if (data.email !== undefined) {
            const emailCell = row.find('td:nth-child(3)'); // Email column
            if (emailCell.length > 0) {
                emailCell.text(data.email);
            }
        }

        // Update phone fields
        if (data.phone !== undefined) {
            const phoneCell = row.find('td:nth-child(4)'); // Phone column
            if (phoneCell.length > 0) {
                phoneCell.text(data.phone);
            }
        }

        // Update category fields
        if (data.category_name !== undefined) {
            const categoryCell = row.find('td:nth-child(5)'); // Category column
            if (categoryCell.length > 0) {
                categoryCell.text(data.category_name);
            }
        }

        // Update subcategory fields
        if (data.subcategory_name !== undefined) {
            const subcategoryCell = row.find('td:nth-child(6)'); // Subcategory column
            if (subcategoryCell.length > 0) {
                subcategoryCell.text(data.subcategory_name);
            }
        }

        // Update user status badges (for users)
        if (data.is_active !== undefined) {
            const statusCell = row.find('td:nth-child(7)'); // Status column
            console.log('Found status cell:', statusCell.length, statusCell);
            if (statusCell.length > 0) {
                let badgeClass, statusText, icon;
                
                if (data.is_active === 'active') {
                    badgeClass = 'badge-success';
                    statusText = 'Active';
                    icon = 'fa-check-circle';
                } else if (data.is_active === 'banned') {
                    badgeClass = 'badge-danger';
                    statusText = 'Banned';
                    icon = 'fa-ban';
                } else {
                    badgeClass = 'badge-warning';
                    statusText = 'Inactive';
                    icon = 'fa-pause-circle';
                }
                
                const newBadge = `<span class="badge ${badgeClass}"><i class="fas ${icon}"></i> ${statusText}</span>`;
                console.log('Updating status cell with:', newBadge);
                console.log('Current status cell content:', statusCell.html());
                
                // Add a more visible flash effect to make the change obvious
                statusCell.addClass('bg-warning').fadeOut(200, function() {
                    $(this).html(newBadge).fadeIn(200).removeClass('bg-warning');
                    console.log('Status cell updated, new content:', $(this).html());
                    
                    // Add a brief flash effect to make the change more obvious
                    $(this).addClass('bg-success').delay(100).queue(function() {
                        $(this).removeClass('bg-success').dequeue();
                    });
                });
                
                // Add a brief highlight to the entire row
                row.addClass('table-warning');
                setTimeout(() => row.removeClass('table-warning'), 1000);
            } else {
                console.log('Status cell not found in row');
            }
        }

        // Update status badges (for categories and subcategories)
        if (data.isSuspended !== undefined) {
            const statusCell = row.find('.status-badge');
            if (statusCell.length > 0) {
                const badgeClass = data.isSuspended ? 'badge-danger' : 'badge-success';
                const statusText = data.isSuspended ? 'Suspended' : 'Active';
                const icon = data.isSuspended ? 'fa-ban' : 'fa-check-circle';
                statusCell.html(`<span class="badge ${badgeClass}"><i class="fas ${icon}"></i> ${statusText}</span>`);
            }
        }

        // Update status badges (for service posts - legacy support)
        if (data.status !== undefined) {
            const statusCell = row.find('.status-badge');
            if (statusCell.length > 0) {
                const badgeClass = data.status ? 'badge-success' : 'badge-danger';
                const statusText = data.status ? 'Active' : 'Suspended';
                const icon = data.status ? 'fa-check-circle' : 'fa-ban';
                statusCell.html(`<span class="badge ${badgeClass}"><i class="fas ${icon}"></i> ${statusText}</span>`);
            }
        }

        // Update premium badge
        if (data.is_premium !== undefined) {
            const premiumCell = row.find('.premium-badge');
            if (premiumCell.length > 0) {
                const badgeClass = data.is_premium ? 'badge-warning' : 'badge-secondary';
                const premiumText = data.is_premium ? 'Premium' : 'Regular';
                premiumCell.html(`<span class="badge ${badgeClass}">${premiumText}</span>`);
            }
        }

        // Update approval status
        if (data.is_approved !== undefined) {
            const approvalCell = row.find('.approval-badge');
            if (approvalCell.length > 0) {
                const badgeClass = data.is_approved ? 'badge-success' : 'badge-warning';
                const approvalText = data.is_approved ? 'Approved' : 'Pending';
                approvalCell.html(`<span class="badge ${badgeClass}">${approvalText}</span>`);
            }
        }

        // Update action buttons for categories/subcategories
        if (data.isSuspended !== undefined) {
            const toggleBtn = row.find('.toggle-status-btn');
            if (toggleBtn.length > 0) {
                const newText = data.isSuspended ? 'Unsuspend' : 'Suspend';
                const newIcon = data.isSuspended ? 'fa-play' : 'fa-pause';
                const newClass = data.isSuspended ? 'btn-success' : 'btn-warning';
                toggleBtn.removeClass('btn-success btn-warning').addClass(newClass);
                toggleBtn.find('i').removeClass('fa-play fa-pause').addClass(newIcon);
                toggleBtn.attr('title', newText);
            }
        }

        // Update action buttons for users (ban/unban)
        if (data.is_active !== undefined) {
            const banBtn = row.find('form[data-url*="ban"] button');
            const unbanBtn = row.find('form[data-url*="unban"] button');
            const toggleBanBtn = row.find('form[data-url*="toggle-ban"] button');
            
            console.log('Found buttons:', {
                banBtn: banBtn.length,
                unbanBtn: unbanBtn.length,
                toggleBanBtn: toggleBanBtn.length
            });
            
            if (data.is_active === 'banned') {
                console.log('User is now banned, updating buttons...');
                // Show unban button, hide ban button
                if (banBtn.length > 0) banBtn.closest('form').hide();
                if (unbanBtn.length > 0) unbanBtn.closest('form').show();
                if (toggleBanBtn.length > 0) {
                    console.log('Updating toggle button to unban style');
                    console.log('Before update - classes:', toggleBanBtn.attr('class'));
                    console.log('Before update - icon:', toggleBanBtn.find('i').attr('class'));
                    
                    toggleBanBtn.removeClass('btn-outline-warning').addClass('btn-outline-success');
                    toggleBanBtn.find('i').removeClass('fa-user-slash').addClass('fa-unlock');
                    toggleBanBtn.attr('title', 'Unban');
                    console.log('Updated toggle button to unban style');
                    console.log('After update - classes:', toggleBanBtn.attr('class'));
                    console.log('After update - icon:', toggleBanBtn.find('i').attr('class'));
                    
                    // Add more visible visual feedback
                    toggleBanBtn.addClass('btn-success').fadeOut(200, function() {
                        $(this).fadeIn(200).removeClass('btn-success');
                        console.log('Button fade effect completed');
                        
                        // Add a brief pulse effect to make the change more obvious
                        $(this).addClass('btn-success').delay(100).queue(function() {
                            $(this).removeClass('btn-success').dequeue();
                        });
                    });
                }
            } else {
                console.log('User is now active, updating buttons...');
                // Show ban button, hide unban button
                if (banBtn.length > 0) banBtn.closest('form').show();
                if (unbanBtn.length > 0) unbanBtn.closest('form').hide();
                if (toggleBanBtn.length > 0) {
                    console.log('Updating toggle button to ban style');
                    console.log('Before update - classes:', toggleBanBtn.attr('class'));
                    console.log('Before update - icon:', toggleBanBtn.find('i').attr('class'));
                    
                    toggleBanBtn.removeClass('btn-outline-success').addClass('btn-outline-warning');
                    toggleBanBtn.find('i').removeClass('fa-unlock').addClass('fa-user-slash');
                    toggleBanBtn.attr('title', 'Ban');
                    console.log('Updated toggle button to ban style');
                    console.log('After update - classes:', toggleBanBtn.attr('class'));
                    console.log('After update - icon:', toggleBanBtn.find('i').attr('class'));
                    
                    // Add more visible visual feedback
                    toggleBanBtn.addClass('btn-warning').fadeOut(200, function() {
                        $(this).fadeIn(200).removeClass('btn-warning');
                        console.log('Button fade effect completed');
                        
                        // Add a brief pulse effect to make the change more obvious
                        $(this).addClass('btn-warning').delay(100).queue(function() {
                            $(this).removeClass('btn-warning').dequeue();
                        });
                    });
                }
            }
        }

        // Update action buttons for service posts (legacy support)
        if (data.status !== undefined) {
            const toggleBtn = row.find('.toggle-status-btn');
            if (toggleBtn.length > 0) {
                const newStatus = data.status ? 0 : 1;
                const newText = data.status ? 'Suspend' : 'Activate';
                const newClass = data.status ? 'btn-warning' : 'btn-success';
                toggleBtn.removeClass('btn-success btn-warning').addClass(newClass);
                toggleBtn.text(newText);
                toggleBtn.data('url', toggleBtn.data('url').replace(/\/\d+\/toggle/, `/${data.id}/toggle`));
            }
        }

        if (data.is_premium !== undefined) {
            const togglePremiumBtn = row.find('.toggle-premium-btn');
            if (togglePremiumBtn.length > 0) {
                const newText = data.is_premium ? 'Remove Premium' : 'Make Premium';
                const newClass = data.is_premium ? 'btn-secondary' : 'btn-warning';
                togglePremiumBtn.removeClass('btn-warning btn-secondary').addClass(newClass);
                togglePremiumBtn.text(newText);
            }
        }

        if (data.is_approved !== undefined) {
            const approveBtn = row.find('.approve-btn');
            const rejectBtn = row.find('.reject-btn');
            
            if (approveBtn.length > 0) {
                approveBtn.toggleClass('d-none', data.is_approved);
            }
            if (rejectBtn.length > 0) {
                rejectBtn.toggleClass('d-none', !data.is_approved);
            }
        }

        // Add visual feedback
        row.addClass('table-success');
        setTimeout(() => row.removeClass('table-success'), 2000);
        
        // Additional visual feedback for user status changes
        if (data.is_active !== undefined) {
            row.addClass('table-warning');
            setTimeout(() => row.removeClass('table-warning'), 1000);
        }

        // Update service post status badges
        if (data.state !== undefined) {
            const statusCell = row.find('td:nth-child(8)'); // Status column
            console.log('Found status cell:', statusCell.length, statusCell);
            if (statusCell.length > 0) {
                let badgeClass, statusText, icon;
                
                switch (data.state) {
                    case 'published':
                        badgeClass = 'badge-success';
                        statusText = 'Published';
                        icon = 'fa-check-circle';
                        break;
                    case 'not published':
                        badgeClass = 'badge-warning';
                        statusText = 'Pending';
                        icon = 'fa-clock';
                        break;
                    case 'rejected':
                        badgeClass = 'badge-danger';
                        statusText = 'Rejected';
                        icon = 'fa-times-circle';
                        break;
                    case 'archive':
                        badgeClass = 'badge-secondary';
                        statusText = 'Archived';
                        icon = 'fa-archive';
                        break;
                    default:
                        badgeClass = 'badge-info';
                        statusText = data.state.charAt(0).toUpperCase() + data.state.slice(1);
                        icon = 'fa-info-circle';
                }
                
                const newBadge = `<span class="badge ${badgeClass} status-badge"><i class="fas ${icon}"></i> ${statusText}</span>`;
                console.log('Updating status cell with:', newBadge);
                console.log('Current status cell content:', statusCell.html());
                
                // Add a more visible flash effect to make the change obvious
                statusCell.addClass('bg-warning').fadeOut(200, function() {
                    $(this).html(newBadge).fadeIn(200).removeClass('bg-warning');
                    console.log('Status cell updated, new content:', $(this).html());
                    
                    // Add a brief flash effect to make the change more obvious
                    $(this).addClass('bg-success').delay(100).queue(function() {
                        $(this).removeClass('bg-success').dequeue();
                    });
                });
            }
        }

        // Update service post premium badges
        if (data.is_premium !== undefined) {
            const premiumCell = row.find('td:nth-child(9)'); // Premium column
            console.log('Found premium cell:', premiumCell.length, premiumCell);
            if (premiumCell.length > 0) {
                let newBadge;
                if (data.is_premium) {
                    newBadge = '<span class="badge badge-warning"><i class="fas fa-star"></i> Premium</span>';
                } else {
                    newBadge = '<span class="badge badge-secondary">Regular</span>';
                }
                
                console.log('Updating premium cell with:', newBadge);
                
                // Add visual feedback
                premiumCell.addClass('bg-warning').fadeOut(200, function() {
                    $(this).html(newBadge).fadeIn(200).removeClass('bg-warning');
                    console.log('Premium cell updated');
                    
                    // Add a brief flash effect
                    $(this).addClass('bg-success').delay(100).queue(function() {
                        $(this).removeClass('bg-success').dequeue();
                    });
                });
            }
        }

        // Update service post action buttons based on status
        if (data.state !== undefined) {
            const actionCell = row.find('td:nth-child(12)'); // Actions column
            console.log('Found action cell:', actionCell.length, actionCell);
            
            if (actionCell.length > 0) {
                // Update approve/reject buttons
                const approveForm = actionCell.find('form[data-url*="approve"]');
                const rejectForm = actionCell.find('form[data-url*="reject"]');
                
                if (data.state === 'published') {
                    approveForm.hide();
                    rejectForm.show();
                } else if (data.state === 'not published') {
                    approveForm.show();
                    rejectForm.hide();
                } else {
                    approveForm.hide();
                    rejectForm.hide();
                }
                
                console.log('Updated action buttons for state:', data.state);
            }
        }

        // Update service post title and description if provided
        if (data.title !== undefined) {
            const titleElement = row.find('td:nth-child(3) h6');
            if (titleElement.length > 0) {
                titleElement.text(data.title);
                titleElement.attr('title', data.title);
            }
        }

        if (data.description !== undefined) {
            const descElement = row.find('td:nth-child(3) p');
            if (descElement.length > 0) {
                descElement.text(data.description);
                descElement.attr('title', data.description);
            }
        }

        // Update service post price if provided
        if (data.price !== undefined) {
            const priceElement = row.find('td:nth-child(3) .badge-success');
            if (priceElement.length > 0) {
                priceElement.text(data.price + ' ' + (data.price_currency_code || 'USD'));
            }
        }

        // Update service post type if provided
        if (data.type !== undefined) {
            const typeElement = row.find('td:nth-child(3) .badge-info');
            if (typeElement.length > 0) {
                typeElement.text(data.type);
            }
        }

        // Update service post views if provided
        if (data.view_count !== undefined) {
            const viewsElement = row.find('td:nth-child(10) .badge');
            if (viewsElement.length > 0) {
                viewsElement.html(`<i class="fas fa-eye mr-1"></i>${data.view_count}`);
            }
        }

        // Update service post featured status if provided
        if (data.is_featured !== undefined) {
            const featuredElement = row.find('td:nth-child(3) .badge-warning');
            if (featuredElement.length > 0) {
                if (data.is_featured) {
                    featuredElement.html('<i class="fas fa-star"></i> Featured');
                } else {
                    featuredElement.remove();
                }
            }
        }
    }

    /**
     * Update specific element with new data
     */
    updateElement(data, elementSelector) {
        const element = $(elementSelector);
        if (element.length === 0) return;

        if (data.content !== undefined) {
            element.html(data.content);
        }

        if (data.text !== undefined) {
            element.text(data.text);
        }

        if (data.value !== undefined) {
            element.val(data.value);
        }

        // Add visual feedback
        element.addClass('text-success');
        setTimeout(() => element.removeClass('text-success'), 2000);
    }

    /**
     * Remove table row
     */
    removeTableRow(data, rowSelector) {
        const row = $(rowSelector);
        if (row.length === 0) return;

        // Add fade out effect
        row.fadeOut(500, function() {
            $(this).remove();
            
            // Update row numbers if needed
            const table = row.closest('table');
            table.find('tbody tr').each(function(index) {
                const rowNumberCell = $(this).find('td:first');
                if (rowNumberCell.length > 0) {
                    rowNumberCell.text(index + 1);
                }
            });
        });
    }

    /**
     * Show success message
     */
    showSuccess(message, options = {}) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Success!',
                text: message,
                icon: 'success',
                timer: options.timer || 2000,
                showConfirmButton: false
            });
        } else {
            alert(message);
        }
    }

    /**
     * Show error message
     */
    showError(xhr, options = {}) {
        let errorMessage = 'Operation failed. Please try again.';
        
        if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
        } else if (xhr.responseText) {
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch (e) {
                // If not JSON, use status text
                errorMessage = xhr.statusText || errorMessage;
            }
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error!',
                text: errorMessage,
                icon: 'error'
            });
        } else {
            alert('Error: ' + errorMessage);
        }
    }

    /**
     * Show confirmation dialog
     */
    showConfirm(message, callback) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, proceed!'
            }).then((result) => {
                if (result.isConfirmed) {
                    callback();
                }
            });
        } else {
            if (confirm(message)) {
                callback();
            }
        }
    }

    /**
     * Manual AJAX request (for custom use)
     */
    request(url, method = 'GET', data = {}, options = {}) {
        return this.handleAjaxRequest(
            url,
            method,
            data,
            options.successMessage || 'Operation completed successfully!',
            options.confirmMessage,
            options
        );
    }
}

// Initialize when document is ready
$(document).ready(function() {
    window.ajaxHandler = new AjaxHandler();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AjaxHandler;
} 