import tainacan from '../../api-client/axios.js';
import axios from 'axios';

const { __ } = wp.i18n;

const { TextControl, Button, Modal, CheckboxControl, RadioControl, Spinner } = wp.components;

export default class ItemsModal extends React.Component {
    constructor(props) {
        super(props);

        // Initialize state
        this.state = {
            modalItems: [],
            totalModalItems: 0,
            itemsPerPage: 24,
            searchItemName: '',
            temporarySelectedItems: [],
            items: [],
            isLoadingItems: false,
            itemsRequestSource: undefined,
            collectionId: undefined,  
            collectionName: '', 
            isLoadingCollections: false, 
            modalCollections: [],
            totalModalCollections: 0, 
            collectionPage: 1,
            itemsPage: 1,
            temporaryCollectionId: '',
            searchCollectionName: '',
            collections: [],
            collectionsRequestSource: undefined,
        };
        
        // Bind events
        this.fetchItems = this.fetchItems.bind(this);
        this.fetchModalItems = this.fetchModalItems.bind(this);
        this.isTemporaryItemSelected = this.isTemporaryItemSelected.bind(this);
        this.toggleSelectTemporaryItem = this.toggleSelectTemporaryItem.bind(this);
        this.selectTemporaryItem = this.selectTemporaryItem.bind(this);
        this.removeTemporaryItemOfId = this.removeTemporaryItemOfId.bind(this);
        this.applySelectedItems = this.applySelectedItems.bind(this);
        this.resetCollections = this.resetCollections.bind(this);
        this.selectCollection = this.selectCollection.bind(this);
        this.fetchCollections = this.fetchCollections.bind(this);
        this.fetchModalCollections = this.fetchModalCollections.bind(this);
        this.fetchCollection = this.fetchCollection.bind(this);
    }

    componentWillMount() {
        
        this.setState({ 
            collectionId: this.props.existingCollectionId,
            temporarySelectedItems: JSON.parse(JSON.stringify(this.props.selectedItemsObject))
        });

        if (this.props.existingCollectionId != null && this.props.existingCollectionId != undefined) {
            this.fetchCollection(this.props.existingCollectionId);
            this.fetchModalItems(this.props.existingCollectionId);
        } else {
            this.setState({ collectionPage: 1 });
            this.fetchModalCollections();
        }
    }

    // ITEMS RELATED --------------------------------------------------
    selectTemporaryItem(item) {
        let existingItemIndex = this.state.temporarySelectedItems.findIndex((existingItem) => (existingItem.id == 'item-id-' + item.id) || (existingItem.id == item.id));

        if (existingItemIndex < 0) {
            let itemId = isNaN(item.id) ? item.id : 'item-id-' + item.id;
            let aTemporarySelectedItems = this.state.temporarySelectedItems;
            aTemporarySelectedItems.push({
                id: itemId,
                title: item.title,
                url: item.url,
                thumbnail: item.thumbnail
            });
            this.setState({ temporarySelectedItems: aTemporarySelectedItems });
        }
    }

    removeTemporaryItemOfId(itemId) {

        let existingItemIndex = this.state.temporarySelectedItems.findIndex((existingItem) => ((existingItem.id == 'item-id-' + itemId) || (existingItem.id == itemId)));

        if (existingItemIndex >= 0) {

            let aTemporarySelectedItems = this.state.temporarySelectedItems;
            aTemporarySelectedItems.splice(existingItemIndex, 1);
            this.setState({ temporarySelectedItems: aTemporarySelectedItems });
        }
    }

    applySelectedItems() {
        let aSelectedItemsObject = JSON.parse(JSON.stringify(this.state.temporarySelectedItems));
        this.props.onApplySelection(aSelectedItemsObject);
    }

    cancelSelection() {

        this.setState({
            modalItems: [],
            modalCollections: []
        });

        this.props.onCancelSelection();
    }

    isTemporaryItemSelected(itemId) {
        return this.state.temporarySelectedItems.findIndex(item => (item.id == itemId) || (item.id == 'item-id-' + itemId)) >= 0;
    }

    toggleSelectTemporaryItem(item, isChecked) {
        if (isChecked)
            this.selectTemporaryItem(item);
        else
            this.removeTemporaryItemOfId(item.id);
        
        this.setState({ temporarySelectedItems: this.state.temporarySelectedItems });
        // setContent();
    }

    fetchItems(name) {

        if (this.state.itemsRequestSource != undefined)
            this.state.itemsRequestSource.cancel('Previous items search canceled.');

        let anItemsRequestSource = axios.CancelToken.source();

        let endpoint = '/collection/'+ this.state.collectionId + '/items/?orderby=title&order=asc&fetch_only=title,thumbnail&perpage=' + this.state.itemsPerPage;

        if (name != undefined && name != '')
            endpoint += '&search=' + name;

        tainacan.get(endpoint, { cancelToken: anItemsRequestSource.token })
            .then(response => {

                let someItems = response.data.items.map((item) => ({ 
                    title: item.title, 
                    id: item.id,
                    url: item.url,
                    thumbnail: [{
                        src: item.thumbnail['tainacan-medium'] != undefined ? item.thumbnail['tainacan-medium'][0] : item.thumbnail['medium'][0],
                        alt: item.title
                    }]
                }));

                this.setState({ 
                    isLoadingItems: false, 
                    items: someItems
                });
                
                return someItems;
            })
            .catch(error => {
                console.log('Error trying to fetch items: ' + error);
            });
    }

    fetchModalItems(collectionId) {
        let someModalItems = this.state.modalItems;

        if (this.state.itemsPage <= 1)
            someModalItems = [];

        let endpoint = '/collection/'+ collectionId + '/items/?orderby=title&order=asc&fetch_only=title,thumbnail&perpage=' + this.state.itemsPerPage + '&paged=' + this.state.itemsPage;

        this.setState({ 
            isLoadingItems: true, 
            modalItems: someModalItems,
            itemsPage: this.state.itemsPage + 1
        });

        tainacan.get(endpoint)
            .then(response => {

                someModalItems = this.state.modalItems;
                for (let item of response.data.items) {
                    someModalItems.push({ 
                        title: item.title, 
                        id: item.id,
                        url: item.url,
                        thumbnail: [{
                            src: item.thumbnail['tainacan-medium'] != undefined ? item.thumbnail['tainacan-medium'][0] : item.thumbnail['medium'][0],
                            alt: item.title
                        }]
                    });
                }
                
                this.setState({ 
                    isLoadingItems: false, 
                    modalItems: someModalItems,
                    totalModalItems: response.headers['x-wp-total']
                });
                
                return someModalItems;
            })
            .catch(error => {
                console.log('Error trying to fetch items: ' + error);
            });
    }

    // COLLECTIONS RELATED --------------------------------------------------
    fetchModalCollections() {

        let someModalCollections = this.state.modalCollections;
        if (this.state.collectionPage <= 1)
            someModalCollections = [];

        let endpoint = '/collections/?orderby=title&order=asc&perpage=' + this.state.itemsPerPage + '&paged=' + this.state.collectionPage;

        this.setState({ 
            isLoadingCollections: true,
            collectionPage: this.state.collectionPage + 1, 
            modalCollections: someModalCollections
        });

        tainacan.get(endpoint)
            .then(response => {

                let otherModalCollections = this.state.modalCollections;
                for (let collection of response.data) {
                    otherModalCollections.push({ 
                        name: collection.name, 
                        id: collection.id
                    });
                }

                this.setState({ 
                    isLoadingCollections: false, 
                    modalCollections: otherModalCollections,
                    totalModalCollections: response.headers['x-wp-total']
                });
            
                return otherModalCollections;
            })
            .catch(error => {
                console.log('Error trying to fetch collections: ' + error);
            });
    }

    fetchCollection(collectionId) {
        tainacan.get('/collections/' + collectionId)
            .then((response) => {
                this.setState({ collectionName: response.data.name });
            }).catch(error => {
                console.log('Error trying to fetch collection: ' + error);
            });
    }

    selectCollection(selectedCollectionId) {
        this.setState({
            collectionId: selectedCollectionId
        });
        this.props.onSelectCollection(selectedCollectionId);
        this.fetchCollection(selectedCollectionId);
        this.fetchModalItems(selectedCollectionId);
    }

    fetchCollections(name) {

        if (this.state.collectionsRequestSource != undefined)
            this.state.collectionsRequestSource.cancel('Previous collections search canceled.');

        let aCollectionRequestSource = axios.CancelToken.source();

        this.setState({ 
            collectionsRequestSource: aCollectionRequestSource,
            isLoadingCollections: true, 
            collections: [],
            items: []
        });

        let endpoint = '/collections/?orderby=title&order=asc&perpage=' + this.state.itemsPerPage;
        if (name != undefined && name != '')
            endpoint += '&search=' + name;

        tainacan.get(endpoint, { cancelToken: aCollectionRequestSource.token })
            .then(response => {
                let someCollections = response.data.map((collection) => ({ name: collection.name, id: collection.id + '' }));

                this.setState({ 
                    isLoadingCollections: false, 
                    collections: someCollections
                });
                
                return someCollections;
            })
            .catch(error => {
                console.log('Error trying to fetch collections: ' + error);
            });
    }

    resetCollections() {

        this.setState({
            itemsPage: 1, 
            collectionId: null,
            collectionPage: 1,
            modalCollections: [],
            modalItems: []
        });
        this.fetchModalCollections(); 
    }

    render() {
        return this.state.collectionId != null && this.state.collectionId != undefined ? (
            // Items modal
            <Modal
                    className="wp-block-tainacan-modal"
                    title={__('Select the desired items from collection ' + this.state.collectionName, 'tainacan')}
                    onRequestClose={ () => this.cancelSelection() }
                    contentLabel={__('Select items', 'tainacan')}>
                    
                <div>
                    <div className="modal-search-area">
                        <TextControl 
                                label={__('Search for a item', 'tainacan')}
                                value={ this.state.searchItemName }
                                onChange={(value) => {
                                    this.setState({ 
                                        searchItemName: value
                                    });
                                    _.debounce(this.fetchItems(value), 300);
                                }}/>
                    </div>
                    {(
                    this.state.searchItemName != '' ? ( 

                        this.state.items.length > 0 ?
                        (
                            <div>
                                <ul className="modal-checkbox-list">
                                {
                                    this.state.items.map((item) =>
                                    <li 
                                        key={ item.id }
                                        className="modal-checkbox-list-item">
                                        { item.thumbnail ?
                                            <img
                                                aria-hidden
                                                src={ item.thumbnail && item.thumbnail[0] && item.thumbnail[0].src ? item.thumbnail[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                                                alt={ item.thumbnail && item.thumbnail[0] ? item.thumbnail[0].alt : item.title }/>
                                            : null
                                        }
                                        <CheckboxControl
                                            label={ item.title }
                                            checked={ this.isTemporaryItemSelected(item.id) }
                                            onChange={ ( isChecked ) => { this.toggleSelectTemporaryItem(item, isChecked) } }
                                        />
                                    </li>
                                    )
                                }                                                
                                </ul>
                                { this.state.isLoadingItems ? <Spinner /> : null }
                            </div>
                        )
                        : this.state.isLoadingItems ? <div class="spinner-container"><Spinner /></div> :
                        <div className="modal-loadmore-section">
                            <p>{ __('Sorry, no items found.', 'tainacan') }</p>
                        </div>
                    ) : 
                    this.state.modalItems.length > 0 ? 
                    (   
                        <div>
                            <ul className="modal-checkbox-list">
                            {
                                this.state.modalItems.map((item) =>
                                    <li 
                                        key={ item.id }
                                        className="modal-checkbox-list-item">
                                        { item.thumbnail ?
                                            <img
                                                aria-hidden
                                                src={ item.thumbnail && item.thumbnail[0] && item.thumbnail[0].src ? item.thumbnail[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                                                alt={ item.thumbnail && item.thumbnail[0] ? item.thumbnail[0].alt : item.title }/>
                                            : null
                                        }
                                        <CheckboxControl
                                            label={ item.title }
                                            checked={ this.isTemporaryItemSelected(item.id) }
                                            onChange={ ( isChecked ) => { this.toggleSelectTemporaryItem(item, isChecked) } } />
                                    </li>
                                )
                            }                                                
                            </ul>
                            { this.state.isLoadingItems ? <Spinner /> : null }
                            <div className="modal-loadmore-section">
                                <p>{ __('Showing', 'tainacan') + " " + this.state.modalItems.length + " " + __('of', 'tainacan') + " " + this.state.totalModalItems + " " + __('items', 'tainacan') + "."}</p>
                                {
                                    this.state.modalItems.length < this.state.totalModalItems ? (
                                    <Button 
                                        isDefault
                                        isSmall
                                        onClick={ () => this.fetchModalItems(this.state.collectionId) }>
                                        {__('Load more', 'tainacan')}
                                    </Button>
                                    ) : null
                                }
                            </div>
                        </div>
                    ) : this.state.isLoadingItems ? <Spinner /> :
                    <div className="modal-loadmore-section">
                        <p>{ __('Sorry, no items found.', 'tainacan') }</p>
                    </div>
                )}
                <div className="modal-footer-area">
                    <Button
                        isDefault
                        onClick={ () => this.resetCollections() }>
                        {__('Switch collection', 'tainacan')}
                    </Button>
                    <Button 
                        isPrimary
                        onClick={ () => this.applySelectedItems() }>
                        {__('Finish', 'tainacan')}
                    </Button>
                </div>
            </div>
        </Modal>
    ) : (
        // Collections modal
        <Modal
                className="wp-block-tainacan-modal"
                title={__('Select a collection to fetch items from', 'tainacan')}
                onRequestClose={ () => this.cancelSelection() }
                contentLabel={__('Select items', 'tainacan')}>
                <div>
                    <div className="modal-search-area">
                        <TextControl 
                                label={__('Search for a collection', 'tainacan')}
                                value={ this.state.searchCollectionName }
                                onChange={(value) => {
                                    this.setState({ 
                                        searchCollectionName: value
                                    });
                                    _.debounce(this.fetchCollections(value), 300);
                                }}/>
                    </div>
                    {(
                    this.state.searchCollectionName != '' ? (
                        this.state.collections.length > 0 ?
                        (
                            <div>
                                <div className="modal-radio-list">
                                    {
                                    <RadioControl
                                        selected={ this.state.temporaryCollectionId }
                                        options={
                                            this.state.collections.map((collection) => {
                                                return { label: collection.name, value: '' + collection.id }
                                            })
                                        }
                                        onChange={ ( aCollectionId ) => { 
                                            this.setState({ temporaryCollectionId: aCollectionId });
                                        } } />
                                    }                                      
                                </div>
                            </div>
                        ) :
                        this.state.isLoadingCollections ? (
                            <Spinner />
                        ) :
                        <div className="modal-loadmore-section">
                            <p>{ __('Sorry, no collection found.', 'tainacan') }</p>
                        </div> 
                    ):
                    this.state.modalCollections.length > 0 ? 
                    (   
                        <div>
                            <div className="modal-radio-list">
                                {
                                <RadioControl
                                    selected={ this.state.temporaryCollectionId }
                                    options={
                                        this.state.modalCollections.map((collection) => {
                                            return { label: collection.name, value: '' + collection.id }
                                        })
                                    }
                                    onChange={ ( aCollectionId ) => { 
                                        this.setState({ temporaryCollectionId: aCollectionId });
                                    } } />
                                }                                     
                            </div>
                            <div className="modal-loadmore-section">
                                <p>{ __('Showing', 'tainacan') + " " + this.state.modalCollections.length + " " + __('of', 'tainacan') + " " + this.state.totalModalCollections + " " + __('collections', 'tainacan') + "."}</p>
                                {
                                    this.state.modalCollections.length < this.state.totalModalCollections ? (
                                    <Button 
                                        isDefault
                                        isSmall
                                        onClick={ () => this.fetchModalCollections() }>
                                        {__('Load more', 'tainacan')}
                                    </Button>
                                    ) : null
                                }
                            </div>
                        </div>
                    ) : this.state.isLoadingCollections ? <div class="spinner-container"><Spinner /></div> :
                    <div className="modal-loadmore-section">
                        <p>{ __('Sorry, no collection found.', 'tainacan') }</p>
                    </div>
                )}
                <div className="modal-footer-area">
                    <Button 
                        isDefault
                        onClick={ () => { this.cancelSelection() }}>
                        {__('Cancel', 'tainacan')}
                    </Button>
                    <Button 
                        isPrimary
                        disabled={ this.state.temporaryCollectionId == undefined || this.state.temporaryCollectionId == null || this.state.temporaryCollectionId == ''}
                        onClick={ () => this.selectCollection(this.state.temporaryCollectionId) }>
                        {__('Select items', 'tainacan')}
                    </Button>
                </div>
            </div>
        </Modal> 
        );
    }
}