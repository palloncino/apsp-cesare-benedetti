/**
 * Page Tracking JavaScript
 * Temporary system for tracking completed pages
 * Easy to remove later - just delete this file
 */

jQuery(document).ready(function($) {
    
    // Handle checkbox clicks
    $(document).on('change', '.at-page-checkbox', function() {
        var checkbox = $(this);
        var pageId = checkbox.data('page-id');
        var isCompleted = checkbox.is(':checked');
        
        // Show loading state
        checkbox.prop('disabled', true);
        
        // Send AJAX request
        $.ajax({
            url: atTracking.ajax_url,
            type: 'POST',
            data: {
                action: 'at_update_page_tracking',
                page_id: pageId,
                is_completed: isCompleted,
                nonce: atTracking.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Success - checkbox state is already updated
                    console.log('Page tracking updated successfully');
                } else {
                    // Error - revert checkbox state
                    checkbox.prop('checked', !isCompleted);
                    alert('Error updating tracking. Please try again.');
                }
            },
            error: function() {
                // Error - revert checkbox state
                checkbox.prop('checked', !isCompleted);
                alert('Error updating tracking. Please try again.');
            },
            complete: function() {
                // Re-enable checkbox
                checkbox.prop('disabled', false);
            }
        });
    });
    
    // CSS is now handled by separate file: page-tracking.css
    
}); 