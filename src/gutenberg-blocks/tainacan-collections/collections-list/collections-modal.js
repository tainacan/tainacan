import tainacan from '../../api-client/axios.js';
import axios from 'axios';

const { __ } = wp.i18n;

const { TextControl, Button, Modal, CheckboxControl, Spinner } = wp.components;

export default class CollectionsModal extends React.Component {
    constructor(props) {
        super(props);

        // Initialize state
        this.state = {
            searchCollectionName: '',
            collectionsRequestSource: undefined,
            collections: [],
            temporarySelectedCollections: [], 
            isLoadingCollections: false, 
            modalCollections: [],
            totalModalCollections: 0,
            collectionsPerPage: 24,
            collectionsPage: 1,
        };
        
        // Bind events
        this.selectTemporaryCollection = this.selectTemporaryCollection.bind(this);
        this.removeTemporaryCollectionOfId = this.removeTemporaryCollectionOfId.bind(this);
        this.applySelectedCollections = this.applySelectedCollections.bind(this);
        this.isTemporaryCollectionSelected = this.isTemporaryCollectionSelected.bind(this);
        this.toggleSelectTemporaryCollection = this.toggleSelectTemporaryCollection.bind(this);
        this.cancelSelection = this.cancelSelection.bind(this);
        this.selectCollection = this.selectCollection.bind(this);
        this.fetchModalCollections = this.fetchModalCollections.bind(this);
        this.fetchCollections = this.fetchCollections.bind(this);
    }

    componentWillMount() {

        this.fetchModalCollections();
        
        this.setState( { 
            collections: [], 
            collectionsPage: 1,
            temporarySelectedCollections: JSON.parse(JSON.stringify(this.props.selectedCollectionsObject))
        } );
    }

    selectTemporaryCollection(collection) {
        let existingCollectionIndex = this.state.temporarySelectedCollections.findIndex((existingCollection) => (existingCollection.id == 'collection-id-' + collection.id) || (existingCollection.id == collection.id));

        if (existingCollectionIndex < 0) {
            let collectionId = isNaN(collection.id) ? collection.id : 'collection-id-' + collection.id;
            let aTemporarySelectedCollections = this.state.temporarySelectedCollections;
            aTemporarySelectedCollections.push({
                id: collectionId,
                name: collection.name,
                url: collection.url,
                thumbnail: collection.thumbnail
            });
            this.setState({ temporarySelectedCollections: aTemporarySelectedCollections });
        }
    }

    removeTemporaryCollectionOfId(collectionId) {

        let existingCollectionIndex = this.state.temporarySelectedCollections.findIndex((existingCollection) => ((existingCollection.id == 'collection-id-' + collectionId) || (existingCollection.id == collectionId)));

        if (existingCollectionIndex >= 0) {
            let aTemporarySelectedCollections = this.state.temporarySelectedCollections;
            aTemporarySelectedCollections.splice(existingCollectionIndex, 1);
            this.setState({ temporarySelectedCollections: aTemporarySelectedCollections });
        }
    }

    applySelectedCollections() {
        let aSelectedCollectionsObject = JSON.parse(JSON.stringify(this.state.temporarySelectedCollections));
        this.props.onApplySelection(aSelectedCollectionsObject);
    }

    isTemporaryCollectionSelected(collectionId) {
        return this.state.temporarySelectedCollections.findIndex(collection => (collection.id == collectionId) || (collection.id == 'collection-id-' + collectionId)) >= 0;
    }

    toggleSelectTemporaryCollection(collection, isChecked) {
        if (isChecked)
            this.selectTemporaryCollection(collection);
        else
            this.removeTemporaryCollectionOfId(collection.id);
    }

    cancelSelection() {

        this.setState({
            collectionsPage: 1,
            modalCollections: []
        });

        this.props.onCancelSelection();
    }

    selectCollection(selectedCollectionId) {

        this.setState({
            collectionId: selectedCollectionId
        });
        this.fetchCollection();
        this.fetchModalCollections();
    }

    fetchModalCollections() {

        let currentModalCollections = this.state.modalCollections;
        if (this.state.collectionsPage <= 1)
            currentModalCollections = [];

        let endpoint = '/collections/?orderby=title&order=asc&perpage=' + this.state.collectionsPerPage + '&paged=' + this.state.collectionsPage;
        
        this.setState({ 
            isLoadingCollections: true, 
            modalCollections: currentModalCollections,
        });

        tainacan.get(endpoint)
            .then(response => {

                for (let collection of response.data) {
                    currentModalCollections.push({ 
                        name: collection.name, 
                        id: collection.id,
                        url: collection.url,
                        thumbnail: [{
                            src: collection.thumbnail['tainacan-medium'] != undefined ? collection.thumbnail['tainacan-medium'][0] : collection.thumbnail['medium'][0],
                            alt: collection.name
                        }]
                    });
                }

                this.setState({
                    collectionsPage: this.state.collectionsPage + 1,  
                    isLoadingCollections: false, 
                    modalCollections: currentModalCollections,
                    totalModalCollections: response.headers['x-wp-total']
                });
                
                return currentModalCollections;
            })
            .catch(error => {
                console.log('Error trying to fetch collections: ' + error);
            });
    }

    fetchCollections(name) {
        if (this.state.collectionsRequestSource != undefined)
            this.state.collectionsRequestSource.cancel('Previous collections search canceled.');

        let aCollectionRequestSource = axios.CancelToken.source();
        this.setState({
            collectionsRequestSource: aCollectionRequestSource,
            isLoadingCollections: true
        });

        let endpoint = '/collections/?orderby=title&order=asc&perpage=' + this.state.collectionsPerPage;

        if (name != undefined && name != '')
            endpoint += '&search=' + name;

        tainacan.get(endpoint, { cancelToken: aCollectionRequestSource.token })
            .then(response => {

                let someCollections = this.state.collections;
                someCollections = response.data.map((collection) => ({ 
                    name: collection.name, 
                    id: collection.id,
                    url: collection.url,
                    thumbnail: [{
                        src: collection.thumbnail['tainacan-medium'] != undefined ? collection.thumbnail['tainacan-medium'][0] : collection.thumbnail['medium'][0],
                        alt: collection.name
                    }]
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

    render() {
        return (
            <Modal
                    className="wp-block-tainacan-modal"
                    title={__('Select the desired collections from your repository', 'tainacan')}
                    onRequestClose={ () => this.cancelSelection() }
                    contentLabel={__('Select collections', 'tainacan')}>

                <div>
                    <div className="modal-search-area">
                        <TextControl 
                                label={__('Search for a collection', 'tainacan')}
                                value={ this.state.searchCollectionName }
                                onInput={(value) => {
                                    this.setState({ 
                                        searchCollectionName: value.target.value
                                    });
                                }}
                                onChange={(value) => this.fetchCollections(value)}/>
                    </div>
                    {(
                    this.state.searchCollectionName != '' ? ( 

                        this.state.collections.length > 0 ?
                        (
                            <div>
                                <ul className="modal-checkbox-list">
                                {
                                    this.state.collections.map((collection) =>
                                    <li 
                                        key={ collection.id }
                                        className="modal-checkbox-list-item">
                                        { collection.thumbnail ?
                                            <img
                                                aria-hidden
                                                src={ collection.thumbnail && collection.thumbnail[0] && collection.thumbnail[0].src ? collection.thumbnail[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                                                alt={ collection.thumbnail && collection.thumbnail[0] ? collection.thumbnail[0].alt : collection.name }/>
                                            : null
                                        }
                                        <CheckboxControl
                                            label={ collection.name }
                                            checked={ this.isTemporaryCollectionSelected(collection.id) }
                                            onChange={ ( isChecked ) => { this.toggleSelectTemporaryCollection(collection, isChecked) } }
                                        />
                                    </li>
                                    )
                                }                                                
                                </ul>
                                { this.state.isLoadingCollections ? <div class="spinner-container"><Spinner /></div> : null }
                            </div>
                        )
                        : this.state.isLoadingCollections ? <div class="spinner-container"><Spinner /></div> :
                        <div className="modal-loadmore-section">
                            <p>{ __('Sorry, no collections found.', 'tainacan') }</p>
                        </div>
                    ) : 
                    this.state.modalCollections.length > 0 ? 
                    (   
                        <div>
                            <ul className="modal-checkbox-list">
                            {
                                this.state.modalCollections.map((collection) =>
                                    <li 
                                        key={ collection.id }
                                        className="modal-checkbox-list-item">
                                        { collection.thumbnail ?
                                            <img
                                                aria-hidden
                                                src={ collection.thumbnail && collection.thumbnail[0] && collection.thumbnail[0].src ? collection.thumbnail[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                                                alt={ collection.thumbnail && collection.thumbnail[0] ? collection.thumbnail[0].alt : collection.name }/>
                                            : null
                                        }
                                        <CheckboxControl
                                            label={ collection.name }
                                            checked={ this.isTemporaryCollectionSelected(collection.id) }
                                            onChange={ ( isChecked ) => { this.toggleSelectTemporaryCollection(collection, isChecked) } } />
                                    </li>
                                )
                            } 
                            { this.state.isLoadingCollections ? <div class="spinner-container"><Spinner /></div> : null }                                               
                            </ul>
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
                    ) : this.state.isLoadingCollections ? <Spinner /> :
                    <div className="modal-loadmore-section">
                        <p>{ __('Sorry, no collections found.', 'tainacan') }</p>
                    </div>
                )}
                <div className="modal-footer-area">
                    <Button
                        isDefault
                        onClick={ () => this.cancelSelection() }>
                        {__('Cancel', 'tainacan')}
                    </Button>
                    <Button 
                        isPrimary
                        type="submit"
                        onClick={ () => this.applySelectedCollections() }>
                        {__('Finish', 'tainacan')}
                    </Button>
                </div>
            </div>
        </Modal>
        );
    }
}