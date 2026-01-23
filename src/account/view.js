/**
 * Eventive Account Block - Frontend View Script
 *
 * @package
 * @since 1.0.0
 */

import { createRoot, useState, useEffect } from '@wordpress/element';

/**
 * Account component
 */
function EventiveAccount( { children } ) {
	const [ isLoggedIn, setIsLoggedIn ] = useState( false );
	const [ isLoading, setIsLoading ] = useState( true );

	useEffect( () => {
		// Wait for Eventive to be ready
		const checkLogin = () => {
			if (
				window.Eventive &&
				typeof window.Eventive.isLoggedIn === 'function'
			) {
				try {
					const loggedIn = window.Eventive.isLoggedIn();
					setIsLoggedIn( loggedIn );
				} catch ( e ) {
					console.error( '[eventive-account] login check error', e );
					setIsLoggedIn( false );
				} finally {
					setIsLoading( false );
				}
			}
		};

		if ( ! window.Eventive || ! window.Eventive.on ) {
			// If loader not present yet, show login prompt as safe default
			setIsLoading( false );
			setIsLoggedIn( false );
			return;
		}

		// Check if Eventive is already ready
		if ( window.Eventive._ready || window.Eventive.ready ) {
			checkLogin();
		} else {
			window.Eventive.on( 'ready', checkLogin );
		}

		// Also listen for auth state changes
		try {
			window.Eventive.on( 'login', () => {
				setIsLoggedIn( true );
			} );
			window.Eventive.on( 'logout', () => {
				setIsLoggedIn( false );
			} );
		} catch ( _ ) {}
	}, [] );

	const handleLogout = async ( e ) => {
		e.preventDefault();

		// Use global handler when available
		if ( typeof window.handleLogout === 'function' ) {
			try {
				window.handleLogout();
				return;
			} catch ( err ) {
				console.error( 'handleLogout error:', err );
			}
		}

		// Fallback logout flow
		try {
			// Clear storage
			const keys = [
				'eventivePersonToken',
				'eventiveAppPersonToken',
				'eventive_token',
				'eventive_person_token',
			];
			keys.forEach( ( k ) => {
				try {
					localStorage.removeItem( k );
				} catch ( _ ) {}
			} );

			try {
				sessionStorage.clear();
			} catch ( _ ) {}

			document.cookie = 'eventive-personState=; path=/; max-age=0';

			// Call Eventive logout
			if ( window.Eventive && window.Eventive.logout ) {
				try {
					await window.Eventive.logout();
				} catch ( err ) {
					console.error( 'Eventive.logout() failed:', err );
				}
			}

			// Call logout endpoint
			if ( window.Eventive && window.Eventive.request ) {
				try {
					await window.Eventive.request( {
						method: 'POST',
						path: 'people/logout',
						authenticatePerson: true,
					} );
				} catch ( _ ) {}
			}

			// Reload page
			window.location.reload();
		} catch ( err ) {
			console.error( 'Unexpected logout error:', err );
			window.location.reload();
		}
	};

	if ( isLoading ) {
		return (
			<div className="eventive-account-container">
				<p className="eventive-loading">Loading...</p>
			</div>
		);
	}

	if ( ! isLoggedIn ) {
		return (
			<div className="eventive-account-container">
				<div className="eventive-notice">
					<p>
						You are not logged in. Please log in to view your
						account.
					</p>
				</div>
			</div>
		);
	}

	return (
		<div className="eventive-account-container">
			<div className="account-actions">
				<a
					href="#"
					onClick={ handleLogout }
					className="eventive-logout-link"
				>
					Log out
				</a>
			</div>
			{ children }
		</div>
	);
}

/**
 * Initialize the block on page load
 */
document.addEventListener( 'DOMContentLoaded', () => {
	const containers = document.querySelectorAll(
		'.wp-block-eventive-account'
	);

	containers.forEach( ( container ) => {
		// Idempotent guard - don't initialize twice
		if ( container.__evtInited ) {
			return;
		}
		container.__evtInited = true;

		// Get the existing inner content (child blocks)
		const innerContent = container.innerHTML;
		const tempDiv = document.createElement( 'div' );
		tempDiv.innerHTML = innerContent;

		const root = createRoot( container );
		root.render(
			<EventiveAccount>
				<div dangerouslySetInnerHTML={ { __html: innerContent } } />
			</EventiveAccount>
		);
	} );
} );
