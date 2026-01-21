/**
 * Eventive Events Week Block - Editor Component
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import './editor.scss';

/**
 * Edit component for Eventive Events Week block
 *
 * @param {Object} props Block properties
 * @return {JSX.Element} Edit component
 */
export default function Edit() {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<div className="eventive-block-placeholder">
				<p className="eventive-block-placeholder__title">
					{ __( 'Eventive Events Week', 'eventive' ) }
				</p>
				<p className="eventive-block-placeholder__description">
					{ __(
						'Weekly calendar grid will display here',
						'eventive'
					) }
				</p>
			</div>
		</div>
	);
}
