import tainacan from '../../js/axios.js';
import axios from 'axios';
import qs from 'qs';

const { __ } = wp.i18n;

const { TextControl, Button, Modal, SelectControl, RadioControl, Spinner } = wp.components;

export default class CollectionModal extends React.Component {
    constructor(props) {
        super(props);

        // Initialize state
        this.state = {
            collectionViewModes: [],
            collectionsPerPage: 24,
            collectionId: undefined,  
            isLoadingCollections: false, 
            modalCollections: [],
            totalModalCollections: 0, 
            collectionPage: 1,
            collectionOrderBy: 'date-desc',
            temporaryCollectionId: '',
            temporaryCollectionDefaultViewMode: '',
            temporaryCollectionEnabledViewModes: [],
            searchCollectionName: '',
            collections: [],
            collectionsRequestSource: undefined
        };
        
        // Bind events
        this.resetCollections = this.resetCollections.bind(this);
        this.selectCollection = this.selectCollection.bind(this);
        this.fetchCollections = this.fetchCollections.bind(this);
        this.fetchModalCollections = this.fetchModalCollections.bind(this);
    }

    componentWillMount() {
        
        this.setState({ 
            collectionId: this.props.existingCollectionId,
            temporaryCollectionId: this.props.existingCollectionId,
            temporaryCollectionDefaultViewMode: this.props.existingCollectionDefaultViewMode,
            temporaryCollectionEnabledViewModes: this.props.existingCollectionEnabledViewModes,
            collectionPage: 1
        });

        this.fetchModalCollections();
    }

    cancelSelection() {

        this.setState({
            modalCollections: []
        });

        this.props.onCancelSelection();
    }

    // COLLECTIONS RELATED --------------------------------------------------
    fetchModalCollections() {

        let someModalCollections = this.state.modalCollections;
        if (this.state.collectionPage <= 1)
            someModalCollections = [];

        let query = {
            perpage: this.state.collectionsPerPage,
            paged: this.state.collectionPage
        }

        if (this.props.filterOptionsBy && Object.keys(this.props.filterOptionsBy).length !== 0) {
            let metaqueries = [];

            for (let metaquery of Object.keys(this.props.filterOptionsBy)) {
                metaqueries.push({
                    key: metaquery,
                    value: this.props.filterOptionsBy[metaquery]
                })
            }
            query = {...query, metaquery: metaqueries}
        }
            
        let endpoint = '/collections/?' + qs.stringify(query); 

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
                        id: collection.id,
                        default_view_mode: collection.default_view_mode,
                        enabled_view_modes: collection.enabled_view_modes
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

    selectCollection({ collectionId, collectionDefaultViewMode, collectionEnabledViewModes }) {
        collectionId = collectionId;
        this.setState({ collectionId: collectionId });
        this.props.onSelectCollection({ collectionId, collectionDefaultViewMode, collectionEnabledViewModes });
    }

    fetchCollections(name) {

        if (this.state.collectionsRequestSource != undefined)
            this.state.collectionsRequestSource.cancel('Previous collections search canceled.');

        let aCollectionRequestSource = axios.CancelToken.source();

        this.setState({ 
            collectionsRequestSource: aCollectionRequestSource,
            isLoadingCollections: true, 
            collections: []
        });

        let query = {
            perpage: this.state.collectionsPerPage,
            paged: this.state.collectionPage
        }

        if (this.props.filterOptionsBy && Object.keys(this.props.filterOptionsBy).length !== 0) {
            let metaqueries = [];

            for (let metaquery of Object.keys(this.props.filterOptionsBy)) {
                metaqueries.push({
                    key: metaquery,
                    value: this.props.filterOptionsBy[metaquery]
                })
            }
            query = {...query, metaquery: metaqueries}
        }
            
        let endpoint = '/collections/?' + qs.stringify(query); 

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
                let someCollections = response.data.map((collection) => ({ 
                    name: collection.name, 
                    id: collection.id + '',
                    default_view_mode: collection.default_view_mode,
                    enabled_view_modes: collection.enabled_view_modes
                }));

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
        return (
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
                                            const selectedCollection = this.state.modalCollections.find((aCollection => aCollectionId == aCollection.id));
                                            this.state.temporaryCollectionId = aCollectionId;
                                            this.state.temporaryCollectionDefaultViewMode = selectedCollection.default_view_mode;
                                            this.state.temporaryCollectionEnabledViewModes = selectedCollection.enabled_view_modes;
                                            this.setState({ temporaryCollectionId: aCollectionId });
                                            this.setState({ temporaryCollectionDefaultViewMode: selectedCollection.default_view_mode });
                                            this.setState({ temporaryCollectionEnabledViewModes: selectedCollection.enabled_view_modes });
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
                                        const selectedCollection = this.state.modalCollections.find((aCollection => aCollectionId == aCollection.id));
                                        this.state.temporaryCollectionId = aCollectionId;
                                        this.state.temporaryCollectionDefaultViewMode = selectedCollection.default_view_mode;
                                        this.state.temporaryCollectionEnabledViewModes = selectedCollection.enabled_view_modes;
                                        this.setState({ temporaryCollectionId: aCollectionId });
                                        this.setState({ temporaryCollectionDefaultViewMode: selectedCollection.default_view_mode });
                                        this.setState({ temporaryCollectionEnabledViewModes: selectedCollection.enabled_view_modes });
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
                        onClick={ () => this.selectCollection({ 
                            collectionId: this.state.temporaryCollectionId,
                            collectionDefaultViewMode: this.state.temporaryCollectionDefaultViewMode,
                            collectionEnabledViewModes: this.state.temporaryCollectionEnabledViewModes
                        }) }>
                        {__('Use selected Collection', 'tainacan')}
                    </Button>
                    {
                        this.state.isLoadingItems ? (
                            <Spinner />
                        ) : null
                    }
                </div>
            </div>
        </Modal> 
        )
    }
}