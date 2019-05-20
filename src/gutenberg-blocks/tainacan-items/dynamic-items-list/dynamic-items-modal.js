import tainacan from '../../api-client/axios.js';
import axios from 'axios';

const { __ } = wp.i18n;

const { TextControl, Button, Modal, RadioControl, Spinner } = wp.components;

export default class DynamicItemsModal extends React.Component {
    constructor(props) {
        super(props);

        // Initialize state
        this.state = {
            collectionsPerPage: 24,
            collectionId: undefined,  
            collectionName: '', 
            isLoadingCollections: false, 
            modalCollections: [],
            totalModalCollections: 0, 
            collectionPage: 1,
            temporaryCollectionId: '',
            searchCollectionName: '',
            collections: [],
            collectionsRequestSource: undefined,
            searchURL: '',
        };
        
        // Bind events
        this.resetCollections = this.resetCollections.bind(this);
        this.selectCollection = this.selectCollection.bind(this);
        this.fetchCollections = this.fetchCollections.bind(this);
        this.fetchModalCollections = this.fetchModalCollections.bind(this);
        this.fetchCollection = this.fetchCollection.bind(this);
        this.applySelectedSearchURL = this.applySelectedSearchURL.bind(this);
    }

    componentWillMount() {
        
        this.setState({ 
            collectionId: this.props.existingCollectionId
        });
         
        if (this.props.existingCollectionId != null && this.props.existingCollectionId != undefined) {
            this.fetchCollection(this.props.existingCollectionId);
            this.setState({ searchURL: this.props.existingSearchURL ? this.props.existingSearchURL : tainacan_plugin.admin_url + 'admin.php?page=tainacan_admin#/collections/'+ this.props.existingCollectionId + '/items/?readmode=true&iframemode=true' });
        } else {
            this.setState({ collectionPage: 1 });
            this.fetchModalCollections();
        }
    }

    // COLLECTIONS RELATED --------------------------------------------------
    fetchModalCollections() {

        let someModalCollections = this.state.modalCollections;
        if (this.state.collectionPage <= 1)
            someModalCollections = [];

        let endpoint = '/collections/?orderby=title&order=asc&perpage=' + this.state.collectionsPerPage + '&paged=' + this.state.collectionPage;

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
            searchURL: tainacan_plugin.admin_url + 'admin.php?page=tainacan_admin#/collections/' + selectedCollectionId + '/items/?readmode=true&iframemode=true'
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

        let endpoint = '/collections/?orderby=title&order=asc&perpage=' + this.state.collectionsPerPage;
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

    applySelectedSearchURL() {    
        this.props.onApplySearchURL(document.getElementById("itemsFrame").contentWindow.location.href);
    }

    resetCollections() {

        this.setState({
            collectionId: null,
            collectionPage: 1,
            modalCollections: []
        });
        this.fetchModalCollections(); 
    }

    cancelSelection() {

        this.setState({
            modalCollections: []
        });

        this.props.onCancelSelection();
    }

    render() {
        return this.state.collectionId != null && this.state.collectionId != undefined ? (
            // Items modal
        <Modal
                className="wp-block-tainacan-modal dynamic-modal"
                title={__('Configure the items search to be used on block', 'tainacan')}
                onRequestClose={ () => this.cancelSelection() }
                contentLabel={__('Configure your items search to be shown on block', 'tainacan')}>
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
                        isPrimary
                        onClick={ () => this.applySelectedSearchURL() }>
                        {__('Use this search', 'tainacan')}
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
                    ) : this.state.isLoadingCollections ? <Spinner/> :
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
                        onClick={ () => { this.selectCollection(this.state.temporaryCollectionId);  } }>
                        {__('Configure search', 'tainacan')}
                    </Button>
                </div>
            </div>
        </Modal> 
        );
    }
}