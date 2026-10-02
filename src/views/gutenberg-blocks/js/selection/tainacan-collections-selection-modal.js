import tainacanApi from '../axios.js';
import axios from 'axios';

const { __ } = wp.i18n;

const { TextControl, Button, Modal, CheckboxControl, Spinner } = wp.components;
const currentWPVersion = (typeof tainacan_blocks != 'undefined') ? tainacan_blocks.wp_version : tainacan_plugin.wp_version;

function storedCollectionId(collection, prefixNumericIds) {
    if (prefixNumericIds && !isNaN(collection.id))
        return 'collection-id-' + collection.id;

    return collection.id;
}

function matchesCollectionId(storedId, collectionId) {
    return storedId == collectionId || storedId == ('collection-id-' + collectionId);
}

export default class CollectionsSelectionModal extends React.Component {
    constructor(props) {
        super(props);

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

        this.selectTemporaryCollection = this.selectTemporaryCollection.bind(this);
        this.removeTemporaryCollectionOfId = this.removeTemporaryCollectionOfId.bind(this);
        this.applySelectedCollections = this.applySelectedCollections.bind(this);
        this.isTemporaryCollectionSelected = this.isTemporaryCollectionSelected.bind(this);
        this.toggleSelectTemporaryCollection = this.toggleSelectTemporaryCollection.bind(this);
        this.cancelSelection = this.cancelSelection.bind(this);
        this.fetchModalCollections = this.fetchModalCollections.bind(this);
        this.fetchCollections = this.fetchCollections.bind(this);
        this.buildCollectionsQuery = this.buildCollectionsQuery.bind(this);
    }

    componentWillMount() {
        this.fetchModalCollections();

        this.setState({
            collections: [],
            collectionsPage: 1,
            temporarySelectedCollections: JSON.parse(JSON.stringify(this.props.selectedCollectionsObject || []))
        });
    }

    buildCollectionsQuery({ paged, search }) {
        const params = new URLSearchParams();
        params.set('orderby', 'title');
        params.set('order', 'asc');
        params.set('perpage', String(this.state.collectionsPerPage));

        if (paged)
            params.set('paged', String(paged));

        params.set('status', 'publish');

        if (search)
            params.set('search', search);

        return '/collections/?' + params.toString();
    }

    selectTemporaryCollection(collection) {
        let existingCollectionIndex = this.state.temporarySelectedCollections.findIndex((existingCollection) => matchesCollectionId(existingCollection.id, collection.id));

        if (existingCollectionIndex < 0) {
            let aTemporarySelectedCollections = this.state.temporarySelectedCollections;
            aTemporarySelectedCollections.push({
                id: storedCollectionId(collection, this.props.prefixNumericIds),
                name: collection.name,
                url: collection.url,
                thumbnail: collection.thumbnail
            });
            this.setState({ temporarySelectedCollections: aTemporarySelectedCollections });
        }
    }

    removeTemporaryCollectionOfId(collectionId) {
        let existingCollectionIndex = this.state.temporarySelectedCollections.findIndex((existingCollection) => matchesCollectionId(existingCollection.id, collectionId));

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
        return this.state.temporarySelectedCollections.findIndex((collection) => matchesCollectionId(collection.id, collectionId)) >= 0;
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

    fetchModalCollections() {
        let currentModalCollections = this.state.modalCollections;
        if (this.state.collectionsPage <= 1)
            currentModalCollections = [];

        let endpoint = this.buildCollectionsQuery({ paged: this.state.collectionsPage });

        this.setState({
            isLoadingCollections: true,
            modalCollections: currentModalCollections,
        });

        tainacanApi.get(endpoint)
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
            .catch(() => {
                this.setState({ isLoadingCollections: false });
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

        let endpoint = this.buildCollectionsQuery({ search: name });

        tainacanApi.get(endpoint, { cancelToken: aCollectionRequestSource.token })
            .then(response => {
                let someCollections = response.data.map((collection) => ({
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
                if (!axios.isCancel(error))
                    this.setState({ isLoadingCollections: false });
            });
    }

    renderCollectionOption(collection) {
        return (
            <li
                key={ collection.id }
                className="modal-checkbox-list-item">
                { collection.thumbnail ?
                    <img
                        aria-hidden
                        src={ collection.thumbnail && collection.thumbnail[0] && collection.thumbnail[0].src ? collection.thumbnail[0].src : `${tainacan_blocks.base_url}/assets/images/placeholder_square.png`}
                        alt={ collection.thumbnail && collection.thumbnail[0] ? collection.thumbnail[0].alt : collection.name }/>
                    : null
                }
                <CheckboxControl
                    label={ collection.name }
                    checked={ this.isTemporaryCollectionSelected(collection.id) }
                    onChange={ ( isChecked ) => { this.toggleSelectTemporaryCollection(collection, isChecked) } }
                />
            </li>
        );
    }

    render() {
        const modalTitle = this.props.modalTitle || __('Select the desired collections from your repository', 'tainacan');

        return (
            <Modal
                    className={ 'wp-block-tainacan-modal ' + (currentWPVersion < '5.9' ? 'wp-version-smaller-than-5-9' : '') + (currentWPVersion < '6.1' ? 'wp-version-smaller-than-6-1' : '')  }
                    title={ modalTitle }
                    onRequestClose={ () => this.cancelSelection() }
                    contentLabel={ this.props.contentLabel || __('Select collections', 'tainacan') }>

                <div>
                    <div className="modal-search-area">
                        <TextControl
                                placeholder={ __('Search by collection\'s name', 'tainacan') }
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
                                    this.state.collections.map((collection) => this.renderCollectionOption(collection))
                                }
                                </ul>
                                { this.state.isLoadingCollections ? <div className="spinner-container"><Spinner /></div> : null }
                            </div>
                        )
                        : this.state.isLoadingCollections ? <div className="spinner-container"><Spinner /></div> :
                        <div className="modal-loadmore-section">
                            <p>{ __('Sorry, no collections found.', 'tainacan') }</p>
                        </div>
                    ) :
                    this.state.modalCollections.length > 0 ?
                    (
                        <div>
                            <ul className="modal-checkbox-list">
                            {
                                this.state.modalCollections.map((collection) => this.renderCollectionOption(collection))
                            }
                            { this.state.isLoadingCollections ? <div className="spinner-container"><Spinner /></div> : null }
                            </ul>
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
                    ) : this.state.isLoadingCollections ? <Spinner /> :
                    <div className="modal-loadmore-section">
                        <p>{ __('Sorry, no collections found.', 'tainacan') }</p>
                    </div>
                )}
                <div className="modal-footer-area">
                    <Button
                        isSecondary
                        onClick={ () => this.cancelSelection() }>
                        {__('Cancel', 'tainacan')}
                    </Button>
                    <Button
                        isPrimary
                        type="button"
                        onClick={ () => this.applySelectedCollections() }>
                        {__('Finish', 'tainacan')}
                    </Button>
                </div>
            </div>
        </Modal>
        );
    }
}
