( function () {
	// ---------- Utilities ----------
	const rAF =
		window.requestAnimationFrame ||
		function ( cb ) {
			return setTimeout( cb, 16 );
		};
	function lower( s ) {
		return ( s == null ? '' : String( s ) ).toLowerCase();
	}

	function initWrapperDecor( wrapper ) {
		const overlay = wrapper.querySelector( '.eventive-marquee-overlay' );
		const url = wrapper.getAttribute( 'data-overlay-url' ) || '';
		const op = parseFloat(
			wrapper.getAttribute( 'data-overlay-opacity' ) || '0.22'
		);
		if ( overlay ) {
			overlay.style.backgroundImage = url ? 'url(' + url + ')' : '';
			overlay.style.opacity = isNaN( op )
				? '0.22'
				: String( Math.max( 0, Math.min( 1, op ) ) );
		}
		const captionText = (
			wrapper.getAttribute( 'data-caption' ) || ''
		).trim();
		const track = wrapper.querySelector(
			'.eventive-marquee-caption-track'
		);
		if ( track ) {
			if ( captionText ) {
				const segment = ' • ' + captionText + ' ';
				let repeated = captionText;
				while ( repeated.length < 200 ) {
					repeated += segment;
				}
				track.textContent = repeated;
			} else {
				track.textContent = '';
			}
		}
	}

	function pickFirstUrl( candidates ) {
		if ( ! Array.isArray( candidates ) ) {
			return '';
		}
		for ( let i = 0; i < candidates.length; i++ ) {
			const c = candidates[ i ];
			if ( ! c ) {
				continue;
			}
			if ( typeof c === 'string' && c.trim() ) {
				return c.trim();
			}
			if (
				typeof c === 'object' &&
				typeof c.url === 'string' &&
				c.url.trim()
			) {
				return c.url.trim();
			}
		}
		return '';
	}

	function safe( obj, chain ) {
		try {
			return chain.reduce( function ( o, k ) {
				return o && o[ k ] !== undefined ? o[ k ] : undefined;
			}, obj );
		} catch ( e ) {
			return undefined;
		}
	}

	function getImageUrlForFilm( film, useStills ) {
		const stillCandidates = [
			safe( film, [ 'images', 'still_image' ] ),
			safe( film, [ 'images', 'still' ] ),
			safe( film, [ 'images', 'stillImage' ] ),
			safe( film, [ 'images', 'still_url' ] ),
			safe( film, [ 'images', 'still', 'url' ] ),
			safe( film, [ 'images', 'still_image', 'url' ] ),
			safe( film, [ 'still_image' ] ),
			safe( film, [ 'still_url' ] ),
		];
		const posterCandidates = [
			safe( film, [ 'poster_image' ] ),
			safe( film, [ 'images', 'poster_image' ] ),
			safe( film, [ 'images', 'poster' ] ),
			safe( film, [ 'images', 'poster', 'url' ] ),
			safe( film, [ 'poster', 'url' ] ),
		];
		let chosen = '';
		if ( useStills ) {
			chosen = pickFirstUrl( stillCandidates );
		}
		if ( ! chosen ) {
			chosen = pickFirstUrl( posterCandidates );
		}
		return chosen || '';
	}

	function getFilmTagNames( film ) {
		const names = [];
		if ( Array.isArray( film.tags ) ) {
			film.tags.forEach( function ( t ) {
				const n =
					t && ( t.name || t.title || t.label )
						? String( t.name || t.title || t.label ).toLowerCase()
						: '';
				if ( n ) {
					names.push( n );
				}
			} );
		}
		if ( Array.isArray( film.tag_names ) ) {
			film.tag_names.forEach( function ( s ) {
				const n = s ? String( s ).toLowerCase() : '';
				if ( n ) {
					names.push( n );
				}
			} );
		}
		if ( Array.isArray( film.categories ) ) {
			film.categories.forEach( function ( s ) {
				const n = s ? String( s ).toLowerCase() : '';
				if ( n ) {
					names.push( n );
				}
			} );
		}
		if ( film.category ) {
			names.push( String( film.category ).toLowerCase() );
		}
		return Array.from( new Set( names ) );
	}
	function filmHasAnyTag( film, list ) {
		if ( ! list || ! list.length ) {
			return false;
		}
		const t = getFilmTagNames( film );
		return list.some( function ( x ) {
			return t.indexOf( x ) > -1;
		} );
	}
	function parseTagList( str ) {
		if ( ! str ) {
			return [];
		}
		return String( str )
			.split( ',' )
			.map( function ( s ) {
				return s.trim().toLowerCase().replace( /\s+/g, ' ' );
			} )
			.filter( Boolean );
	}
	function filterByIncludeExclude( items, includeStr, excludeStr ) {
		const inc = parseTagList( includeStr ),
			exc = parseTagList( excludeStr );
		return ( items || [] ).filter( function ( item ) {
			const passInc = inc.length ? filmHasAnyTag( item, inc ) : true;
			const passExc = exc.length ? ! filmHasAnyTag( item, exc ) : true;
			return passInc && passExc;
		} );
	}

	function createPosterSlide(
		filmName,
		imageUrl,
		filmId,
		filmSyncEnabled,
		prettyPermalinks,
		detailBaseURL
	) {
		if ( ! imageUrl ) {
			const placeholder = document.createElement( 'a' );
			placeholder.href = '#';
			placeholder.className = 'poster-slide placeholder';
			placeholder.setAttribute( 'aria-hidden', 'true' );
			return placeholder;
		}
		const filmNameSlug = String( filmName || '' )
			.toLowerCase()
			.replace( /[^\w\s-]/g, '' )
			.replace( /\s+/g, '-' )
			.trim();
		let linkHref;
		if ( filmSyncEnabled ) {
			linkHref = detailBaseURL.replace( /\/$/, '' ) + '/' + filmNameSlug;
		} else {
			linkHref = prettyPermalinks
				? detailBaseURL + '?film-id=' + encodeURIComponent( filmId )
				: detailBaseURL + '&film-id=' + encodeURIComponent( filmId );
		}

		const slide = document.createElement( 'div' );
		slide.className = 'poster-slide';
		slide.style.backgroundImage = "url('" + imageUrl + "')";
		const a = document.createElement( 'a' );
		a.href = linkHref;
		a.target = '_self';
		a.appendChild( slide );
		a.addEventListener( 'click', function ( e ) {
			e.stopPropagation();
		} );
		return a;
	}

	function duplicateContentForLoop( container ) {
		const slides = Array.from( container.children );
		slides.forEach( function ( s ) {
			container.appendChild( s.cloneNode( true ) );
		} );
	}

	function initOneWrapper( wrapper ) {
		if ( wrapper.__evtMarqueeInited ) {
			return;
		}
		wrapper.__evtMarqueeInited = true;
		initWrapperDecor( wrapper );

		const filmSyncEnabled =
			wrapper.getAttribute( 'data-film-sync-enabled' ) === 'true';
		const prettyPermalinks =
			wrapper.getAttribute( 'data-pretty-permalinks' ) === 'true';
		const detailBaseURL =
			wrapper.getAttribute( 'data-detail-base-url' ) || '';
		const eventBucket = wrapper.getAttribute( 'data-event-bucket' );

		const marquee = wrapper.querySelector( '.eventive-marquee' );
		if ( ! marquee ) {
			return;
		}
		const tag = marquee.getAttribute( 'data-tag' ) || '';
		const number = Math.min(
			parseInt( marquee.getAttribute( 'data-number' ), 10 ) || 5,
			50
		);
		const rawStills = ( marquee.getAttribute( 'data-stills' ) || '' )
			.toString()
			.toLowerCase();
		const useStills =
			rawStills === 'true' || rawStills === '1' || rawStills === 'yes';
		const yearRound = marquee.getAttribute( 'data-year-round' ) === 'true';
		const exclude = marquee.getAttribute( 'data-exclude' ) || '';

		function run() {
			const qs = yearRound ? '?marquee=true' : '';
			window.Eventive.request( {
				method: 'GET',
				path:
					'event_buckets/' +
					encodeURIComponent( eventBucket ) +
					'/films' +
					qs,
			} )
				.then( function ( res ) {
					if ( ! res || ! res.films ) {
						return;
					}
					const filtered = filterByIncludeExclude(
						res.films,
						tag,
						exclude
					);
					const content = document.createElement( 'div' );
					content.className = 'marquee-content';
					const slideWidth = 210;
					filtered.slice( 0, number ).forEach( function ( f ) {
						const imageUrl = getImageUrlForFilm( f, useStills );
						content.appendChild(
							createPosterSlide(
								f.name,
								imageUrl,
								f.id,
								filmSyncEnabled,
								prettyPermalinks,
								detailBaseURL
							)
						);
					} );
					let rendered = Array.from( content.children );
					let currentWidth = rendered.length * slideWidth;
					const containerWidth = marquee.offsetWidth;
					while ( currentWidth < containerWidth ) {
						rendered.forEach( function ( slide ) {
							content.appendChild( slide.cloneNode( true ) );
						} );
						rendered = Array.from( content.children );
						currentWidth = rendered.length * slideWidth;
					}
					duplicateContentForLoop( content );
					const totalWidth = content.children.length * slideWidth;
					content.style.width = totalWidth + 'px';
					const PX_PER_SECOND = 60,
						MIN_SEC = 20,
						MAX_SEC = 180;
					const durationSec = Math.max(
						MIN_SEC,
						Math.min(
							MAX_SEC,
							Math.round( totalWidth / PX_PER_SECOND )
						)
					);
					content.style.animationDuration = durationSec + 's';
					const track = wrapper.querySelector(
						'.eventive-marquee-caption-track'
					);
					const speedAttr = lower(
						wrapper.getAttribute( 'data-caption-speed' ) || 'match'
					);
					let captionDuration = durationSec;
					const asNumber = parseInt( speedAttr, 10 );
					if ( ! isNaN( asNumber ) && asNumber > 0 ) {
						captionDuration = asNumber;
					}
					if ( track && ( track.textContent || '' ).trim().length ) {
						track.style.animationDuration = captionDuration + 's';
						track.classList.add( 'caption-scroll' );
					}
					marquee.appendChild( content );
				} )
				.catch( function ( err ) {
					try {
						console.error( '[eventive_marquee] fetch error', err );
					} catch ( _ ) {}
				} );
		}

		if ( window.Eventive && typeof Eventive.ready === 'function' ) {
			Eventive.ready( run );
		} else if (
			window.Eventive &&
			Eventive.on &&
			typeof Eventive.on === 'function'
		) {
			try {
				Eventive.on( 'ready', run );
			} catch ( _ ) {
				run();
			}
		} else {
			// Poll until Eventive.request exists, then run
			let tries = 0;
			( function poll() {
				if (
					window.Eventive &&
					typeof Eventive.request === 'function'
				) {
					run();
					return;
				}
				if ( ++tries > 60 ) {
					run();
					return;
				}
				setTimeout( poll, 50 );
			} )();
		}
	}

	function boot() {
		document
			.querySelectorAll( '.eventive-marquee-wrapper' )
			.forEach( initOneWrapper );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
	} else {
		boot();
	}

	// Elementor live preview support
	if ( window.jQuery && window.elementorFrontend ) {
		jQuery( window ).on( 'elementor/frontend/init', function () {
			try {
				elementorFrontend.hooks.addAction(
					'frontend/element_ready/shortcode.default',
					function ( scope ) {
						if ( scope && scope[ 0 ] ) {
							const wraps = scope[ 0 ].querySelectorAll(
								'.eventive-marquee-wrapper'
							);
							wraps && wraps.forEach( initOneWrapper );
						}
					}
				);
			} catch ( _ ) {}
		} );
	}
} )();
