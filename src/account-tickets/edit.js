/**
 * Eventive Account Tickets Block - Editor Component
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import '../common/editor-global.scss';
import './editor.scss';

/**
 * Edit component for Eventive Account Tickets block
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
					<strong>
						{ __( 'Eventive Account Tickets', 'eventive' ) }
					</strong>
				</p>
				<p className="eventive-block-placeholder__description">
					{ __(
						'Displays tickets for logged-in users with barcode viewing.',
						'eventive'
					) }
				</p>
			</div>
		</div>
	);
}
