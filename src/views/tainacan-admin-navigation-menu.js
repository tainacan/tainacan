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
                    collection,
                    item,
                    taxonomy,
                    importer
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
                                currentLinkHash[1] == currentRoute.path
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
                            }
                        ];
                        collectionLinks.forEach(element => {

                            if (element.hide)
                                return;

                            const listItem = document.createElement( 'li' );
                            const link = document.createElement( 'a' );
                            
                            link.setAttribute( 'href', document.location.pathname + document.location.search + '#' + element.href );
                            link.innerHTML = '<span class="icon"><i class="tainacan-icon tainacan-icon-' + element.icon + '"></i></span><span class="menu-text">' + element.label + '</span>';

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

                        if ( currentRoute.params.collectionId && collection ) {

                            // Adds collections link
                            const collectionsLink = document.createElement( 'li' );
                            collectionsLink.classList.add('dynamic-breadcrumb');
                            collectionsLink.innerHTML = '<a href="' + document.location.pathname + document.location.search + '#collections">' + wp.i18n.__( 'Collections', 'tainacan') + '</a>';
                            tainacanBreadcrumbsList.appendChild( collectionsLink );

                            // Adds collection link
                            const collectionLink = document.createElement( 'li' );
                            collectionLink.classList.add('dynamic-breadcrumb');
                            collectionLink.innerHTML = '<a aria-current="page" href="' + document.location.pathname + document.location.search + '#collections/' + currentRoute.params.collectionId + '/items">' + collection.name + '</a>';
                            tainacanBreadcrumbsList.appendChild( collectionLink );
                        }

                        if ( currentRoute.params.itemId && item && item.title ) {
                            
                            // Adds items link
                            const itemsLink = document.createElement( 'li' );
                            itemsLink.classList.add('dynamic-breadcrumb');
                            itemsLink.innerHTML = '<a href="' + document.location.pathname + document.location.search + '#collections/' + currentRoute.params.collectionId + '/items">' + wp.i18n.__( 'Items', 'tainacan') + '</a>';
                            tainacanBreadcrumbsList.appendChild( itemsLink );

                            // Adds item link
                            const itemLink = document.createElement( 'li' );
                            itemLink.classList.add('dynamic-breadcrumb');
                            itemLink.innerHTML = '<a aria-current="page" href="' + document.location.pathname + document.location.search + '#collections/' + currentRoute.params.collectionId + '/items/' + currentRoute.params.itemId + '">' + item.title + '</a>';
                            tainacanBreadcrumbsList.appendChild( itemLink );
                        }
                        
                        if ( currentRoute.params.taxonomyId && taxonomy ) {

                            // Adds taxonomies link
                            const taxonomiesLink = document.createElement( 'li' );
                            taxonomiesLink.classList.add('dynamic-breadcrumb');
                            taxonomiesLink.innerHTML = '<a href="' + document.location.pathname + document.location.search + '#taxonomies">' + wp.i18n.__( 'Taxonomies', 'tainacan') + '</a>';
                            tainacanBreadcrumbsList.appendChild( taxonomiesLink );

                            // Adds taxonomy link
                            const taxonomyLink = document.createElement( 'li' );
                            taxonomyLink.classList.add('dynamic-breadcrumb');
                            taxonomyLink.innerHTML = '<a aria-current="page" href="' + document.location.pathname + document.location.search + '#taxonomies/' + currentRoute.params.taxonomyId + '/edit">' + taxonomy.name + '</a>';
                            tainacanBreadcrumbsList.appendChild( taxonomyLink );
                        }
                        
                        if ( currentRoute.params.importerSlug && importer ) {

                            // Adds importers link
                            const importersLink = document.createElement( 'li' );
                            importersLink.classList.add('dynamic-breadcrumb');
                            importersLink.innerHTML = '<a href="' + document.location.pathname + document.location.search + '#importers">' + wp.i18n.__( 'Importers', 'tainacan') + '</a>';
                            tainacanBreadcrumbsList.appendChild( importersLink );

                            // Adds importer link
                            const importerLink = document.createElement( 'li' );
                            importerLink.classList.add('dynamic-breadcrumb');
                            importerLink.innerHTML = '<a aria-current="page" href="' + document.location.pathname + document.location.search + '#importers/' + currentRoute.params.importerSlug + '">' + importer + '</a>';
                            tainacanBreadcrumbsList.appendChild( importerLink );

                            // Adds importer mapping link
                            if ( currentRoute.params.sessionId && currentRoute.params.collectionId ) {
                                const importerMappingLink = document.createElement( 'li' );
                                importerMappingLink.classList.add('dynamic-breadcrumb');
                                importerMappingLink.innerHTML = '<a aria-current="page" href="' + document.location.pathname + document.location.search + '#importers/' + currentRoute.params.importerSlug + '/' + currentRoute.params.sessionId + '/mapping/' + currentRoute.params.collectionId + '">' + currentRoute.params.sessionId + '</a>';
                                tainacanBreadcrumbsList.appendChild( importerMappingLink );
                            }
                        }

                        // Adds current subpage
                        const breadcrumbItem = document.createElement( 'li' );
                        breadcrumbItem.classList.add('dynamic-breadcrumb');
                        breadcrumbItem.innerHTML = '<span class="menu-text">' + currentRoute.meta.title + '</span>';

                        tainacanBreadcrumbsList.appendChild( breadcrumbItem );
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

            const isCollapsed = tainacanAdminMenu.classList.contains('is-collapsed');

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