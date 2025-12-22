/**
 * Eventive Options Page - Event Sync Handler
 * 
 * Handles syncing events from Eventive API to WordPress posts
 */

jQuery(document).ready(function($) {
    console.log('Eventive Options script loaded');

    // Find the sync events form
    const $form = $('form').has('button[name="eventive_sync_events"]');
    const $button = $('button[name="eventive_sync_events"]');
    const $progressDiv = $('#eventive-sync-events-progress');

    if ($form.length && $button.length) {
        console.log('Sync events form found');

        // Prevent default form submission and handle via AJAX
        $form.on('submit', function(e) {
            e.preventDefault();
            console.log('Sync events button clicked');

            // Verify nonce
            const nonce = $('input[name="eventive_sync_events_nonce"]').val();
            if (!nonce) {
                alert('Security verification failed. Please refresh the page and try again.');
                return;
            }

            // Disable button and show progress
            $button.prop('disabled', true);
            $progressDiv.show().html('Syncing events with Eventive, please wait...');

            // Make AJAX call to sync events
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'sync_eventive_events',
                    nonce: nonce
                },
                success: function(response) {
                    console.log('Sync response:', response);
                    
                    if (response.success) {
                        $progressDiv.html('<span style="color: green;">✓ ' + response.data.message + '</span>');
                        
                        // Re-enable button after a short delay
                        setTimeout(function() {
                            $button.prop('disabled', false);
                            $progressDiv.fadeOut();
                        }, 3000);
                    } else {
                        $progressDiv.html('<span style="color: red;">✗ Error: ' + (response.data?.message || 'Unknown error occurred') + '</span>');
                        $button.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    $progressDiv.html('<span style="color: red;">✗ Connection error: ' + error + '</span>');
                    $button.prop('disabled', false);
                }
            });
        });
    } else {
        console.log('Sync events form not found');
    }
});
