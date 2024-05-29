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
        
        if ( tainacanRepositoryLinks && tainacanCollectionLinks ) {

            let originalCollectionLevelLinks = [];
            tainacanCollectionLinks.childNodes.forEach(element => originalCollectionLevelLinks.push(element.cloneNode(true)) );

            wp.hooks.addAction( 'tainacan_navigation_path_updated', 'tainacan_admin_navigation_menu', function( currentRoute ) {

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
                    console.log( '/collections/' + currentRoute.params.collectionId + '/' + currentLinkHash[1] , currentRoute.path)
                    if ( currentLinkHash[1] && currentRoute.params.collectionId && ( '/collections/' + currentRoute.params.collectionId + '/' + currentLinkHash[1] ) == currentRoute.path ) {
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
            
                if ( currentRoute.params.collectionId ) {

                    while (tainacanCollectionLinks.lastChild)
                        tainacanCollectionLinks.removeChild(tainacanCollectionLinks.lastChild);

                    const collectionLinks = [
                        { id: 'collections', label: wp.i18n.__( 'Collections list', 'tainacan'), href: 'collections/' },
                        { id: 'items', label: wp.i18n.__( 'Items', 'tainacan'), href: `collections/${currentRoute.params.collectionId}/items` },
                        { id: 'settings', label: wp.i18n.__( 'Settings', 'tainacan'), href: `collections/${currentRoute.params.collectionId}/settings` },
                        { id: 'metadata', label: wp.i18n.__( 'Metadata', 'tainacan'), href: `collections/${currentRoute.params.collectionId}/metadata` },
                        { id: 'filters', label: wp.i18n.__( 'Filters', 'tainacan'), href: `collections/${currentRoute.params.collectionId}/filters` },
                        { id: 'activities', label: wp.i18n.__( 'Activities', 'tainacan'), href: `collections/${currentRoute.params.collectionId}/activities` },
                        { id: 'capabilities', label: wp.i18n.__( 'Capabilities', 'tainacan'), href: `collections/${currentRoute.params.collectionId}/capabilities` },    
                    ];
                    collectionLinks.forEach(element => {
                        const listItem = document.createElement( 'li' );
                        const link = document.createElement( 'a' );
                        
                        link.setAttribute( 'href', tainacan_plugin.admin_url + '?page=tainacan_admin#' + element.href );
                        link.innerText = element.label;

                        listItem.setAttribute( 'id', element.id );
                        listItem.appendChild( link );

                        tainacanCollectionLinks.appendChild( listItem );
                    });
                } else {
                    
                    while (tainacanCollectionLinks.lastChild)
                        tainacanCollectionLinks.removeChild(tainacanCollectionLinks.lastChild);

                    originalCollectionLevelLinks.forEach(element => tainacanCollectionLinks.appendChild( element ) );
                }
            } );
        }
    }
}

handleMenuCollapses();
handleCollectionLevelDynamicMenu();