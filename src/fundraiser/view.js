/**
 * Fundraiser Block - Frontend View Script
 */
import apiFetch from '@wordpress/api-fetch';

/**
 * Initialize Fundraiser blocks on page load
 */
document.addEventListener( 'DOMContentLoaded', () => {
	const fundraiserBlocks = document.querySelectorAll(
		'.wp-block-eventive-fundraiser'
	);

	fundraiserBlocks.forEach( ( block ) => {
		const startDate = block.dataset.startTime;
		const endDate = block.dataset.endTime;
		const goalAmount = parseFloat( block.dataset.goalAmount ) || 1000;

		if ( ! startDate || ! endDate ) {
			const container = block.querySelector(
				'#eventive-donations-container'
			);
			if ( container ) {
				container.innerHTML =
					'<p>Error: Start and end dates are required.</p>';
			}
			return;
		}

		// Construct API call
		const fetchData = async () => {
			const donationsContainer = block.querySelector(
				'#eventive-donations-container'
			);

			// Remove loading text
			const loadingText = block.querySelector(
				'.eventive-film-loading-text'
			);
			if ( loadingText ) {
				loadingText.remove();
			}

			try {
				// Build the API path with parameters
				const params = new URLSearchParams( {
					start: startDate,
					end: endDate,
					type: 'PAYMENT',
					eventive_nonce: window.EventiveBlockData.eventNonce,
				} );

				const data = await apiFetch( {
					path: `eventive/v1/ledger?${ params.toString() }`,
					method: 'GET',
				} );

				// The API now returns aggregated data instead of raw transactions
				const totalDonations = data.total_donations || 0;
				const donationCount = data.donation_count || 0;

				const progressPercent = Math.min(
					( totalDonations / goalAmount ) * 100,
					100
				).toFixed( 2 );

				if ( donationsContainer ) {
					donationsContainer.innerHTML = `
						<div>
							<h3>Fundraiser Progress</h3>
							<div class="progress-bar-container">
								<div class="progress-bar" style="--progress-percent: ${ progressPercent }%; width: ${ progressPercent }%;"></div>
							</div>
							<p>$${ totalDonations.toFixed( 2 ) } of $${ goalAmount.toFixed(
								2
							) } raised (${ progressPercent }%)</p>
							${
								donationCount > 0
									? `<p class="donation-count">${ donationCount } donation${
											donationCount !== 1 ? 's' : ''
									  }</p>`
									: ''
							}
						</div>
					`;
				}
			} catch ( error ) {
				if ( donationsContainer ) {
					donationsContainer.innerHTML = `<p>Error fetching donations: ${ error.message }</p>`;
				}
			}
		};

		// Fetch data immediately
		fetchData();
	} );
} );
