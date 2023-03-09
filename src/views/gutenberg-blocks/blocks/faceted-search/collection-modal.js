import tainacan from '../../js/axios.js';
import axios from 'axios';
import qs from 'qs';

const { __ } = wp.i18n;

const { TextControl, Button, Modal, SelectControl, RadioControl, Spinner } = wp.components;
const currentWPVersion = (typeof tainacan_blocks != 'undefined') ? tainacan_blocks.wp_version : tainacan_plugin.wp_version;

export default class CollectionModal extends React.Component {
    constructor(props) {
        super(props);

        // Initialize state
        this.state = {
            collectionDefaultOrderBy: 'date',
            collectionDefaultOrder: 'ASC',
            collectionDefaultOrderByMeta: '',
            collectionDefaultOrderByType: '',
            collectionViewModes: [],
            collectionsPerPage: 24,
            collectionId: undefined,  
            isLoadingCollections: false, 
            modalCollections: [],
            totalModalCollections: 0, 
            collectionsPage: 1,
            collectionsOrderBy: 'date-desc',
            temporaryCollectionId: '',
            temporaryCollectionDefaultViewMode: '',
            temporaryCollectionEnabledViewModes: [],
            temporaryCollectionDefaultOrderBy: 'date',
            temporaryCollectionDefaultOrderByMeta: '',
            temporaryCollectionDefaultOrderByType: '',
            temporaryCollectionDefaultOrder: 'ASC',
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
            temporaryCollectionDefaultOrder: this.props.existingCollectionDefaultOrder,
            temporaryCollectionDefaultOrderBy: this.props.existingCollectionDefaultOrderBy,
            temporaryCollectionDefaultOrderByMeta: this.props.existingCollectionDefaultOrderByMeta,
            temporaryCollectionDefaultOrderByType: this.props.existingCollectionDefaultOrderByType,
            collectionsPage: 1
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
        if (this.state.collectionsPage <= 1)
            someModalCollections = [];

        let query = {
            perpage: this.state.collectionsPerPage,
            paged: this.state.collectionsPage
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

        if (this.state.collectionsOrderBy == 'date')
            endpoint += '&orderby=date&order=asc';
        else if (this.state.collectionsOrderBy == 'date-desc')
            endpoint += '&orderby=date&order=desc';
        else if (this.state.collectionsOrderBy == 'title')
            endpoint += '&orderby=title&order=asc';
        else if (this.state.collectionsOrderBy == 'title-desc')
            endpoint += '&orderby=title&order=desc';

        this.setState({ 
            isLoadingCollections: true,
            collectionsPage: this.state.collectionsPage + 1, 
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
                        enabled_view_modes: collection.enabled_view_modes,
                        default_orderby: collection.default_orderby,
                        default_order: collection.default_order
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

    selectCollection({ collectionId, collectionDefaultViewMode, collectionEnabledViewModes, collectionDefaultOrder, collectionDefaultOrderBy, collectionDefaultOrderByMeta, collectionDefaultOrderByType }) {
        collectionId = collectionId;
        this.setState({ collectionId: collectionId });
        this.props.onSelectCollection({ collectionId, collectionDefaultViewMode, collectionEnabledViewModes, collectionDefaultOrder, collectionDefaultOrderBy, collectionDefaultOrderByMeta, collectionDefaultOrderByType });
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
            paged: this.state.collectionsPage
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
        
        if (this.state.collectionsOrderBy == 'date')
            endpoint += '&orderby=date&order=asc';
        else if (this.state.collectionsOrderBy == 'date-desc')
            endpoint += '&orderby=date&order=desc';
        else if (this.state.collectionsOrderBy == 'title')
            endpoint += '&orderby=title&order=asc';
        else if (this.state.collectionsOrderBy == 'title-desc')
            endpoint += '&orderby=title&order=desc';

        tainacan.get(endpoint, { cancelToken: aCollectionRequestSource.token })
            .then(response => {
                let someCollections = response.data.map((collection) => ({ 
                    name: collection.name, 
                    id: collection.id + '',
                    default_view_mode: collection.default_view_mode,
                    enabled_view_modes: collection.enabled_view_modes,
                    default_orderby: collection.default_orderby,
                    default_order: collection.default_order
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
            collectionsPage: 1,
            modalCollections: []
        });
        this.fetchModalCollections(); 
    }

    render() {
        return (
        <Modal
                className={ 'wp-block-tainacan-modal ' + (currentWPVersion < '5.9' ? 'wp-version-smaller-than-5-9' : '') + (currentWPVersion < '6.1' ? 'wp-version-smaller-than-6-1' : '')  }
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
                                value={ this.state.collectionsOrderBy }
                                options={ [
                                    { label: __('Latest', 'tainacan'), value: 'date-desc' },
                                    { label: __('Oldest', 'tainacan'), value: 'date' },
                                    { label: __('Name (A-Z)', 'tainacan'), value: 'title' },
                                    { label: __('Name (Z-A)', 'tainacan'), value: 'title-desc' }
                                ] }
                                onChange={ ( acollectionsOrderBy ) => { 
                                    this.state.collectionsOrderBy = acollectionsOrderBy;
                                    this.state.collectionsPage = 1;
                                    this.setState({ 
                                        collectionsOrderBy: this.state.collectionsOrderBy,
                                        collectionsPage: this.state.collectionsPage 
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
                                            this.state.temporaryCollectionDefaultOrder = selectedCollection.default_order;
                                            this.state.temporaryCollectionDefaultOrderBy = selectedCollection.default_orderby.orderby ? selectedCollection.default_orderby.orderby : selectedCollection.default_orderby;
                                            this.state.temporaryCollectionDefaultOrderByMeta = selectedCollection.default_orderby.metakey ? selectedCollection.default_orderby.metakey : '';
                                            this.state.temporaryCollectionDefaultOrderByType = selectedCollection.default_orderby.metatype ? selectedCollection.default_orderby.metatype : '';
                                            this.setState({ temporaryCollectionId: aCollectionId });
                                            this.setState({ temporaryCollectionDefaultViewMode: selectedCollection.default_view_mode });
                                            this.setState({ temporaryCollectionEnabledViewModes: selectedCollection.enabled_view_modes });
                                            this.setState({ temporaryCollectionDefaultOrder: selectedCollection.default_order });
                                            this.setState({ temporaryCollectionDefaultOrderBy: selectedCollection.default_orderby.orderby ? selectedCollection.default_orderby.orderby : selectedCollection.default_orderby });
                                            this.setState({ temporaryCollectionDefaultOrderByMeta: selectedCollection.default_orderby.metakey ? selectedCollection.default_orderby.metakey : '' });
                                            this.setState({ temporaryCollectionDefaultOrderByType: selectedCollection.default_orderby.metatype ? selectedCollection.default_orderby.metatype : '' });
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
                                        this.state.temporaryCollectionDefaultOrder = selectedCollection.default_order;
                                        this.state.temporaryCollectionDefaultOrderBy = selectedCollection.default_orderby;
                                        this.state.temporaryCollectionDefaultOrderBy = selectedCollection.default_orderby.orderby ? selectedCollection.default_orderby.orderby : selectedCollection.default_orderby;
                                        this.state.temporaryCollectionDefaultOrderByMeta = selectedCollection.default_orderby.metakey ? selectedCollection.default_orderby.metakey : '';
                                        this.state.temporaryCollectionDefaultOrderByType = selectedCollection.default_orderby.metatype ? selectedCollection.default_orderby.metatype : '';
                                        this.setState({ temporaryCollectionId: aCollectionId });
                                        this.setState({ temporaryCollectionDefaultViewMode: selectedCollection.default_view_mode });
                                        this.setState({ temporaryCollectionEnabledViewModes: selectedCollection.enabled_view_modes });
                                        this.setState({ temporaryCollectionDefaultOrder: selectedCollection.default_order });
                                        this.setState({ temporaryCollectionDefaultOrderBy: selectedCollection.default_orderby });
                                        this.setState({ temporaryCollectionDefaultOrderBy: selectedCollection.default_orderby.orderby ? selectedCollection.default_orderby.orderby : selectedCollection.default_orderby });
                                        this.setState({ temporaryCollectionDefaultOrderByMeta: selectedCollection.default_orderby.metakey ? selectedCollection.default_orderby.metakey : '' });
                                        this.setState({ temporaryCollectionDefaultOrderByType: selectedCollection.default_orderby.metatype ? selectedCollection.default_orderby.metatype : '' });
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
                    ) : this.state.isLoadingCollections ? <div class="spinner-container"><Spinner /></div> :
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
                        onClick={ () => this.selectCollection({ 
                            collectionId: this.state.temporaryCollectionId,
                            collectionDefaultViewMode: this.state.temporaryCollectionDefaultViewMode,
                            collectionEnabledViewModes: this.state.temporaryCollectionEnabledViewModes,
                            collectionDefaultOrder: this.state.temporaryCollectionDefaultOrder,
                            collectionDefaultOrderBy: this.state.temporaryCollectionDefaultOrderBy,
                            collectionDefaultOrderByMeta: this.state.temporaryCollectionDefaultOrderByMeta,
                            collectionDefaultOrderByType: this.state.temporaryCollectionDefaultOrderByType
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