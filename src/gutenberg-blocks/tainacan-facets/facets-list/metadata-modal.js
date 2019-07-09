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
            collectionSlug: undefined,
            isLoadingCollections: false, 
            modalCollections: [],
            totalModalCollections: 0, 
            collectionPage: 1,
            temporaryCollectionId: '',
            searchCollectionName: '',
            metadatumId: undefined,  
            metadatumType: undefined,  
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
        this.selectMetadatum = this.selectMetadatum.bind(this);
        this.fetchModalMetadata = this.fetchModalMetadata.bind(this);
        this.getMetadatumType = this.getMetadatumType.bind(this);
    }

    componentWillMount() {
        
        this.setState({ 
            collectionId: this.props.existingCollectionId,
            collectionSlug: this.props.existingCollectionSlug
        });
        if (this.props.existingCollectionId != null && this.props.existingCollectionId != undefined) {
            this.fetchModalMetadata(this.props.existingCollectionId);
            this.setState({ 
                metadatumId: this.props.existingMetadatumId ? this.props.existingMetadatumId : undefined, 
                metadatumType: this.props.existingMetadatumType ? this.props.existingMetadatumType : undefined 
            });
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
                        id: collection.id,
                        slug: collection.slug
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

    selectCollection(selectedCollectionId) {

        let selectedCollection;
        if (selectedCollectionId == 'default')
            selectedCollection = { label: __('Repository items', 'tainacan'), id: 'default', slug: tainacan_plugin.theme_items_list_url.split('/')[tainacan_plugin.theme_items_list_url.split('/').length - 1] };
        else {
            selectedCollection = this.state.modalCollections.find((collection) => collection.id == selectedCollectionId)
            if (selectedCollection == undefined)
                selectedCollection = this.state.collections.find((collection) => collection.id == selectedCollectionId)
        }

        this.setState({
            collectionId: selectedCollection.id,
            collectionSlug: selectedCollection.slug      
        });

        this.props.onSelectCollection(selectedCollection);
        this.fetchModalMetadata(selectedCollection.id);
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
                let someCollections = response.data.map((collection) => ({ name: collection.name, id: collection.id + '', slug: collection.slug }));

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
                        type: this.getMetadatumType(metadatum)
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

    getMetadatumType(metadatum) {
        let metadatumType = metadatum.metadata_type_object ? metadatum.metadata_type_object.component : false;

        if (metadatumType) {
            switch(metadatumType) {
                case 'tainacan-text':           return __('Text', 'tainacan');
                case 'tainacan-textarea':       return __('Text area', 'tainacan');
                case 'tainacan-date':           return __('Date', 'tainacan');
                case 'tainacan-numeric':        return __('Numeric', 'tainacan');
                case 'tainacan-selectbox':      return __('Select box', 'tainacan');
                case 'tainacan-relationship':   return __('Relationship', 'tainacan');
                case 'tainacan-taxonomy':       return __('Taxonomy', 'tainacan');
                case 'tainacan-compound':       return __('Compound', 'tainacan');
                default:                        return false;
            }
        } else {
            return metadatumType;
        }
    }

    selectMetadatum(selectedMetadatum) {
        this.setState({
            metadatumId: selectedMetadatum.id,
            metadatumType: selectedMetadatum.type
        });
        this.props.onSelectMetadatum({ 
            metadatumId: selectedMetadatum.id,
            metadatumType: selectedMetadatum.type
        });
    }


    render() {
        return this.state.collectionId != null && this.state.collectionId != undefined ? (
            // Metadata modal
            <Modal
                className="wp-block-tainacan-modal"
                title={__('Select a metadatum to show it\'s facets on block', 'tainacan')}
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
                                            return { label: metadatum.name + ' (' + metadatum.type + ')', value: '' + metadatum.id }
                                        })
                                    }
                                    onChange={ ( aMetadatumId ) => { 
                                        this.setState({ 
                                            temporaryMetadatumId: aMetadatumId
                                        });
                                    } } />                          
                            </div>
                            <br/>
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
                    onClick={ () => { this.selectMetadatum(this.state.modalMetadata.find((metadatatum) => metadatatum.id == this.state.temporaryMetadatumId));  } }>
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
                                                return { label: collection.name, value: '' + collection.id, slug: collection.slug }
                                            })
                                        }
                                        onChange={ ( aCollectionId ) => { 
                                            this.setState({ temporaryCollectionId: aCollectionId });
                                        } } />
                                    }                                      
                                </div>
                                <br/>
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
                                    options={ [{ label: __('Repository items', 'tainacan'), value: 'default', slug: tainacan_plugin.theme_items_list_url.split('/')[tainacan_plugin.theme_items_list_url.split('/').length - 1] }] }
                                    onChange={ ( aCollectionId ) => { 
                                        this.setState({ temporaryCollectionId: aCollectionId });
                                    } } />
                                <hr/>
                                <p class="modal-radio-area-label">{__('Collections', 'tainacan')}</p>
                                <RadioControl
                                    selected={ this.state.temporaryCollectionId }
                                    options={
                                        this.state.modalCollections.map((collection) => {
                                            return { label: collection.name, value: '' + collection.id, slug: collection.slug }
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
                        disabled={ this.state.temporaryCollectionId == undefined || this.state.temporaryCollectionId == null || this.state.temporaryCollectionId == '' && (this.state.searchCollectionName != '' ? this.state.collections.find((collection) => collection.id == this.state.temporaryCollectionId) : this.state.modalCollections.find((collection) => collection.id == this.state.temporaryCollectionId)) != undefined}
                        onClick={ () => { this.selectCollection(this.state.temporaryCollectionId) } }>
                        {__('Select metadatum', 'tainacan')}
                    </Button>
                </div>
            </div>
        </Modal> 
        );
    }
}