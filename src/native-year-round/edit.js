/**
 * Eventive Native Year-Round Block - Editor Component
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl } from '@wordpress/components';
import './editor.scss';

/**
 * Edit component for Eventive Native Year-Round block
 *
 * @param {Object} props               Block properties
 * @param          props.attributes
 * @param          props.setAttributes
 * @return {JSX.Element} Edit component
 */
export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();
	const { image, description, venue, details } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Display Settings', 'eventive' ) }
					initialOpen={ true }
				>
					<SelectControl
						label={ __( 'Image Type', 'eventive' ) }
						value={ image }
						options={ [
							{ label: 'Poster', value: 'poster' },
							{ label: 'Cover', value: 'cover' },
							{ label: 'Still', value: 'still' },
							{ label: 'None', value: 'none' },
						] }
						onChange={ ( value ) =>
							setAttributes( { image: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show Description', 'eventive' ) }
						checked={ description }
						onChange={ ( value ) =>
							setAttributes( { description: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show Venue', 'eventive' ) }
						checked={ venue }
						onChange={ ( value ) =>
							setAttributes( { venue: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show Details', 'eventive' ) }
						checked={ details }
					onChange={ ( value ) =>
						setAttributes( { details: value } )
					}
				/>
			</PanelBody>
		</InspectorControls>

		<div { ...blockProps }>
			<div className="eventive-block-placeholder">
				<p className="eventive-block-placeholder__title">
					<strong>
						{ __( 'Eventive Native Year-Round', 'eventive' ) }
					</strong>
				</p>
				<p className="eventive-block-placeholder__description">
					{ __(
						'Weekly event calendar will display here',
						'eventive'
					) }
				</p>
			</div>
		</div>
	</>
);
}