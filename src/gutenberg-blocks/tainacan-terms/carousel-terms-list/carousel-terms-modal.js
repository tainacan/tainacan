import tainacan from '../../api-client/axios.js';
import axios from 'axios';

const { __ } = wp.i18n;

const { TextControl, Button, Modal, CheckboxControl, Spinner } = wp.components;

export default class TermsModal extends React.Component {
    constructor(props) {
        super(props);

        // Initialize state
        this.state = {
            searchTermName: '',
            termsRequestSource: undefined,
            terms: [],
            temporarySelectedTerms: [], 
            isLoadingTerms: false, 
            modalTerms: [],
            totalModalTerms: 0,
            termsPerPage: 24,
            termsPage: 1,
        };
        
        // Bind events
        this.selectTemporaryTerm = this.selectTemporaryTerm.bind(this);
        this.removeTemporaryTermOfId = this.removeTemporaryTermOfId.bind(this);
        this.applySelectedTerms = this.applySelectedTerms.bind(this);
        this.isTemporaryTermSelected = this.isTemporaryTermSelected.bind(this);
        this.toggleSelectTemporaryTerm = this.toggleSelectTemporaryTerm.bind(this);
        this.cancelSelection = this.cancelSelection.bind(this);
        this.selectTerm = this.selectTerm.bind(this);
        this.fetchModalTerms = this.fetchModalTerms.bind(this);
        this.fetchTerms = this.fetchTerms.bind(this);
    }

    componentWillMount() {

        this.fetchModalTerms();
        
        this.setState( { 
            terms: [], 
            termsPage: 1,
            temporarySelectedTerms: JSON.parse(JSON.stringify(this.props.selectedTermsObject))
        } );
    }

    selectTemporaryTerm(term) {
        let existingTermIndex = this.state.temporarySelectedTerms.findIndex((existingTerm) => existingTerm.id == term.id);

        if (existingTermIndex < 0) {
            let aTemporarySelectedTerms = this.state.temporarySelectedTerms;
            aTemporarySelectedTerms.push({
                id: term.id,
                name: term.name,
                url: term.url,
                thumbnail: term.thumbnail
            });
            this.setState({ temporarySelectedTerms: aTemporarySelectedTerms });
        }
    }

    removeTemporaryTermOfId(termId) {

        let existingTermIndex = this.state.temporarySelectedTerms.findIndex((existingTerm) => existingTerm.id == termId);

        if (existingTermIndex >= 0) {
            let aTemporarySelectedTerms = this.state.temporarySelectedTerms;
            aTemporarySelectedTerms.splice(existingTermIndex, 1);
            this.setState({ temporarySelectedTerms: aTemporarySelectedTerms });
        }
    }

    applySelectedTerms() {
        let aSelectedTermsObject = JSON.parse(JSON.stringify(this.state.temporarySelectedTerms));
        this.props.onApplySelection(aSelectedTermsObject);
    }

    isTemporaryTermSelected(termId) {
        return this.state.temporarySelectedTerms.findIndex(term => term.id == termId) >= 0;
    }

    toggleSelectTemporaryTerm(term, isChecked) {
        if (isChecked)
            this.selectTemporaryTerm(term);
        else
            this.removeTemporaryTermOfId(term.id);
    }

    cancelSelection() {

        this.setState({
            termsPage: 1,
            modalTerms: []
        });

        this.props.onCancelSelection();
    }

    selectTerm(selectedTermId) {

        this.setState({
            termId: selectedTermId
        });
        this.fetchTerm();
        this.fetchModalTerms();
    }

    fetchModalTerms() {

        let currentModalTerms = this.state.modalTerms;
        if (this.state.termsPage <= 1)
            currentModalTerms = [];

        let endpoint = '/terms/?orderby=title&order=asc&perpage=' + this.state.termsPerPage + '&paged=' + this.state.termsPage;
        
        this.setState({ 
            isLoadingTerms: true, 
            modalTerms: currentModalTerms,
        });

        tainacan.get(endpoint)
            .then(response => {

                for (let term of response.data) {
                    currentModalTerms.push({ 
                        name: term.name, 
                        id: term.id,
                        url: term.url,
                        thumbnail: [{
                            src: term.thumbnail['tainacan-medium'] != undefined ? term.thumbnail['tainacan-medium'][0] : term.thumbnail['medium'][0],
                            alt: term.name
                        }]
                    });
                }

                this.setState({
                    termsPage: this.state.termsPage + 1,  
                    isLoadingTerms: false, 
                    modalTerms: currentModalTerms,
                    totalModalTerms: response.headers['x-wp-total']
                });
                
                return currentModalTerms;
            })
            .catch(error => {
                console.log('Error trying to fetch terms: ' + error);
            });
    }

    fetchTerms(name) {
        if (this.state.termsRequestSource != undefined)
            this.state.termsRequestSource.cancel('Previous terms search canceled.');

        let aTermRequestSource = axios.CancelToken.source();
        this.setState({
            termsRequestSource: aTermRequestSource,
            isLoadingTerms: true
        });

        let endpoint = '/terms/?orderby=title&order=asc&perpage=' + this.state.termsPerPage;

        if (name != undefined && name != '')
            endpoint += '&search=' + name;

        tainacan.get(endpoint, { cancelToken: aTermRequestSource.token })
            .then(response => {

                let someTerms = this.state.terms;
                someTerms = response.data.map((term) => ({ 
                    name: term.name, 
                    id: term.id,
                    url: term.url,
                    thumbnail: [{
                        src: term.thumbnail['tainacan-medium'] != undefined ? term.thumbnail['tainacan-medium'][0] : term.thumbnail['medium'][0],
                        alt: term.name
                    }]
                }));

                this.setState({ 
                    isLoadingTerms: false, 
                    terms: someTerms
                });

                return someTerms;
            })
            .catch(error => {
                console.log('Error trying to fetch terms: ' + error);
            });
    }

    render() {
        return (
            <Modal
                    className="wp-block-tainacan-modal"
                    title={__('Select the desired terms from your Taxonomy', 'tainacan')}
                    onRequestClose={ () => this.cancelSelection() }
                    contentLabel={__('Select terms', 'tainacan')}>

                <div>
                    <div className="modal-search-area">
                        <TextControl 
                                label={__('Search for a term', 'tainacan')}
                                value={ this.state.searchTermName }
                                onInput={(value) => {
                                    this.setState({ 
                                        searchTermName: value.target.value
                                    });
                                }}
                                onChange={(value) => this.fetchTerms(value)}/>
                    </div>
                    {(
                    this.state.searchTermName != '' ? ( 

                        this.state.terms.length > 0 ?
                        (
                            <div>
                                <ul className="modal-checkbox-list">
                                {
                                    this.state.terms.map((term) =>
                                    <li 
                                        key={ term.id }
                                        className="modal-checkbox-list-item">
                                        { term.thumbnail ?
                                            <img
                                                aria-hidden
                                                src={ term.thumbnail && term.thumbnail[0] && term.thumbnail[0].src ? term.thumbnail[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                                                alt={ term.thumbnail && term.thumbnail[0] ? term.thumbnail[0].alt : term.name }/>
                                            : null
                                        }
                                        <CheckboxControl
                                            label={ term.name }
                                            checked={ this.isTemporaryTermSelected(term.id) }
                                            onChange={ ( isChecked ) => { this.toggleSelectTemporaryTerm(term, isChecked) } }
                                        />
                                    </li>
                                    )
                                }                                                
                                </ul>
                                { this.state.isLoadingTerms ? <div class="spinner-container"><Spinner /></div> : null }
                            </div>
                        )
                        : this.state.isLoadingTerms ? <div class="spinner-container"><Spinner /></div> :
                        <div className="modal-loadmore-section">
                            <p>{ __('Sorry, no terms found.', 'tainacan') }</p>
                        </div>
                    ) : 
                    this.state.modalTerms.length > 0 ? 
                    (   
                        <div>
                            <ul className="modal-checkbox-list">
                            {
                                this.state.modalTerms.map((term) =>
                                    <li 
                                        key={ term.id }
                                        className="modal-checkbox-list-item">
                                        { term.thumbnail ?
                                            <img
                                                aria-hidden
                                                src={ term.thumbnail && term.thumbnail[0] && term.thumbnail[0].src ? term.thumbnail[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                                                alt={ term.thumbnail && term.thumbnail[0] ? term.thumbnail[0].alt : term.name }/>
                                            : null
                                        }
                                        <CheckboxControl
                                            label={ term.name }
                                            checked={ this.isTemporaryTermSelected(term.id) }
                                            onChange={ ( isChecked ) => { this.toggleSelectTemporaryTerm(term, isChecked) } } />
                                    </li>
                                )
                            } 
                            { this.state.isLoadingTerms ? <div class="spinner-container"><Spinner /></div> : null }                                               
                            </ul>
                            <div className="modal-loadmore-section">
                                <p>{ __('Showing', 'tainacan') + " " + this.state.modalTerms.length + " " + __('of', 'tainacan') + " " + this.state.totalModalTerms + " " + __('terms', 'tainacan') + "."}</p>
                                {
                                    this.state.modalTerms.length < this.state.totalModalTerms ? (
                                    <Button 
                                        isDefault
                                        isSmall
                                        onClick={ () => this.fetchModalTerms() }>
                                        {__('Load more', 'tainacan')}
                                    </Button>
                                    ) : null
                                }
                            </div>
                        </div>
                    ) : this.state.isLoadingTerms ? <Spinner /> :
                    <div className="modal-loadmore-section">
                        <p>{ __('Sorry, no terms found.', 'tainacan') }</p>
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
                        onClick={ () => this.applySelectedTerms() }>
                        {__('Finish', 'tainacan')}
                    </Button>
                </div>
            </div>
        </Modal>
        );
    }
}