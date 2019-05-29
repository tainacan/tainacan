import tainacan from '../../api-client/axios.js';
import axios from 'axios';

const { __ } = wp.i18n;

const { TextControl, Button, Modal, RadioControl, Spinner } = wp.components;

export default class MetadataModal extends React.Component {
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
            metadatumId: undefined,  
            isLoadingMetadata: false, 
            modalMetadata: [],
            temporaryMetadatumId: '',
            collections: [],
            collectionsRequestSource: undefined,
            metadataRequestSource: undefined
        };
        
        // Bind events
        this.resetCollections = this.resetCollections.bind(this);
        this.selectCollection = this.selectCollection.bind(this);
        this.fetchCollections = this.fetchCollections.bind(this);
        this.fetchModalCollections = this.fetchModalCollections.bind(this);
        this.fetchCollection = this.fetchCollection.bind(this);
        this.selectMetadatum = this.selectMetadatum.bind(this);
        this.fetchModalMetadata = this.fetchModalMetadata.bind(this);
    }

    componentWillMount() {
        
        this.setState({ 
            collectionId: this.props.existingCollectionId
        });
        if (this.props.existingCollectionId != null && this.props.existingCollectionId != undefined) {
            this.fetchCollection(this.props.existingCollectionId);
            this.fetchModalMetadata(this.props.existingCollectionId);
            this.setState({ metadatumId: this.props.existingMetadatumId ? this.props.existingMetadatumId : undefined });
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
        if (collectionId != 'default') {
            tainacan.get('/collections/' + collectionId)
                .then((response) => {
                    this.setState({ collectionName: response.data.name });
                }).catch(error => {
                    console.log('Error trying to fetch collection: ' + error);
                });
        } else {
            this.setState({ collectionName: __('Repository', 'tainacan') });
        }
    }

    selectCollection(selectedCollectionId) {

        this.setState({
            collectionId: selectedCollectionId        
        });

        this.props.onSelectCollection(selectedCollectionId);
        this.fetchCollection(selectedCollectionId);
        this.fetchModalMetadata(selectedCollectionId);
    }

    fetchCollections(name) {

        if (this.state.collectionsRequestSource != undefined)
            this.state.collectionsRequestSource.cancel('Previous collections search canceled.');

        let aCollectionRequestSource = axios.CancelToken.source();

        this.setState({ 
            collectionsRequestSource: aCollectionRequestSource,
            isLoadingCollections: true, 
            collections: [],
            metadata: []
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


    // FACETS RELATED --------------------------------------------------
    fetchModalMetadata(selectedCollectionId) {

        let someModalMetadata = [];
        let endpoint = selectedCollectionId != 'default' ? '/collection/' + selectedCollectionId + '/metadata/?nopaging=1' : '/metadata/?nopaging=1';

        this.setState({ 
            isLoadingMetadata: true,
            modalMetadata: someModalMetadata
        });

        tainacan.get(endpoint)
            .then(response => {

                let otherModalMetadata = this.state.modalMetadata;

                for (let metadatum of response.data) {
                    otherModalMetadata.push({ 
                        name: metadatum.name, 
                        id: metadatum.id,
                        type: metadatum.metadata_type_object ? metadatum.metadata_type_object.component : false
                    });
                }

                this.setState({ 
                    isLoadingMetadata: false, 
                    modalMetadata: otherModalMetadata
                });
            
                return otherModalMetadata;
            })
            .catch(error => {
                console.log('Error trying to fetch metadata: ' + error);
            });
    }

    selectMetadatum(selectedMetadatumId) {
        this.setState({
            collectionId: selectedMetadatumId
        });

        this.props.onSelectMetadatum(selectedMetadatumId);
    }


    render() {
        return this.state.collectionId != null && this.state.collectionId != undefined ? (
            // Metadata modal
            <Modal
                className="wp-block-tainacan-modal"
                title={__('Select a metadatum to show it\'s values on block', 'tainacan')}
                onRequestClose={ () => this.cancelSelection() }
                contentLabel={__('Select metadatum', 'tainacan')}>
                {(
                    this.state.modalMetadata.length > 0 ? 
                    (   
                        <div>
                            <div className="modal-radio-list">
                                <RadioControl
                                    selected={ this.state.temporaryMetadatumId }
                                    options={
                                        this.state.modalMetadata.map((metadatum) => {
                                            return { label: metadatum.name, value: '' + metadatum.id }
                                        })
                                    }
                                    onChange={ ( aMetadatumId ) => { 
                                        this.setState({ temporaryMetadatumId: aMetadatumId });
                                    } } />                          
                            </div>
                        </div>
                    ) : this.state.isLoadingMetadata ? <Spinner/> :
                        <div className="modal-loadmore-section">
                            <p>{ __('Sorry, no metadatum found.', 'tainacan') }</p>
                        </div>
                )
            }
            <div className="modal-footer-area">
                <Button 
                    isDefault
                    onClick={ () => { this.resetCollections(); }}>
                    {__('Switch collection', 'tainacan')}
                </Button>
                <Button
                    isPrimary
                    disabled={ this.state.temporaryMetadatumId == undefined || this.state.temporaryMetadatumId == null || this.state.temporaryMetadatumId == ''}
                    onClick={ () => { this.selectMetadatum(this.state.temporaryMetadatumId);  } }>
                    {__('Finish', 'tainacan')}
                </Button>
            </div>
        </Modal> 
        ) : (
        // Collections modal
        <Modal
                className="wp-block-tainacan-modal"
                title={__('Select a collection to fetch metadata from', 'tainacan')}
                onRequestClose={ () => this.cancelSelection() }
                contentLabel={__('Select collection', 'tainacan')}>
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
                                
                                <p class="modal-radio-area-label">{__('Repository', 'tainacan')}</p>
                                <RadioControl
                                    className={'repository-radio-option'}
                                    selected={ this.state.temporaryCollectionId }
                                    options={ [{ label: __('Repository items', 'tainacan'), value: 'default' }] }
                                    onChange={ ( aCollectionId ) => { 
                                        this.setState({ temporaryCollectionId: aCollectionId });
                                    } } />
                                <hr/>
                                <p class="modal-radio-area-label">{__('Collections', 'tainacan')}</p>
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
                        {__('Select metadatum', 'tainacan')}
                    </Button>
                </div>
            </div>
        </Modal> 
        );
    }
}