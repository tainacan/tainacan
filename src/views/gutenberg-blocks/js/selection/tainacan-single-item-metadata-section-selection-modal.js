import tainacanApi from '../axios.js';
import axios from 'axios';

const { __ } = wp.i18n;

const { TextControl, Button, Modal, RadioControl, SelectControl, Spinner } = wp.components;
const currentWPVersion = (typeof tainacan_blocks != 'undefined') ? tainacan_blocks.wp_version : tainacan_plugin.wp_version;

export default class TainacanSingleItemMetadataSectionSelectionModal extends React.Component {
    constructor(props) {
        super(props);

        // Initialize state
        this.state = {
            collectionsPerPage: 24,
            collectionId: undefined,
            itemId: undefined,
            sectionId: undefined,
            collectionName: '',
            isLoadingCollections: false, 
            modalCollections: [],
            itemTitle: '',
            isLoadingMetadataSections: false, 
            modalMetadataSections: [],
            totalModalCollections: 0, 
            collectionOrderBy: 'date-desc',
            collectionPage: 1,
            temporaryCollectionId: '',
            temporaryItemId: '',
            temporaryMetadataSectionId: '',
            searchCollectionName: '',
            collections: [],
            collectionsRequestSource: undefined,
            metadataSections: [],
            metadataSectionsRequestSource: undefined,
            searchURL: '',
            itemsPerPage: 12,
            templateMode: false
        };
        
        // Bind events
        this.resetCollections = this.resetCollections.bind(this);
        this.selectCollection = this.selectCollection.bind(this);
        this.fetchCollections = this.fetchCollections.bind(this);
        this.fetchModalCollections = this.fetchModalCollections.bind(this);
        this.fetchCollection = this.fetchCollection.bind(this);

        this.fetchItem = this.fetchItem.bind(this);
        this.selectItem = this.selectItem.bind(this);

        this.fetchModalMetadataSections = this.fetchModalMetadataSections.bind(this);
        
        this.applySelectedMetadataSection = this.applySelectedMetadataSection.bind(this);
    }

    componentWillMount() {
        
        this.setState({ 
            collectionId: this.props.existingCollectionId,
            itemId: this.props.existingItemId,
            sectionId: this.props.existingSectionId,
            templateMode: this.props.isTemplateMode
        });
         
        if (this.props.existingCollectionId && !this.props.isTemplateMode) {
            this.fetchCollection(this.props.existingCollectionId);
            this.setState({ 
                searchURL: tainacan_blocks.admin_url + '?itemsSingleSelectionMode=true&page=tainacan_admin#/collections/'+ this.props.existingCollectionId + '/items/?status=publish'
            });

            if (this.props.existingItemId) {
                this.fetchItem(this.props.existingItemId);
                this.fetchModalMetadataSections();
            }

        }  else if (this.props.existingCollectionId && this.props.isTemplateMode) {
            this.fetchCollection(this.props.existingCollectionId);
            this.setState({
                collectionId: this.props.existingCollectionId,
                templateMode: this.props.isTemplateMode
            });
            this.fetchModalMetadataSections(this.props.existingCollectionId);
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
        tainacanApi.get('/collections/' + this.state.collectionId + '/items/' + itemId)
            .then((response) => {
                this.setState({ itemTitle: response.data.title });
            }).catch(error => {
                console.log('Error trying to fetch collection: ' + error);
            });
    }

    selectCollection(selectedCollectionId) {
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

    fetchModalMetadataSections(existingCollectionId) {

        let someModalMetadataSections = [];
        let endpoint = '/collection/' + (existingCollectionId ? existingCollectionId : this.state.collectionId) + '/metadata-sections/?nopaging=1';
        
        this.setState({ 
            isLoadingMetadataSections: true,
            modalMetadataSections: someModalMetadataSections
        });

        tainacanApi.get(endpoint)
            .then(response => {

                let otherModalMetadataSections = this.state.modalMetadataSections;

                for (let metadataSection of response.data) {
                    otherModalMetadataSections.push({ 
                        name: metadataSection.name, 
                        id: metadataSection.id,
                    });
                }

                this.setState({ 
                    isLoadingMetadataSections: false, 
                    modalMetadataSections: otherModalMetadataSections
                });
            
                return otherModalMetadataSections;
            })
            .catch(error => {
                console.log('Error trying to fetch metadataSections: ' + error);
            });
    }


    selectItem() {
        let iframe = document.getElementById("itemsFrame");
        if (iframe) {
            let params = new URLSearchParams(iframe.contentWindow.location.search);
            let selectedItems = params.getAll('selecteditems');
            params.delete('selecteditems')
            if (selectedItems[0]) {
                this.setState({
                    itemId: selectedItems[0]
                });
                this.props.onSelectItem(selectedItems[0]);
                this.fetchModalMetadataSections();
            }
        }
    }

    applySelectedMetadataSection(selectedMetadataSection) {
        this.setState({
            sectionId: selectedMetadataSection.id
        });
        this.props.onApplySelectedMetadataSection({ 
            sectionId: selectedMetadataSection.id
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

    resetItem() {

        this.setState({
            itemId: null,
        });
    }

    cancelSelection() {

        this.setState({
            modalCollections: [],
            modalMetadataSections: []
        });

        this.props.onCancelSelection();
    }

    render() {
        return (this.state.collectionId && (this.state.templateMode || this.state.itemId)) ? (
            // metadataSections modal
            <Modal
                className={ 'wp-block-tainacan-modal ' + (currentWPVersion < '5.9' ? 'wp-version-smaller-than-5-9' : '') + (currentWPVersion < '6.1' ? 'wp-version-smaller-than-6-1' : '')  }
                title={__('Select a metadata section to show', 'tainacan')}
                onRequestClose={ () => this.cancelSelection() }
                contentLabel={__('Select metadata section', 'tainacan')}>
                {(
                    this.state.modalMetadataSections.length > 0 ? 
                    (   
                        <div>
                            <div className="modal-radio-list">
                                <RadioControl
                                    selected={ this.state.temporaryMetadataSectionId }
                                    options={
                                        this.state.modalMetadataSections.map((metadataSection) => {
                                            return { label: metadataSection.name , value: '' + metadataSection.id }
                                        })
                                    }
                                    onChange={ ( aMetadataSectionId ) => { 
                                        this.setState({ 
                                            temporaryMetadataSectionId: aMetadataSectionId
                                        });
                                    } } />                          
                            </div>
                            <br/>
                        </div>
                    ) : this.state.isLoadingMetadataSections ? <Spinner/> :
                        <div className="modal-loadmore-section">
                            <p>{ __('Sorry, no metadata section found.', 'tainacan') }</p>
                        </div>
                )
            }
            <div className="modal-footer-area">
                <Button 
                    isSecondary
                    onClick={ () => { this.resetCollections(); }}>
                    { __('Switch Collection', 'tainacan') }
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
                    disabled={ this.state.temporaryMetadataSectionId == undefined || this.state.temporaryMetadataSectionId == null || this.state.temporaryMetadataSectionId == ''}
                    onClick={ () => { this.applySelectedMetadataSection(this.state.modalMetadataSections.find((metadataSection) => metadataSection.id == this.state.temporaryMetadataSectionId));  } }>
                    { __('Use this metadata section', 'tainacan') }
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
                        style={{ marginLeft: 'auto' }} 
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