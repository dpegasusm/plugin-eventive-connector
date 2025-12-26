/**
 * Eventive Tags Block - Render Template
 *
 * @package Eventive
 * @since 1.0.0
 *
 * @param array $attributes Block attributes.
 * @param string $content Block default content.
 * @param WP_Block $block Block instance.
 */

// Get global event bucket setting
$event_bucket = get_option( 'eventive_event_bucket', '' );

// Get block attributes
$view         = isset( $attributes['view'] ) ? $attributes['view'] : 'list';
$display      = isset( $attributes['display'] ) ? $attributes['display'] : 'both';
$hide_empty   = isset( $attributes['hideEmpty'] ) ? $attributes['hideEmpty'] : false;
$exclude_tags = isset( $attributes['excludeTags'] ) ? $attributes['excludeTags'] : '';

// Build wrapper attributes with data attributes for React
$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'data-view'         => esc_attr( $view ),
		'data-display'      => esc_attr( $display ),
		'data-hide-empty'   => esc_attr( $hide_empty ? 'true' : 'false' ),
		'data-exclude-tags' => esc_attr( $exclude_tags ),
		'data-bucket'       => esc_attr( $event_bucket ),
	)
);
?>

<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<!-- React will mount here -->
</div>
