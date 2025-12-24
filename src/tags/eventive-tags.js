( function () {
	let INITED = false;
	window.__eventiveLastRenderAt = window.__eventiveLastRenderAt || 0;

	// Helper: find nearest ancestor with any of the given data attributes
	function findClosestData( el, attrs ) {
		let n = el;
		while ( n && n.nodeType === 1 ) {
			for ( let i = 0; i < attrs.length; i++ ) {
				const v = n.getAttribute( attrs[ i ] );
				if ( v != null && v !== '' ) {
					return { el: n, key: attrs[ i ], value: v };
				}
			}
			n = n.parentElement;
		}
		return null;
	}

	// Helper: unique push
	function addToSet( set, v ) {
		if ( v == null ) {
			return;
		}
		set[ v ] = true;
	}

	// Helper: CSV to map
	function csvToMap( csv ) {
		const map = {};
		if ( ! csv ) {
			return map;
		}
		String( csv )
			.split( ',' )
			.forEach( function ( s ) {
				s = ( s || '' ).trim();
				if ( ! s ) {
					return;
				}
				map[ s ] = true;
			} );
		return map;
	}

	function getTagIdFromLink( a ) {
		if ( ! a ) {
			return '';
		}
		const ds = a.getAttribute( 'data-tag-id' );
		if ( ds ) {
			return ds;
		}
		const href = a.getAttribute( 'href' ) || '';
		try {
			return (
				new URL( href, window.location.href ).searchParams.get(
					'tag-id'
				) || ''
			);
		} catch ( _ ) {
			const m = href.match( /[?&]tag-id=([^&#]+)/ );
			return m ? decodeURIComponent( m[ 1 ] ) : '';
		}
	}

	function setQueryTagId( tagId, method ) {
		try {
			const url = new URL( window.location.href );
			const had = url.searchParams.has( 'tag-id' );
			if ( ! tagId ) {
				// Remove both possible keys and clean empty search
				url.searchParams.delete( 'tag-id' );
				url.searchParams.delete( 'tag' );
			} else {
				url.searchParams.set( 'tag-id', tagId );
			}
			const finalUrl =
				url.origin +
				url.pathname +
				( url.searchParams.toString()
					? '?' + url.searchParams.toString()
					: '' ) +
				url.hash;
			if ( method === 'replace' || ( ! tagId && had ) ) {
				history.replaceState( {}, '', finalUrl );
			} else {
				history.pushState( {}, '', finalUrl );
			}
			// Notify listeners that the URL (and possibly tag) changed
			try {
				document.dispatchEvent(
					new CustomEvent( 'eventive:urlChanged', {
						detail: { tagId, url: finalUrl },
					} )
				);
			} catch ( _ ) {}
		} catch ( _ ) {}
	}

	function clearPluginParams( method, contextEl ) {
		try {
			const url = new URL( window.location.href );
			// Remove all plugin-related params
			const KEYS = [
				'tag-id',
				'tag',
				'include-tags',
				'exclude-tags',
				'film-id',
				'event-id',
				'view',
				'image',
				'show-events',
				'show-details',
				'show-tags',
				'year-round',
				'search',
				'q',
				'page',
			];
			KEYS.forEach( function ( k ) {
				url.searchParams.delete( k );
			} );

			// If an ancestor defines a canonical reset URL, use it
			const resetInfo = contextEl
				? findClosestData( contextEl, [ 'data-reset-url' ] )
				: null;
			let finalUrl = '';
			if ( resetInfo && resetInfo.value ) {
				// Ensure we keep the current hash when not supplied
				try {
					const base = new URL(
						resetInfo.value,
						window.location.origin
					);
					finalUrl =
						base.origin +
						base.pathname +
						( base.search || '' ) +
						( base.hash || window.location.hash || '' );
				} catch ( e ) {
					finalUrl = resetInfo.value; // best effort
				}
			} else {
				// Build URL without empty querystring
				finalUrl =
					url.origin +
					url.pathname +
					( url.searchParams.toString()
						? '?' + url.searchParams.toString()
						: '' ) +
					url.hash;
				if ( ! url.searchParams.toString() ) {
					finalUrl = url.origin + url.pathname + url.hash;
				}
			}

			if ( method === 'replace' ) {
				history.replaceState( {}, '', finalUrl );
			} else {
				history.pushState( {}, '', finalUrl );
			}
			// Notify listeners that plugin params have been cleared
			try {
				document.dispatchEvent(
					new CustomEvent( 'eventive:urlChanged', {
						detail: { tagId: '', url: finalUrl },
					} )
				);
			} catch ( _ ) {}
		} catch ( _ ) {}
	}

	function dispatchSetActive( tagId ) {
		try {
			document.dispatchEvent(
				new CustomEvent( 'eventive:setActiveTag', {
					detail: { tagId },
				} )
			);
		} catch ( _ ) {}
		try {
			if ( window.setActiveTag ) {
				window.setActiveTag( tagId );
			}
		} catch ( _ ) {}
		try {
			if ( window.renderEvents ) {
				window.renderEvents();
			}
		} catch ( _ ) {}
	}

	function updateActiveClasses( container, tagId ) {
		if ( ! container ) {
			return;
		}
		try {
			const links = container.querySelectorAll(
				'.eventive-tags a, a.eventive-tag, .external-tag-filter'
			);
			links.forEach( function ( l ) {
				l.classList.remove( 'is-active' );
			} );
			links.forEach( function ( l ) {
				const idAttr = l.getAttribute( 'data-tag-id' );
				const id = idAttr !== null ? idAttr : getTagIdFromLink( l );
				if ( tagId === '' ) {
					// Mark anchors with empty/absent data-tag-id as active (the "All" pill)
					if ( id === '' || id == null ) {
						l.classList.add( 'is-active' );
					}
				} else if ( id === tagId ) {
					l.classList.add( 'is-active' );
				}
			} );
		} catch ( _ ) {}
		// Also try to sync any other tag bars on the page
		if ( container !== document ) {
			try {
				const all = document.querySelectorAll( '.eventive-tags' );
				all.forEach( function ( cont ) {
					const links = cont.querySelectorAll(
						'a.external-tag-filter, a.eventive-tag, .eventive-tags a'
					);
					links.forEach( function ( l ) {
						l.classList.remove( 'is-active' );
					} );
					links.forEach( function ( l ) {
						const idAttr = l.getAttribute( 'data-tag-id' );
						const id =
							idAttr !== null ? idAttr : getTagIdFromLink( l );
						if ( tagId === '' ) {
							if ( id === '' || id == null ) {
								l.classList.add( 'is-active' );
							}
						} else if ( id === tagId ) {
							l.classList.add( 'is-active' );
						}
					} );
				} );
			} catch ( _ ) {}
		}
	}

	function computeUpcomingTagsForBucket( bucket ) {
		return new Promise( function ( resolve ) {
			if ( ! window.Eventive || typeof Eventive.request !== 'function' ) {
				return resolve( {} );
			}
			Eventive.request( {
				method: 'GET',
				path:
					'event_buckets/' + encodeURIComponent( bucket ) + '/films',
				qs: { marquee: true },
			} )
				.then( function ( res ) {
					const films = ( res && res.films ) || [];
					const map = {};
					films.forEach( function ( f ) {
						// Prefer ids when available, otherwise names
						if ( Array.isArray( f.tags ) ) {
							f.tags.forEach( function ( t ) {
								const id =
									t && ( t.id || t._id || t.ref || t.value );
								const name =
									t && ( t.name || t.title || t.label );
								if ( id ) {
									addToSet( map, String( id ) );
								}
								if ( ! id && name ) {
									addToSet(
										map,
										String( name ).toLowerCase()
									);
								}
							} );
						}
						if ( Array.isArray( f.tag_ids ) ) {
							f.tag_ids.forEach( function ( id ) {
								addToSet( map, String( id ) );
							} );
						}
						if ( Array.isArray( f.tag_names ) ) {
							f.tag_names.forEach( function ( n ) {
								addToSet( map, String( n ).toLowerCase() );
							} );
						}
					} );
					resolve( map );
				} )
				.catch( function () {
					resolve( {} );
				} );
		} );
	}

	function hideEmptyTagsInScope( root ) {
		if ( ! root ) {
			return;
		}
		// Only run when explicitly requested via data attribute on the container or ancestor
		const containers = root.querySelectorAll(
			'.tag-container, .eventive-tags'
		);
		containers.forEach( function ( cont ) {
			if ( cont.__evtTagsFiltered ) {
				return;
			} // run once per container
			let shouldHide = cont.getAttribute( 'data-hide-empty' ) === 'true';
			if ( ! shouldHide ) {
				const anc = findClosestData( cont, [
					'data-hide-empty-tags',
					'data-hide-empty',
				] );
				shouldHide = !! ( anc && anc.value === 'true' );
			}
			if ( ! shouldHide ) {
				return;
			}

			// Check for precomputed available tags on an ancestor (preferred inside events list)
			const availInfo = findClosestData( cont, [
				'data-available-tags',
			] );
			const availableMap = availInfo ? csvToMap( availInfo.value ) : null;

			// Collect all tag ids/names present in the UI
			const tagLinks = cont.querySelectorAll(
				'.eventive-tags a, a.eventive-tag, .external-tag-filter'
			);
			const tagRefs = [];
			tagLinks.forEach( function ( a ) {
				const id =
					a.getAttribute( 'data-tag-id' ) ||
					getTagIdFromLink( a ) ||
					'';
				const name = ( a.textContent || '' ).trim().toLowerCase();
				if ( id ) {
					tagRefs.push( { a, id: String( id ) } );
				} else if ( name ) {
					tagRefs.push( { a, name } );
				}
			} );
			if ( ! tagRefs.length ) {
				return;
			}

			if ( availableMap && Object.keys( availableMap ).length ) {
				tagRefs.forEach( function ( ref ) {
					let present = false;
					if ( ref.id && availableMap[ ref.id ] ) {
						present = true;
					} else if ( ref.name && availableMap[ ref.name ] ) {
						present = true;
					}
					if ( ! present ) {
						const label = ref.a.closest( '.tag-label' ) || ref.a;
						if ( label ) {
							label.style.display = 'none';
						}
					}
				} );
				cont.__evtTagsFiltered = true;
				return; // done; no API call needed
			}

			const bucketInfo = findClosestData( cont, [
				'data-bucket',
				'data-event-bucket',
				'data-bucket-id',
			] );
			const bucket = bucketInfo && bucketInfo.value;
			if ( ! bucket ) {
				return;
			}

			computeUpcomingTagsForBucket( bucket ).then( function ( map ) {
				// If map is empty (API failure), do nothing to avoid hiding everything
				if ( ! map || Object.keys( map ).length === 0 ) {
					cont.__evtTagsFiltered = true;
					return;
				}
				tagRefs.forEach( function ( ref ) {
					let present = false;
					if ( ref.id && map[ ref.id ] ) {
						present = true;
					} else if ( ref.name && map[ ref.name ] ) {
						present = true;
					}
					if ( ! present ) {
						const label = ref.a.closest( '.tag-label' ) || ref.a; // hide the whole pill if possible
						if ( label ) {
							label.style.display = 'none';
						}
					}
				} );
				cont.__evtTagsFiltered = true;
			} );
		} );
	}

	// Debounced hide runner to accommodate late-injected tag UIs
	let __evtHideTimer = null;
	function scheduleHide( root ) {
		if ( __evtHideTimer ) {
			clearTimeout( __evtHideTimer );
		}
		__evtHideTimer = setTimeout( function () {
			hideEmptyTagsInScope( root || document );
		}, 60 );
	}

	// Initialize within a scope (supports Elementor widget refresh)
	function initScope( scope ) {
		const root = scope && scope.nodeType ? scope : document;

		// Guard: only bind once per root
		if ( root.__evtTagsBound ) {
			return;
		}
		root.__evtTagsBound = true;

		// CLICK HANDLER for links (delegated to support dynamic content)
		root.addEventListener(
			'click',
			function ( e ) {
				const t = e.target;
				if ( ! t ) {
					return;
				}
				const a = t.closest
					? t.closest(
							'.eventive-tags a, a.eventive-tag, .external-tag-filter'
					  )
					: null;
				if ( ! a ) {
					return;
				}

				// Allow modifier clicks to open new tab/window
				if (
					e.metaKey ||
					e.ctrlKey ||
					e.shiftKey ||
					e.altKey ||
					a.target === '_blank'
				) {
					return;
				}

				// Unless explicitly opted to navigate
				if ( a.getAttribute( 'data-behavior' ) === 'navigate' ) {
					return;
				}

				const tagId =
					a.getAttribute( 'data-tag-id' ) ||
					getTagIdFromLink( a ) ||
					'';

				// If this is the "All" pill (no tag id), clear the filter in-place
				if ( ! tagId ) {
					e.preventDefault();
					e.stopPropagation();
					clearPluginParams( 'replace', a ); // clear all plugin params and return to base
					dispatchSetActive( '' ); // signal clear
					updateActiveClasses( root, '' );
					// Fallback: if nothing re-rendered within one tick, navigate to canonical reset URL
					setTimeout( function () {
						try {
							const info = findClosestData( a, [
								'data-reset-url',
							] );
							if ( info && info.value ) {
								// Check if any renderer set a known global flag
								if (
									! window.__eventiveLastRenderAt ||
									Date.now() - window.__eventiveLastRenderAt >
										500
								) {
									window.location.assign( info.value );
								}
							}
						} catch ( _ ) {}
					}, 0 );
					return;
				}

				e.preventDefault();
				e.stopPropagation();

				setQueryTagId( tagId, 'push' );
				dispatchSetActive( tagId );
				updateActiveClasses( root, tagId );
			},
			true
		);

		// CHANGE HANDLER for select dropdowns
		root.querySelectorAll( 'select.eventive-tag-select' ).forEach(
			function ( sel ) {
				if ( sel.__evtTagsBound ) {
					return;
				}
				sel.__evtTagsBound = true;
				sel.addEventListener( 'change', function () {
					const opt = sel.options[ sel.selectedIndex ];
					const tagId =
						( opt &&
							( opt.getAttribute( 'data-tag-id' ) ||
								opt.value ) ) ||
						'';
					if ( ! tagId ) {
						clearPluginParams( 'replace', sel );
						dispatchSetActive( '' );
						updateActiveClasses( root, '' );
						try {
							document.dispatchEvent(
								new CustomEvent( 'eventive:urlChanged', {
									detail: {
										tagId: '',
										url: window.location.href,
									},
								} )
							);
						} catch ( _ ) {}
						setTimeout( function () {
							try {
								const info = findClosestData( sel, [
									'data-reset-url',
								] );
								if ( info && info.value ) {
									if (
										! window.__eventiveLastRenderAt ||
										Date.now() -
											window.__eventiveLastRenderAt >
											500
									) {
										window.location.assign( info.value );
									}
								}
							} catch ( _ ) {}
						}, 0 );
						return;
					}
					setQueryTagId( tagId, 'push' );
					dispatchSetActive( tagId );
					updateActiveClasses( root, tagId );
				} );
			}
		);

		// INITIALIZE from URL on first run
		if ( ! INITED ) {
			INITED = true;
			const initTag = ( function () {
				try {
					const u = new URL( window.location.href );
					const v = u.searchParams.get( 'tag-id' );
					return v === null || v === '' ? '' : v;
				} catch ( _ ) {
					return '';
				}
			} )();
			if ( initTag ) {
				// Sync form controls + classes without scrolling or reload
				dispatchSetActive( initTag );
				updateActiveClasses( root, initTag );
				setQueryTagId( initTag, 'replace' );
			} else {
				// No tag in URL → ensure "All" pill appears active
				updateActiveClasses( root, '' );
				// Also normalize URL by clearing stray plugin params on first load with no tag
				clearPluginParams( 'replace', root );
			}
		}

		// Optionally hide tags that have no upcoming items (opt-in via data-hide-empty="true")
		scheduleHide( root );
	}

	// DOM ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener(
			'DOMContentLoaded',
			function () {
				initScope( document );
			},
			{ once: true }
		);
	} else {
		initScope( document );
	}

	// Secondary pass after full load in case tags inject late
	window.addEventListener( 'load', function () {
		scheduleHide( document );
	} );

	// Observe DOM for injected tag containers and re-run hiding
	try {
		const mo = new MutationObserver( function ( muts ) {
			let shouldRun = false;
			for ( let i = 0; i < muts.length; i++ ) {
				const m = muts[ i ];
				if ( m.type === 'childList' ) {
					// If any added node contains a tag container, re-run
					m.addedNodes &&
						m.addedNodes.forEach &&
						m.addedNodes.forEach( function ( n ) {
							if ( ! n || ! n.querySelector ) {
								return;
							}
							if (
								n.matches &&
								n.matches( '.tag-container, .eventive-tags' )
							) {
								shouldRun = true;
							} else if (
								n.querySelector(
									'.tag-container, .eventive-tags'
								)
							) {
								shouldRun = true;
							}
						} );
				} else if ( m.type === 'attributes' ) {
					if (
						m.target &&
						m.target.matches &&
						m.target.matches( '.tag-container, .eventive-tags' )
					) {
						shouldRun = true;
					}
				}
			}
			if ( shouldRun ) {
				scheduleHide( document );
			}
		} );
		mo.observe( document.documentElement, {
			subtree: true,
			childList: true,
			attributes: true,
			attributeFilter: [ 'data-hide-empty', 'data-available-tags' ],
		} );
	} catch ( _ ) {}

	// Components can signal when they have rendered to avoid fallback navigation
	document.addEventListener( 'eventive:rendered', function () {
		window.__eventiveLastRenderAt = Date.now();
	} );

	// React to history navigation so lists re-render on back/forward
	if ( ! document.__evtTagsPopstateBound ) {
		document.__evtTagsPopstateBound = true;
		window.addEventListener( 'popstate', function () {
			try {
				const u = new URL( window.location.href );
				const tid = u.searchParams.get( 'tag-id' ) || '';
				dispatchSetActive( tid );
				updateActiveClasses( document, tid );
			} catch ( _ ) {}
		} );
	}

	// Elementor live preview re-renders
	if ( window.jQuery && window.elementorFrontend ) {
		jQuery( window ).on( 'elementor/frontend/init', function () {
			try {
				elementorFrontend.hooks.addAction(
					'frontend/element_ready/shortcode.default',
					function ( scope ) {
						if ( scope && scope[ 0 ] ) {
							initScope( scope[ 0 ] );
							scheduleHide( scope[ 0 ] );
						}
					}
				);
			} catch ( _ ) {}
		} );
	}
} )();
