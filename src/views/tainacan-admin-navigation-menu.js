function handleMenuCollapses() {
    const tainacanRootMenu = document.getElementById('tainacan-root-menu');

    if ( tainacanRootMenu && tainacanRootMenu.childNodes.length ) {

        const tainacanRootMenuItems = tainacanRootMenu.childNodes;

        tainacanRootMenuItems.forEach( item => {
            if ( item.classList && item.classList.contains('menu-item-has-children') ) {
                const itemButton = item.querySelector( 'button' );

                itemButton.addEventListener( 'click', function() {
                    item.classList.toggle('is-open');

                    const hasOpenItem = item.classList.contains('is-open');
                    itemButton.setAttribute( 'aria-expanded', hasOpenItem);

                    if ( hasOpenItem ) {
                        tainacanRootMenuItems.forEach( otherItem => {
                            if ( otherItem !== item && otherItem.classList && otherItem.classList.contains('menu-item-has-children') ) {
                                const otherItemButton = otherItem.querySelector( 'button' );
                                otherItem.classList.remove('is-open');
                                otherItemButton.setAttribute( 'aria-expanded', false );
                            }
                        });
                    }
                } );
            }
        } );

        const backdrops = document.querySelectorAll('.menu-backdrop');

        backdrops.forEach( backdrop => {
            backdrop.addEventListener( 'click', function() {
                
                tainacanRootMenuItems.forEach( item => {
                    if ( item.classList && item.classList.contains('menu-item-has-children') ) {
                        const itemButton = item.querySelector( 'button' );
                        item.classList.remove('is-open');
                        itemButton.setAttribute( 'aria-expanded', false );
                    }
                });
            });
        } );
    }
}
function handleDynamicMenusAndBreadcrumbs() {

    if ( wp && wp.hooks ) {
        const tainacanRepositoryLinks = document.getElementById( 'tainacan_admin' );
        const tainacanCollectionLinks = document.getElementById( 'tainacan_collection_links' );
        const tainacanBreadcrumbsList = document.getElementById( 'tainacan-breadcrumbs-list' );

        if ( tainacanRepositoryLinks && tainacanCollectionLinks ) {

            let originalCollectionLevelLinks = [];
            tainacanCollectionLinks.childNodes.forEach(element => originalCollectionLevelLinks.push(element.cloneNode(true)) );

            wp.hooks.addAction( 'tainacan_navigation_path_updated',
                'tainacan_admin_navigation_menu',
                function( {
                    currentRoute,
                    adminOptions,
                    parentEntity,
                    childEntity,
                    collection
                }) {

                    const repositoryLinkElements = tainacanRepositoryLinks.querySelectorAll( 'a' );
                    let isSomeRepositoryLinkActive = false;
                    repositoryLinkElements.forEach( repositoryLinkElement => {
                        const currentLinkHash = repositoryLinkElement.href.split('#');

                        if (
                            currentLinkHash[1] &&
                            !currentRoute.collectionId && 
                            currentRoute.path != '/collections' &&
                            currentRoute.path != '/items' &&
                            currentRoute.path != '/my-items' &&
                            currentRoute.path.indexOf( currentLinkHash[1] ) == 0
                        ) {
                            repositoryLinkElement.setAttribute('aria-current', 'page');
                            isSomeRepositoryLinkActive = true;
                        } else {
                            repositoryLinkElement.removeAttribute('aria-current');
                        }
                    });

                    if ( isSomeRepositoryLinkActive ) {
                        tainacanRepositoryLinks.parentNode.classList.add('is-open');
                        tainacanCollectionLinks.parentNode.classList.remove('is-open');
                    }

                    const collectionLinkElements = tainacanCollectionLinks.querySelectorAll( 'a' );
                    let isSomeCollectionLinkActive = false;
                    collectionLinkElements.forEach( collectionLinkElement => {
                        const currentLinkHash = collectionLinkElement.href.split('#');
                        if (
                            (
                                currentLinkHash[1] &&
                                currentRoute.params.collectionId &&
                                currentRoute.meta.menuLink && 
                                currentRoute.path.indexOf( currentLinkHash[1] + '/' + currentRoute.params.collectionId + '/' + currentRoute.meta.menuLink ) == 0
                            ) ||
                            (
                                currentLinkHash[1] &&
                                currentLinkHash[1].split('?')[0] == currentRoute.path
                            )
                        ) {
                            collectionLinkElement.setAttribute('aria-current', 'page');
                            isSomeCollectionLinkActive = true;
                        } else {
                            collectionLinkElement.removeAttribute('aria-current');
                        }
                    });
                    
                    if ( isSomeCollectionLinkActive ) {
                        tainacanRepositoryLinks.parentNode.classList.remove('is-open');
                        tainacanCollectionLinks.parentNode.classList.add('is-open');
                    }
                    
                    if ( currentRoute.params.collectionId && collection ) {

                        while (tainacanCollectionLinks.lastChild)
                            tainacanCollectionLinks.removeChild(tainacanCollectionLinks.lastChild);

                        const collectionLinks = [
                            {
                                id: 'collections',
                                label: wp.i18n.__( 'Collections list', 'tainacan'),
                                icon: 'pointer tainacan-icon-rotate-180',
                                href: 'collections/',
                                hide: adminOptions.hidePrimaryMenuCollectionsButton === 'true' || adminOptions.hidePrimaryMenuCollectionsButton === true
                            },
                            {
                                id: 'items',
                                label: wp.i18n.__( 'Collection items', 'tainacan'),
                                icon: 'items',
                                href: `collections/${currentRoute.params.collectionId}/items`,
                                hide: adminOptions.hidePrimaryMenuItemsButton === 'true' || adminOptions.hidePrimaryMenuItemsButton === true
                            },
                            {
                                id: 'my-items',
                                label: wp.i18n.__( 'My items', 'tainacan'),
                                href: `collections/${currentRoute.params.collectionId}/my-items?authorid=` + tainacan_user.data.ID,
                                hide: adminOptions.hidePrimaryMenuItemsButton === 'true' || adminOptions.hidePrimaryMenuItemsButton === true
                            },
                            {
                                id: 'settings',
                                label: wp.i18n.__( 'Settings', 'tainacan'),
                                icon: 'settings',
                                href: `collections/${currentRoute.params.collectionId}/settings`,
                                hide: !collection.current_user_can_edit
                            },
                            {
                                id: 'metadata',
                                label: wp.i18n.__( 'Metadata', 'tainacan'),
                                icon: 'metadata',
                                href: `collections/${currentRoute.params.collectionId}/metadata`,
                                hide: !collection.current_user_can_edit_metadata
                            },
                            {
                                id: 'filters',
                                label: wp.i18n.__( 'Filters', 'tainacan'),
                                icon: 'filters',
                                href: `collections/${currentRoute.params.collectionId}/filters`,
                                hide: !collection.current_user_can_edit_filters
                            },
                            {
                                id: 'activities',
                                label: wp.i18n.__( 'Activities', 'tainacan'),
                                icon: 'activities',
                                href: `collections/${currentRoute.params.collectionId}/activities`,
                                hide: !tainacan_user.caps['tnc_rep_read_logs']
                            },
                            {
                                id: 'capabilities',
                                label: wp.i18n.__( 'Capabilities', 'tainacan'),
                                icon: 'capability',
                                href: `collections/${currentRoute.params.collectionId}/capabilities`,
                                hide: !collection.current_user_can_edit_users
                            },
                            {
                                id: 'reports',
                                label: wp.i18n.__( 'Reports', 'tainacan'),
                                svgIcon: `<svg fill="currentColor" width="1em" height="1em" viewBox="0 0 16 16" version="1.1" xmlns:svg="http://www.w3.org/2000/svg">
                                    <path d="m 10.1,9.75 h 1.05 c 0.175,0 0.35,-0.175 0.35,-0.35 V 5.725 c 0,-0.175 -0.175,-0.35 -0.35,-0.35 H 10.1 c -0.175,0 -0.35,0.175 -0.35,0.35 V 9.4 c 0,0.175 0.175,0.35 0.35,0.35 z m 2.625,0 h 1.05 c 0.175,0 0.35,-0.175 0.35,-0.35 V 3.1 c 0,-0.175 -0.175,-0.35 -0.35,-0.35 h -1.05 c -0.175,0 -0.35,0.175 -0.35,0.35 v 6.3 c 0,0.175 0.175,0.35 0.35,0.35 z m -7.875,0 H 5.9 C 6.075,9.75 6.25,9.575 6.25,9.4 V 7.475 C 6.25,7.3 6.075,7.125 5.9,7.125 H 4.85 C 4.675,7.125 4.5,7.3 4.5,7.475 V 9.4 c 0,0.175 0.175,0.35 0.35,0.35 z m 2.625,0 h 1.05 C 8.7,9.75 8.875,9.575 8.875,9.4 V 3.975 C 8.875,3.8 8.7,3.625 8.525,3.625 H 7.475 C 7.3,3.625 7.125,3.8 7.125,3.975 V 9.4 c 0,0.175 0.175,0.35 0.35,0.35 z m 7.0875,1.75 H 2.75 V 3.1875 C 2.75,2.9457813 2.5542187,2.75 2.3125,2.75 H 1.4375 C 1.1957812,2.75 1,2.9457813 1,3.1875 v 9.1875 c 0,0.483175 0.3918355,0.875 0.875,0.875 H 14.5625 C 14.804175,13.25 15,13.054175 15,12.8125 v -0.875 C 15,11.695825 14.804175,11.5 14.5625,11.5 Z" />
                                </svg>`,
                                href: `collections/${currentRoute.params.collectionId}/reports`,
                                hide: !collection.current_user_can_read_private_items
                            }
                        ];
                        collectionLinks.forEach(element => {

                            if (element.hide)
                                return;

                            const listItem = document.createElement( 'li' );
                            const link = document.createElement( 'a' );
                            
                            link.setAttribute( 'href', document.location.pathname + document.location.search + '#' + element.href );
                            link.innerHTML = '';
                            
                            if ( element.icon )
                                link.innerHTML += '<span class="icon"><i class="tainacan-icon tainacan-icon-' + element.icon + '"></i></span>';
                            else if ( element.svgIcon )
                                link.innerHTML += '<span class="icon"><i class="tainacan-icon tainacan-icon-svg">' + element.svgIcon + '</i></span>';

                            link.innerHTML += '<span class="menu-text">' + element.label + '</span>';

                            if ( element.id === currentRoute.meta.menuLink ) 
                                link.setAttribute('aria-current', 'page');

                            listItem.setAttribute( 'id', element.id );
                            listItem.appendChild( link );
                            
                            tainacanCollectionLinks.appendChild( listItem );
                        });

                        // Inserts the collection name as the second item in the collection level menu
                        const collectionName = document.createElement( 'li' );
                        collectionName.setAttribute( 'class', 'separator-list-item' );
                        collectionName.innerHTML = '<span class="icon"><i class="tainacan-icon tainacan-icon-collection tainacan-icon-1-125em"></i></span><span class="menu-text">' + collection.name + '</span>';
                        tainacanCollectionLinks.insertBefore( collectionName, tainacanCollectionLinks.children[1] );

                    } else {
                        
                        while (tainacanCollectionLinks.lastChild)
                            tainacanCollectionLinks.removeChild(tainacanCollectionLinks.lastChild);

                        originalCollectionLevelLinks.forEach(element => tainacanCollectionLinks.appendChild( element ) );
                    }

                    // Updates breadcrumbs
                    if ( tainacanBreadcrumbsList && currentRoute.meta && currentRoute.meta.title ) {

                        // First, we clear the dynamic collection elements from the breadcrumbs list
                        const dynamicBreadcrumbs = tainacanBreadcrumbsList.querySelectorAll( '.dynamic-breadcrumb' );
                        dynamicBreadcrumbs.forEach( element => tainacanBreadcrumbsList.removeChild( element ) );

                        // Helper function to add a breadcrumb
                        const addBreadcrumb = (label, link, isCurrent = false) => {
                            const breadcrumb = document.createElement('li');
                            breadcrumb.classList.add('dynamic-breadcrumb');
                            breadcrumb.innerHTML = isCurrent
                                ? `<span class="menu-text">${label}</span>`
                                : `<a href="${document.location.pathname + document.location.search}#${link}">${label}</a>`;
                            tainacanBreadcrumbsList.appendChild(breadcrumb);
                        };

                        // Add parent entity breadcrumb
                        if (parentEntity) {
                            if ( parentEntity.label && parentEntity.rootLink )
                                addBreadcrumb(parentEntity.label, parentEntity.rootLink);
                            if ( parentEntity.name && parentEntity.defaultLink )
                                addBreadcrumb(parentEntity.name, parentEntity.defaultLink);
                        }

                        // Add child entity breadcrumb (if applicable)
                        if (childEntity) {
                            if ( childEntity.label && childEntity.rootLink )
                                addBreadcrumb(childEntity.label, childEntity.rootLink);
                            if ( childEntity.name && childEntity.defaultLink )
                                addBreadcrumb(childEntity.name, childEntity.defaultLink);
                        }

                        // Add current subpage breadcrumb
                        const currentSubpageLabel = currentRoute.meta.title + (parentEntity && parentEntity.name && !childEntity ? ` ${parentEntity.name}` : '');
                        addBreadcrumb(currentSubpageLabel, '', true);

                    }
                }
            );
        }
    }
}

/**
 * Handle UI Tweak buttons such as menu collapsing and fullscreen mode
 */
function handleUITweakButtons() {

    const tainacanMenuToggler = document.getElementById('tainacan-menu-toggler');
    const tainacanMenuCollapser = document.getElementById('tainacan-menu-collapser');
    const tainacanFullscreenToggler = document.getElementById('tainacan-fullscreen-toggler');
    const tainacanAdminMenu = document.getElementById('tainacan-navigation-menu');

    if ( tainacanMenuToggler && tainacanMenuCollapser && tainacanAdminMenu ) {

        tainacanMenuToggler.addEventListener( 'click', function() {

            const isToggled = tainacanAdminMenu.classList.contains('is-active');

            tainacanAdminMenu.classList.toggle('is-active');
            tainacanMenuToggler.ariaPressed = '' + isToggled;

        } );

        tainacanMenuCollapser.addEventListener( 'click', function() {

            const isCollapsed = !tainacanAdminMenu.classList.contains('is-collapsed');

            tainacanAdminMenu.classList.toggle('is-collapsed');
            tainacanMenuCollapser.ariaPressed = '' + isCollapsed;

            let currentUserPrefs = JSON.parse(tainacan_user.prefs);
            currentUserPrefs['is_navigation_sidebar_collapsed'] = isCollapsed;

            let data = { 'meta': { 'tainacan_prefs': JSON.stringify(currentUserPrefs) } };

            if ( tainacan_user.nonce && tainacan_user.wp_api_url ) {
            
                fetch(tainacan_user.wp_api_url + 'users/me', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': tainacan_user.nonce
                    },
                    body: JSON.stringify(data)
                }).then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                }).catch(error => {
                    console.error('Request to /users/me failed. Maybe you\'re not logged in.', error);
                });
            }

        } );
    }

    if ( tainacanFullscreenToggler ) {

        tainacanFullscreenToggler.addEventListener( 'click', function() {

            const isFullscreen = !document.body.classList.contains('tainacan-pages-container--fullscreen');

            document.body.classList.toggle('tainacan-pages-container--fullscreen');
            tainacanFullscreenToggler.ariaPressed = '' + isFullscreen;

            // Emits fullscreen mode change event so that inner components such as Masonry can react to layout change
            const fullscreenEvent = new CustomEvent('tainacan_fullscreen_mode_change', {
                detail: { isFullscreen: isFullscreen }
            });
            document.dispatchEvent(fullscreenEvent);

            let currentUserPrefs = JSON.parse(tainacan_user.prefs);
            currentUserPrefs['is_fullscreen'] = isFullscreen;

            let data = { 'meta': { 'tainacan_prefs': JSON.stringify(currentUserPrefs) } };

            if ( tainacan_user.nonce && tainacan_user.wp_api_url ) {
            
                fetch(tainacan_user.wp_api_url + 'users/me', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': tainacan_user.nonce
                    },
                    body: JSON.stringify(data)
                }).then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                }).catch(error => {
                    console.error('Request to /users/me failed. Maybe you\'re not logged in.', error);
                });
            }

        } );
    }
}

handleMenuCollapses();
handleDynamicMenusAndBreadcrumbs();
handleUITweakButtons();