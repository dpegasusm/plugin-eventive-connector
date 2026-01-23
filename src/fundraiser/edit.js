/**
 * Eventive Fundraiser Block - Edit Component
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, DateTimePicker } from '@wordpress/components';

import './editor.scss';

/**
 * Edit component - renders the block in the editor
 *
 * @param {Object}   props               Block properties
 * @param {Object}   props.attributes    Block attributes
 * @param {Function} props.setAttributes Function to update attributes
 * @return {JSX.Element} Edit component
 */
export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Fundraiser Settings', 'eventive' ) }>
					<div style={ { marginBottom: '16px' } }>
						<label
							style={ {
								display: 'block',
								marginBottom: '8px',
								fontWeight: '500',
							} }
						>
							{ __( 'Start Date & Time', 'eventive' ) }
						</label>
						<DateTimePicker
							currentDate={ attributes.startTime }
							onChange={ ( value ) =>
								setAttributes( { startTime: value } )
							}
							is12Hour={ true }
						/>
						<p
							style={ {
								marginTop: '8px',
								fontSize: '12px',
								color: '#757575',
							} }
						>
							{ __(
								'Start date and time for donation tracking',
								'eventive'
							) }
						</p>
					</div>
					<div style={ { marginBottom: '16px' } }>
						<label
							style={ {
								display: 'block',
								marginBottom: '8px',
								fontWeight: '500',
							} }
						>
							{ __( 'End Date & Time', 'eventive' ) }
						</label>
						<DateTimePicker
							currentDate={ attributes.endTime }
							onChange={ ( value ) =>
								setAttributes( { endTime: value } )
							}
							is12Hour={ true }
						/>
						<p
							style={ {
								marginTop: '8px',
								fontSize: '12px',
								color: '#757575',
							} }
						>
							{ __(
								'End date and time for donation tracking',
								'eventive'
							) }
						</p>
					</div>
					<TextControl
						label={ __( 'Goal Amount ($)', 'eventive' ) }
						value={ attributes.goalAmount }
						type="number"
						onChange={ ( value ) =>
							setAttributes( {
								goalAmount: parseFloat( value ) || 1000,
							} )
						}
						help={ __( 'Fundraising goal in dollars', 'eventive' ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="eventive-block-placeholder">
					<h3 className="eventive-block-placeholder__title">
						{ __( 'Eventive Fundraiser', 'eventive' ) }
					</h3>
					<div className="eventive-block-placeholder__description">
						<p>
							{ __(
								'Fundraiser progress will display on the frontend.',
								'eventive'
							) }
						</p>
						{ attributes.startTime && attributes.endTime && (
							<p>
								<strong>{ __( 'Period:', 'eventive' ) }</strong>{ ' ' }
								{ new Date(
									attributes.startTime
								).toLocaleString() }{ ' ' }
								{ __( 'to', 'eventive' ) }{ ' ' }
								{ new Date(
									attributes.endTime
								).toLocaleString() }
							</p>
						) }
						<p>
							<strong>{ __( 'Goal:', 'eventive' ) }</strong> $
							{ attributes.goalAmount }
						</p>
					</div>
				</div>
			</div>
		</>
	);
}
