/**
 * Eventive Venues Block - Editor Component
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import '../common/editor-global.scss';
import './editor.scss';

/**
 * Edit component for Eventive Venues block
 *
 * @return {JSX.Element} Edit component
 */
export default function Edit() {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<div className="eventive-block-placeholder">
				<p className="eventive-block-placeholder__title">
					<span
						className="dashicons dashicons-location-alt"
						style={ {
							fontSize: '20px',
							verticalAlign: 'middle',
							marginRight: '4px',
						} }
					></span>
					<strong>
						{ __( 'Eventive Venues Block', 'eventive' ) }
					</strong>
				</p>
				<p className="eventive-block-placeholder__description">
					{ __(
						'Venues will be displayed here on the frontend',
						'eventive'
					) }
				</p>
			</div>
		</div>
	);
}
