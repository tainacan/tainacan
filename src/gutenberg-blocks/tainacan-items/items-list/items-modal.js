import tainacan from '../../api-client/axios.js';
import axios from 'axios';
import qs from 'qs';

const { __ } = wp.i18n;

const { TextControl, Button, Modal, SelectControl, RadioControl, Spinner } = wp.components;

export default class ItemsModal extends React.Component {
    constructor(props) {
        super(props);

        // Initialize state
        this.state = {
            collectionsPerPage: 24,
            items: [],
            isLoadingItems: false,
            itemsRequestSource: undefined,
            collectionId: undefined,  
            collectionName: '', 
            isLoadingCollections: false, 
            modalCollections: [],
            totalModalCollections: 0, 
            collectionPage: 1,
            collectionOrderBy: 'date-desc',
            temporaryCollectionId: '',
            searchCollectionName: '',
            collections: [],
            collectionsRequestSource: undefined,
            searchURL: '',
        };
        
        // Bind events
        this.fetchItems = this.fetchItems.bind(this);
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
        });

        if (this.props.existingCollectionId != null && this.props.existingCollectionId != undefined) {
            this.fetchCollection(this.props.existingCollectionId);
            this.setState({ 
                searchURL: this.props.existingSearchURL ? this.props.existingSearchURL : tainacan_blocks.admin_url + 'admin.php?page=tainacan_admin#/collections/'+ this.props.existingCollectionId + '/items/?iframemode=true' });
        } else {
            this.setState({ collectionPage: 1 });
            this.fetchModalCollections();
        }
    }

    // ITEMS RELATED --------------------------------------------------
    applySelectedItems() {
        let iframe = document.getElementById("itemsFrame");
        if (iframe) {
            let params = new URLSearchParams(iframe.contentWindow.location.search);
            let selectedItems = params.getAll('selecteditems');
            params.delete('selecteditems')
            this.fetchItems(selectedItems);
        }
    }

    cancelSelection() {

        this.setState({
            modalCollections: []
        });

        this.props.onCancelSelection();
    }

    fetchItems(selectedItems) {

        this.setState({ isLoadingItems: true });

        if (this.state.itemsRequestSource != undefined)
            this.state.itemsRequestSource.cancel('Previous items search canceled.');

        let anItemsRequestSource = axios.CancelToken.source();

        let endpoint = '/collection/' + this.state.collectionId + '/items?'+ qs.stringify({ postin: selectedItems, perpage: selectedItems.length }) + '&fetch_only=title,url,thumbnail';
        
        tainacan.get(endpoint, { cancelToken: anItemsRequestSource.token })
            .then(response => {
                
                let someItems = response.data.items.map((item) => ({ 
                    title: item.title, 
                    id: isNaN(item.id) ? item.id : 'item-id-' + item.id,
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

                this.props.onApplySelection(someItems);
                
                return someItems;
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

        let endpoint = '/collections/?perpage=' + this.state.collectionsPerPage + '&paged=' + this.state.collectionPage;

        if (this.state.collectionOrderBy == 'date')
            endpoint += '&orderby=date&order=asc';
        else if (this.state.collectionOrderBy == 'date-desc')
            endpoint += '&orderby=date&order=desc';
        else if (this.state.collectionOrderBy == 'title')
            endpoint += '&orderby=title&order=asc';
        else if (this.state.collectionOrderBy == 'title-desc')
            endpoint += '&orderby=title&order=desc';

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
            collectionId: selectedCollectionId,
            searchURL: tainacan_blocks.admin_url + 'admin.php?page=tainacan_admin#/collections/' + selectedCollectionId + '/items/?iframemode=true'
        });
        
        this.props.onSelectCollection(selectedCollectionId);
        this.fetchCollection(selectedCollectionId);
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

        let endpoint = '/collections/?perpage=' + this.state.collectionsPerPage;
        if (name != undefined && name != '')
            endpoint += '&search=' + name;
        
        if (this.state.collectionOrderBy == 'date')
            endpoint += '&orderby=date&order=asc';
        else if (this.state.collectionOrderBy == 'date-desc')
            endpoint += '&orderby=date&order=desc';
        else if (this.state.collectionOrderBy == 'title')
            endpoint += '&orderby=title&order=asc';
        else if (this.state.collectionOrderBy == 'title-desc')
            endpoint += '&orderby=title&order=desc';

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
            collectionId: null,
            collectionPage: 1,
            modalCollections: []
        });
        this.fetchModalCollections(); 
    }

    render() {
        return this.state.collectionId != null && this.state.collectionId != undefined ? (
        // Items modal
        <Modal
            className="wp-block-tainacan-modal dynamic-modal"
            title={ __('Select items to add on block', 'tainacan', 'tainacan')}
            onRequestClose={ () => this.cancelSelection() }
            contentLabel={ __('Select items that will be added on block', 'tainacan', 'tainacan')}>
            <iframe
                    id="itemsFrame"
                    src={ this.state.searchURL } />
            <div className="modal-footer-area">
                <Button 
                    isDefault
                    onClick={ () => { this.resetCollections() }}>
                    {__('Switch collection', 'tainacan')}
                </Button>
                <Button
                    style={{ marginLeft: 'auto' }} 
                    isPrimary
                    onClick={ () => this.applySelectedItems() }>
                    {__('Add the selected items', 'tainacan')}
                </Button>
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
                                placeholder={ __('Search by collection\'s name', 'tainacan') }
                                label={__('Search for a collection', 'tainacan')}
                                value={ this.state.searchCollectionName }
                                onChange={(value) => {
                                    this.setState({ 
                                        searchCollectionName: value
                                    });
                                    _.debounce(this.fetchCollections(value), 300);
                                }}/>
                        <SelectControl
                                label={__('Order by', 'tainacan')}
                                value={ this.state.collectionOrderBy }
                                options={ [
                                    { label: __('Latest', 'tainacan'), value: 'date-desc' },
                                    { label: __('Oldest', 'tainacan'), value: 'date' },
                                    { label: __('Name (A-Z)', 'tainacan'), value: 'title' },
                                    { label: __('Name (Z-A)', 'tainacan'), value: 'title-desc' }
                                ] }
                                onChange={ ( aCollectionOrderBy ) => { 
                                    this.state.collectionOrderBy = aCollectionOrderBy;
                                    this.state.collectionPage = 1;
                                    this.setState({ 
                                        collectionOrderBy: this.state.collectionOrderBy,
                                        collectionPage: this.state.collectionPage 
                                    });
                                    if (this.state.searchCollectionName && this.state.searchCollectionName != '') {
                                        this.fetchCollections(this.state.searchCollectionName);
                                    } else {
                                        this.fetchModalCollections();
                                    }
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
                    {
                        this.state.isLoadingItems ? (
                            <Spinner />
                        ) : null
                    }
                </div>
            </div>
        </Modal> 
        );
    }
}