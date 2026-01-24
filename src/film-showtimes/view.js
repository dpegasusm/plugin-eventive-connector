/**
 * Film Showtimes Block - Frontend View Script
 */
import apiFetch from '@wordpress/api-fetch';

/**
 * Group events by date
 * @param events
 */
function groupEventsByDate( events ) {
	const grouped = {};

	events.forEach( ( event ) => {
		const startTime = new Date( event.start_time );
		const dateKey = startTime.toLocaleDateString( 'en-US', {
			weekday: 'long',
			year: 'numeric',
			month: 'long',
			day: 'numeric',
		} );

		if ( ! grouped[ dateKey ] ) {
			grouped[ dateKey ] = [];
		}

		grouped[ dateKey ].push( event );
	} );

	// Sort events within each date by time
	Object.keys( grouped ).forEach( ( dateKey ) => {
		grouped[ dateKey ].sort(
			( a, b ) => new Date( a.start_time ) - new Date( b.start_time )
		);
	} );

	return grouped;
}

/**
 * Initialize Film Showtimes blocks on page load
 */
document.addEventListener( 'DOMContentLoaded', () => {
	const showtimeBlocks = document.querySelectorAll(
		'.wp-block-eventive-film-showtimes'
	);

	showtimeBlocks.forEach( ( block ) => {
		// Get post ID from EventiveBlockData (localized from PHP)
		const postId = window.EventiveBlockData?.postId;

		if ( ! postId ) {
			block.innerHTML =
				'<div class="eventive-error">This block requires it be placed on a Eventive Film post type.</div>';
			return;
		}

		// Display loading message while fetching
		block.innerHTML =
			'<div class="eventive-loading">Loading showtimes...</div>';

		const fetchAndRenderShowtimes = () => {
			// Fetch the film meta from WordPress REST API
			apiFetch( {
				path: `/wp/v2/eventive_film/${ postId }`,
			} )
				.then( ( post ) => {
					const filmId = post.meta?._eventive_film_id;
					const bucketId =
						post.meta?._eventive_bucket_id ||
						window.EventiveBlockData?.eventBucket ||
						'';

					if ( ! filmId || ! bucketId ) {
						block.innerHTML =
							'<div class="eventive-error">Missing film or bucket configuration.</div>';
						return;
					}

					// Fetch showtimes from Eventive API
					window.Eventive.request( {
						method: 'GET',
						path: `event_buckets/${ bucketId }/films/${ filmId }/events`,
						authenticatePerson: false,
					} )
						.then( ( response ) => {
							const events = response.events || [];
							if ( events.length === 0 ) {
								block.innerHTML =
									'<div class="eventive-error">No upcoming showtimes available</div>';
								return;
							}

							// Group events by date
							const grouped = groupEventsByDate( events );

							// Build HTML for showtimes
							const showtimesHTML = Object.entries( grouped )
								.map(
									( [ dateKey, dateEvents ] ) => `
								<div class="eventive-showtime-date-group">
									<h3 class="eventive-showtime-date">${ dateKey }</h3>
									<div class="eventive-showtime-list">
										${ dateEvents
											.map( ( event ) => {
												const startTime = new Date(
													event.start_time
												);
												const timeString =
													startTime.toLocaleTimeString(
														'en-US',
														{
															hour: 'numeric',
															minute: '2-digit',
															hour12: true,
														}
													);
												return `
											<div class="eventive-showtime-item">
												<span class="eventive-showtime-time">${ timeString }</span>
												<div class="eventive-button" data-event="${ event.id }"></div>
											</div>
										`;
											} )
											.join( '' ) }
									</div>
								</div>
							`
								)
								.join( '' );

							block.innerHTML = `<div class="eventive-film-showtimes-container">${ showtimesHTML }</div>`;

							// Rebuild Eventive buttons
							if ( window.Eventive?.rebuild ) {
								window.Eventive.rebuild();
							}
						} )
						.catch( ( error ) => {
							console.error(
								'[eventive-film-showtimes] Error fetching showtimes:',
								error
							);
							block.innerHTML =
								'<div class="eventive-error">Unable to load showtimes</div>';
						} );
				} )
				.catch( ( error ) => {
					console.error(
						'[eventive-film-showtimes] Error fetching post data:',
						error
					);
					block.innerHTML =
						'<div class="eventive-error">Unable to load film data. Please try again later.</div>';
				} );
		};

		if ( window.Eventive && window.Eventive._ready ) {
			fetchAndRenderShowtimes();
		} else if (
			window.Eventive &&
			typeof window.Eventive.on === 'function'
		) {
			window.Eventive.on( 'ready', fetchAndRenderShowtimes );
		} else {
			setTimeout( () => {
				if (
					window.Eventive &&
					typeof window.Eventive.request === 'function'
				) {
					fetchAndRenderShowtimes();
				} else {
					console.error(
						'[eventive-film-showtimes] Eventive API not available'
					);
					block.innerHTML =
						'<div class="eventive-error">Eventive API not available</div>';
				}
			}, 1000 );
		}
	} );
} );
