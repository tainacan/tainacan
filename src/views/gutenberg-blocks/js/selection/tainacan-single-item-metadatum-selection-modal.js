import tainacanApi from '../axios.js';
import axios from 'axios';
import { subscribeSelectionState, appendInitiallySelectedItemsParam } from './tainacan-selection.js';

const { __ } = wp.i18n;

const { TextControl, Button, Modal, RadioControl, SelectControl, Spinner } = wp.components;
const currentWPVersion = (typeof tainacan_blocks != 'undefined') ? tainacan_blocks.wp_version : tainacan_plugin.wp_version;

export default class TainacanSingleItemMetadatumSelectionModal extends React.Component {
    constructor(props) {
        super(props);

        const existingMetadatumId = props.existingMetadatumId;
        const existingCollectionId = props.existingCollectionId;
        const searchURL = existingCollectionId && !props.isTemplateMode
            ? appendInitiallySelectedItemsParam(
                tainacan_blocks.admin_url + '?itemsSingleSelectionMode=true&page=tainacan_admin#/collections/' + existingCollectionId + '/items/?status=publish',
                props.existingItemId
            )
            : '';

        // Initialize state from block attributes so the correct step renders on first paint.
        this.state = {
            collectionsPerPage: 24,
            collectionId: existingCollectionId,
            itemId: props.existingItemId,
            metadatumId: existingMetadatumId,
            collectionName: '',
            isLoadingCollections: false, 
            modalCollections: [],
            itemTitle: '',
            metadatumType: undefined,
            isLoadingMetadata: false, 
            modalMetadata: [],
            totalModalCollections: 0, 
            collectionOrderBy: 'date-desc',
            collectionPage: 1,
            temporaryCollectionId: existingCollectionId ? '' + existingCollectionId : '',
            temporaryItemId: props.existingItemId ? '' + props.existingItemId : '',
            temporaryMetadatumId: existingMetadatumId ? '' + existingMetadatumId : '',
            searchCollectionName: '',
            collections: [],
            collectionsRequestSource: undefined,
            metadata: [],
            metadataRequestSource: undefined,
            searchURL: searchURL,
            itemsPerPage: 12,
            templateMode: props.isTemplateMode || false
        };
        this.selectionState = props.existingItemId && Number(props.existingItemId) > 0
            ? { selectedItems: [String(props.existingItemId)], query: {}, href: '', collectionId: existingCollectionId ? String(existingCollectionId) : '' }
            : null;
        
        // Bind events
        this.resetCollections = this.resetCollections.bind(this);
        this.selectCollection = this.selectCollection.bind(this);
        this.fetchCollections = this.fetchCollections.bind(this);
        this.fetchModalCollections = this.fetchModalCollections.bind(this);
        this.fetchCollection = this.fetchCollection.bind(this);

        this.fetchItem = this.fetchItem.bind(this);
        this.selectItem = this.selectItem.bind(this);

        this.fetchModalMetadata = this.fetchModalMetadata.bind(this);
        
        this.applySelectedMetadatum = this.applySelectedMetadatum.bind(this);
        this.onSelectionState = this.onSelectionState.bind(this);
    }

    onSelectionState(state) {
        this.selectionState = state;
    }

    componentDidMount() {
        this.unsubscribeSelectionState = subscribeSelectionState(this.onSelectionState);

        const { existingCollectionId, existingItemId, isTemplateMode } = this.props;

        if (existingCollectionId && !isTemplateMode) {
            this.fetchCollection(existingCollectionId);

            if (existingItemId != null && existingItemId !== '' && Number(existingItemId) > 0) {
                this.fetchItem(existingItemId);
                this.fetchModalMetadata(existingCollectionId);
            }
        } else if (existingCollectionId && isTemplateMode) {
            this.fetchCollection(existingCollectionId);
            this.fetchModalMetadata(existingCollectionId);
        } else {
            this.setState({ collectionPage: 1 });
            this.fetchModalCollections();
        }
    }

    componentWillUnmount() {
        if (this.unsubscribeSelectionState)
            this.unsubscribeSelectionState();
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

        tainacanApi.get(endpoint)
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
        tainacanApi.get('/collections/' + collectionId)
            .then((response) => {
                this.setState({ collectionName: response.data.name });
            }).catch(error => {
                console.log('Error trying to fetch collection: ' + error);
            });
    }

    fetchItem(itemId) {
        tainacanApi.get('/items/' + itemId)
            .then((response) => {
                this.setState({ itemTitle: response.data.title });
            }).catch(error => {
                console.log('Error trying to fetch collection: ' + error);
            });
    }

    selectCollection(selectedCollectionId) {
        this.selectionState = null;
        this.setState({
            collectionId: selectedCollectionId,
            searchURL: tainacan_blocks.admin_url + '?itemsSingleSelectionMode=true&page=tainacan_admin#/collections/' + selectedCollectionId + '/items/?status=publish'
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

        tainacanApi.get(endpoint, { cancelToken: aCollectionRequestSource.token })
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

    fetchModalMetadata(existingCollectionId) {

        let someModalMetadata = [];
        let endpoint = '/collection/' + (existingCollectionId ? existingCollectionId : this.state.collectionId) + '/metadata/?nopaging=1';
        
        this.setState({ 
            isLoadingMetadata: true,
            modalMetadata: someModalMetadata
        });

        tainacanApi.get(endpoint)
            .then(response => {

                let otherModalMetadata = this.state.modalMetadata;

                for (let metadatum of response.data) {
                    otherModalMetadata.push({ 
                        name: metadatum.name, 
                        id: metadatum.id,
                        type: metadatum.metadata_type,
                        typeLabel: metadatum.metadata_type_object ? metadatum.metadata_type_object.name : ''
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


    selectItem() {
        const selectedItemId = this.selectionState && this.selectionState.selectedItems && this.selectionState.selectedItems[0];
        if (selectedItemId) {
            this.setState({
                itemId: selectedItemId
            });
            this.props.onSelectItem(selectedItemId);
            this.fetchModalMetadata(this.state.collectionId);
        }
    }

    applySelectedMetadatum(selectedMetadatum) {
        this.setState({
            metadatumId: selectedMetadatum.id,
            metadatumType: selectedMetadatum.type
        });
        this.props.onApplySelectedMetadatum({ 
            metadatumId: selectedMetadatum.id,
            metadatumType: selectedMetadatum.type
        });
    }

    resetCollections() {

        this.selectionState = null;
        this.setState({
            collectionId: null,
            collectionPage: 1,
            modalCollections: []
        });
        this.fetchModalCollections(); 
    }

    resetItem() {

        this.selectionState = null;
        this.setState({
            itemId: null,
        });
    }

    cancelSelection() {

        this.setState({
            modalCollections: [],
            modalMetadata: []
        });

        this.props.onCancelSelection();
    }

    render() {
        return (this.state.collectionId && (this.state.templateMode || this.state.itemId)) ? (
            // Metadata modal
            <Modal
                className={ 'wp-block-tainacan-modal ' + (currentWPVersion < '5.9' ? 'wp-version-smaller-than-5-9' : '') + (currentWPVersion < '6.1' ? 'wp-version-smaller-than-6-1' : '')  }
                title={__('Select a metadatum to show it\'s value', 'tainacan')}
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
                                            return { label: metadatum.name + ' (' + metadatum.typeLabel + ')', value: '' + metadatum.id }
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
                    isSecondary
                    onClick={ () => { this.resetCollections(); }}>
                    {__('Switch Collection', 'tainacan')}
                </Button>
                { !this.state.templateMode ?
                    <Button 
                        isSecondary
                        onClick={ () => { this.resetItem(); }}>
                        { __('Switch Item', 'tainacan') }
                    </Button>
                : null }
                <Button
                    isPrimary
                    disabled={ this.state.temporaryMetadatumId == undefined || this.state.temporaryMetadatumId == null || this.state.temporaryMetadatumId == ''}
                    onClick={ () => { this.applySelectedMetadatum(this.state.modalMetadata.find((metadatatum) => metadatatum.id == this.state.temporaryMetadatumId));  } }>
                    {__('Use this metadatum', 'tainacan')}
                </Button>
            </div>
        </Modal> 
        
    ) : (
        this.state.collectionId && !this.state.templateMode ? (
            // Item modal
            <Modal
                className={ 'wp-block-tainacan-modal dynamic-modal ' + (currentWPVersion < '5.9' ? 'wp-version-smaller-than-5-9' : '') + (currentWPVersion < '6.1' ? 'wp-version-smaller-than-6-1' : '') }
                title={ this.props.modalTitle ? this.props.modalTitle : __('Select one item for the block', 'tainacan') }
                onRequestClose={ () => this.cancelSelection() }
                shouldCloseOnClickOutside={ false }
                contentLabel={ this.props.modalTitle ? this.props.modalTitle : __('Select one item for the block', 'tainacan') }>
                <iframe
                        id="itemsFrame"
                        src={ this.state.searchURL } />
                <div className="modal-footer-area">
                    <Button 
                        isSecondary
                        onClick={ () => { this.resetCollections() }}>
                        {__('Switch collection', 'tainacan')}
                    </Button>
                    <Button
                        style={{ marginInlineStart: 'auto' }} 
                        isPrimary
                        onClick={ () => this.selectItem() }>
                        { __('Use this item', 'tainacan') }
                    </Button>
                </div>
        </Modal>
        ) : (
            !this.state.templateMode ?
            // Collections modal
            <Modal
                    className={ 'wp-block-tainacan-modal ' + (currentWPVersion < '5.9' ? 'wp-version-smaller-than-5-9' : '') + (currentWPVersion < '6.1' ? 'wp-version-smaller-than-6-1' : '')  }
                    title={__('Select a collection to fetch items from', 'tainacan')}
                    onRequestClose={ () => this.cancelSelection() }
                    shouldCloseOnClickOutside={ false }
                    contentLabel={__('Select item', 'tainacan')}>
                    <div>
                        <div className="modal-search-area">
                            <TextControl 
                                    label={__('Search for a collection', 'tainacan')}
                                    placeholder={ __('Search by collection\'s name', 'tainacan') }
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
                                            isSecondary
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
                            isSecondary
                            onClick={ () => { this.cancelSelection() }}>
                            {__('Cancel', 'tainacan')}
                        </Button>
                        <Button
                            isPrimary
                            disabled={ this.state.temporaryCollectionId == undefined || this.state.temporaryCollectionId == null || this.state.temporaryCollectionId == ''}
                            onClick={ () => { this.selectCollection(this.state.temporaryCollectionId);  } }>
                            { __('Select item', 'tainacan') }
                        </Button>
                    </div>
                </div>
            </Modal>
            : null
        ) 
        );
    }
}