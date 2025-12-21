<?php
/**
 * Provincetown Agile Login Block
 *
 * @package WordPress
 * @subpackage Provincetown_Film_Festival
 * @since 1.0.0
 */

/**
 * Render callback for the Provincetown Agile Login block.
 *
 * This file directly outputs the icon markup.
 */
$container_id = is_user_logged_in() ? 'provincetown-agile-account' : 'provincetown-agile-login';

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => $container_id,
	)
);

echo '<div ' . wp_kses_post( $wrapper_attributes ) . '>
	<div id="' . esc_attr( $container_id ) . '" role="button" aria-expanded="false" aria-controls="provincetown-dialog-user-modal">
		<span class="dashicons dashicons-admin-users user-icon"></span>
	</div>
</div>';
