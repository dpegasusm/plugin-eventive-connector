/**
 * Eventive Single Film Block - Edit Component
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Spinner } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

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
	const [ films, setFilms ] = useState( [] );
	const [ events, setEvents ] = useState( [] );
	const [ loadingFilms, setLoadingFilms ] = useState( true );
	const [ loadingEvents, setLoadingEvents ] = useState( true );

	useEffect( () => {
		// Fetch films
		const fetchFilms = async () => {
			try {
				const params = new URLSearchParams( {
					eventive_nonce: window.EventiveBlockData?.eventNonce || '',
				} );

				const data = await apiFetch( {
					path: `eventive/v1/films?${ params.toString() }`,
					method: 'GET',
				} );

				const filmList = ( data.films || data || [] ).map(
					( film ) => ( {
						label: film.name || film.title || 'Untitled',
						value: film.id,
					} )
				);

				filmList.sort( ( a, b ) => a.label.localeCompare( b.label ) );
				setFilms( filmList );
			} catch ( error ) {
				console.error( 'Error fetching films:', error );
			} finally {
				setLoadingFilms( false );
			}
		};

		// Fetch events
		const fetchEvents = async () => {
			try {
				const params = new URLSearchParams( {
					eventive_nonce: window.EventiveBlockData?.eventNonce || '',
				} );

				const data = await apiFetch( {
					path: `eventive/v1/events?${ params.toString() }`,
					method: 'GET',
				} );

				const eventList = ( data.events || data || [] )
					.map( ( event ) => ( {
						label: event.name || event.title || 'Untitled',
						value: event.id,
					} ) )
					.sort( ( a, b ) => a.label.localeCompare( b.label ) );

				setEvents( eventList );
			} catch ( error ) {
				console.error( 'Error fetching events:', error );
			} finally {
				setLoadingEvents( false );
			}
		};

		fetchFilms();
		fetchEvents();
	}, [] );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Single Film Settings', 'eventive' ) }>
					{ loadingFilms ? (
						<div style={ { padding: '10px 0' } }>
							<Spinner /> { __( 'Loading films…', 'eventive' ) }
						</div>
					) : (
						<SelectControl
							label={ __( 'Select Film', 'eventive' ) }
							value={ attributes.filmId }
							options={ [
								{
									label: __(
										'-- Select a Film --',
										'eventive'
									),
									value: '',
								},
								...films,
							] }
							onChange={ ( value ) => {
								setAttributes( {
									filmId: value,
									eventId: '',
								} );
							} }
							help={ __(
								'Choose a film to display',
								'eventive'
							) }
						/>
					) }

					{ loadingEvents ? (
						<div style={ { padding: '10px 0' } }>
							<Spinner /> { __( 'Loading events…', 'eventive' ) }
						</div>
					) : (
						<SelectControl
							label={ __( 'Select Event', 'eventive' ) }
							value={ attributes.eventId }
							options={ [
								{
									label: __(
										'-- Select an Event --',
										'eventive'
									),
									value: '',
								},
								...events,
							] }
							onChange={ ( value ) => {
								setAttributes( {
									eventId: value,
									filmId: '',
								} );
							} }
							help={ __(
								'Choose an event to display',
								'eventive'
							) }
						/>
					) }

					<p className="components-base-control__help">
						{ __(
							'Provide either a Film or Event (not both).',
							'eventive'
						) }
					</p>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div className="eventive-block-placeholder">
					<h3 className="eventive-block-placeholder__title">
						{ __( 'Eventive Single Film', 'eventive' ) }
					</h3>
					<div className="eventive-block-placeholder__description">
						<p>
							{ __(
								'Film or event details will display on the frontend.',
								'eventive'
							) }
						</p>
						{ attributes.filmId && (
							<p>
								<strong>
									{ __( 'Selected Film:', 'eventive' ) }
								</strong>{ ' ' }
								{ films.find(
									( f ) => f.value === attributes.filmId
								)?.label || attributes.filmId }
							</p>
						) }
						{ attributes.eventId && (
							<p>
								<strong>
									{ __( 'Selected Event:', 'eventive' ) }
								</strong>{ ' ' }
								{ events.find(
									( e ) => e.value === attributes.eventId
								)?.label || attributes.eventId }
							</p>
						) }
						{ ! attributes.filmId && ! attributes.eventId && (
							<p className="warning">
								{ __(
									'Please select either a Film or Event in the block settings.',
									'eventive'
								) }
							</p>
						) }
					</div>
				</div>
			</div>
		</>
	);
}
