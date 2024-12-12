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
    }
}
function handleCollectionLevelDynamicMenu() {

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
                    item
                }) {

                    const repositoryLinkElements = tainacanRepositoryLinks.querySelectorAll( 'a' );
                    let isSomeRepositoryLinkActive = false;
                    repositoryLinkElements.forEach( repositoryLinkElement => {
                        const currentLinkHash = repositoryLinkElement.href.split('#');
                        if ( currentLinkHash[1] && currentLinkHash[1] == currentRoute.path ) {
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
                                hide: !tainacan_plugin.user_caps['tnc_rep_read_logs']
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
                    if ( currentRoute.meta && currentRoute.meta.title ) {

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

handleMenuCollapses();
handleCollectionLevelDynamicMenu();