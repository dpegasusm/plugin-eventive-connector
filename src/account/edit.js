/**
 * Eventive Account Block - Editor Component
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import './editor.scss';

const ALLOWED_BLOCKS = [
	'eventive/account-details',
	'eventive/account-passes',
	'eventive/account-tickets',
];

const TEMPLATE = [
	[ 'eventive/account-details', {} ],
	[ 'eventive/account-passes', {} ],
	[ 'eventive/account-tickets', {} ],
];

/**
 * Edit component for Eventive Account block
 *
 * @param {Object} props Block properties
 * @return {JSX.Element} Edit component
 */
export default function Edit() {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<div className="eventive-account-editor">
				<div className="eventive-block-header">
					<h3>{ __( 'Eventive Account', 'eventive' ) }</h3>
					<p>
						{ __(
							'Container for account details, passes, and tickets',
							'eventive'
						) }
					</p>
				</div>
				<InnerBlocks
					allowedBlocks={ ALLOWED_BLOCKS }
					template={ TEMPLATE }
					templateLock="all"
				/>
			</div>
		</div>
	);
}
