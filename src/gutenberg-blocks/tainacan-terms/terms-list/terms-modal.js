import tainacan from '../../api-client/axios.js';
import axios from 'axios';

const { __ } = wp.i18n;

const { TextControl, Button, Modal, CheckboxControl, RadioControl, Spinner } = wp.components;

export default class TermsModal extends React.Component {
    constructor(props) {
        super(props);

        // Initialize state
        this.state = {
            modalTerms: [],
            totalModalTerms: 0,
            termsPerPage: 24,
            searchTermName: '',
            temporarySelectedTerms: [],
            terms: [],
            isLoadingTerms: false,
            taxonomyId: undefined,  
            taxonomyName: '', 
            isLoadingTaxonomies: false, 
            modalTaxonomies: [],
            totalModalTaxonomies: 0, 
            taxonomyPage: 1,
            temporaryTaxonomyId: '',
            searchTaxonomyName: '',
            taxonomies: [],
        };
        
        // Bind events
        this.fetchTerms = this.fetchTerms.bind(this);
        this.fetchModalTerms = this.fetchModalTerms.bind(this);
        this.isTemporaryTermSelected = this.isTemporaryTermSelected.bind(this);
        this.toggleSelectTemporaryTerm = this.toggleSelectTemporaryTerm.bind(this);
        this.selectTemporaryTerm = this.selectTemporaryTerm.bind(this);
        this.removeTemporaryTermOfId = this.removeTemporaryTermOfId.bind(this);
        this.applySelectedTerms = this.applySelectedTerms.bind(this);
        this.resetTaxonomies = this.resetTaxonomies.bind(this);
        this.selectTaxonomy = this.selectTaxonomy.bind(this);
        this.fetchTaxonomies = this.fetchTaxonomies.bind(this);
        this.fetchModalTaxonomies = this.fetchModalTaxonomies.bind(this);
        this.fetchTaxonomy = this.fetchTaxonomy.bind(this);
    }

    componentWillMount() {
        
        this.setState({ 
            taxonomyId: this.props.existingTaxonomyId,
            temporarySelectedTerms: JSON.parse(JSON.stringify(this.props.selectedTermsObject))
        });

        if (this.props.existingTaxonomyId != null && this.props.existingTaxonomyId != undefined) {
            this.fetchTaxonomy(this.props.existingTaxonomyId);
            this.fetchModalTerms(0, this.props.existingTaxonomyId);
        } else {
            this.setState({ taxonomyPage: 1 });
            this.fetchModalTaxonomies();
        }
    }

    // TERMS RELATED --------------------------------------------------
    selectTemporaryTerm(term) {
        let existingTermIndex = this.state.temporarySelectedTerms.findIndex((existingTerm) => (existingTerm.id == 'term-id-' + term.id) || (existingTerm.id == term.id));

        if (existingTermIndex < 0) {
            let termId = isNaN(term.id) ? term.id : 'term-id-' + term.id;
            let aTemporarySelectedTerms = this.state.temporarySelectedTerms;
            aTemporarySelectedTerms.push({
                id: termId,
                name: term.name,
                url: term.url,
                header_image: term.header_image
            });
            this.setState({ temporarySelectedTerms: aTemporarySelectedTerms });
        }
    }

    removeTemporaryTermOfId(termId) {

        let existingTermIndex = this.state.temporarySelectedTerms.findIndex((existingTerm) => ((existingTerm.id == 'term-id-' + termId) || (existingTerm.id == termId)));

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

    cancelSelection() {

        this.setState({
            modalTerms: [],
            modalTaxonomies: []
        });

        this.props.onCancelSelection();
    }

    isTemporaryTermSelected(termId) {
        return this.state.temporarySelectedTerms.findIndex(term => (term.id == termId) || (term.id == 'term-id-' + termId)) >= 0;
    }

    toggleSelectTemporaryTerm(term, isChecked) {
        if (isChecked)
            this.selectTemporaryTerm(term);
        else
            this.removeTemporaryTermOfId(term.id);
        
        this.setState({ temporarySelectedTerms: this.state.temporarySelectedTerms });
        // setContent();
    }

    fetchTerms(name) {

        let endpoint = '/taxonomy/'+ this.state.taxonomyId + '/terms/?order=asc&hideempty=0&number=' + this.state.termsPerPage;

        if (name != undefined && name != '')
            endpoint += '&searchterm=' + name;

        tainacan.get(endpoint)
            .then(response => {

                let someTerms = response.data.map((term) => ({ 
                    name: term.name, 
                    id: term.id,
                    url: term.url,
                    header_image: [{
                        src: term.header_image,
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

    fetchModalTerms(offset, taxonomyId) {

        let someModalTerms = this.state.modalTerms;

        if (offset <= 0)
            someModalTerms = [];

        let endpoint = '/taxonomy/'+ taxonomyId + '/terms/?order=asc&hideempty=0&number=' + this.state.termsPerPage + '&offset=' + offset;

        this.setState({ 
            isLoadingTerms: true, 
            modalTerms: someModalTerms
        });

        tainacan.get(endpoint)
            .then(response => {

                let otherModalTerms = this.state.modalTerms;
                for (let term of response.data) {
                    otherModalTerms.push({ 
                        name: term.name, 
                        id: term.id,
                        url: term.url,
                        header_image: [{
                            src: term.header_image,
                            alt: term.name
                        }]
                    });
                }
                
                this.setState({ 
                    isLoadingTerms: false, 
                    modalTerms: otherModalTerms,
                    totalModalTerms: response.headers['x-wp-total']
                });
                
                return otherModalTerms;
            })
            .catch(error => {
                console.log('Error trying to fetch terms: ' + error);
            });
    }

    // TAXONOMY RELATED --------------------------------------------------
    fetchModalTaxonomies() {

        let someModalTaxonomies = this.state.modalTaxonomies;
        if (this.state.taxonomyPage <= 1)
            someModalTaxonomies = [];

        let endpoint = '/taxonomies/?orderby=title&order=asc&perpage=' + this.state.termsPerPage + '&paged=' + this.state.taxonomyPage;

        this.setState({ 
            isLoadingTaxonomies: true,
            taxonomyPage: this.state.taxonomyPage + 1, 
            modalTaxonomies: someModalTaxonomies
        });

        tainacan.get(endpoint)
            .then(response => {

                let otherModalTaxonomies = this.state.modalTaxonomies;
                for (let taxonomy of response.data) {
                    otherModalTaxonomies.push({ 
                        name: taxonomy.name, 
                        id: taxonomy.id
                    });
                }

                this.setState({ 
                    isLoadingTaxonomies: false, 
                    modalTaxonomies: otherModalTaxonomies,
                    totalModalTaxonomies: response.headers['x-wp-total']
                });
            
                return otherModalTaxonomies;
            })
            .catch(error => {
                console.log('Error trying to fetch taxonomies: ' + error);
            });
    }

    fetchTaxonomy(taxonomyId) {
        tainacan.get('/taxonomies/' + taxonomyId)
            .then((response) => {
                this.setState({ taxonomyName: response.data.name });
            }).catch(error => {
                console.log('Error trying to fetch taxonomy: ' + error);
            });
    }

    selectTaxonomy(selectedTaxonomyId) {
        this.setState({
            taxonomyId: selectedTaxonomyId
        });
        this.props.onSelectTaxonomy(selectedTaxonomyId);
        this.fetchTaxonomy(selectedTaxonomyId);
        this.fetchModalTerms(0, selectedTaxonomyId);
        //setContent();
    }

    fetchTaxonomies(name) {

        this.setState({ 
            isLoadingTaxonomies: true, 
            taxonomies: [],
            terms: []
        });

        let endpoint = '/taxonomies/?orderby=title&order=asc&perpage=' + this.state.termsPerPage;
        if (name != undefined && name != '')
            endpoint += '&search=' + name;

        tainacan.get(endpoint)
            .then(response => {
                let someTaxonomies = response.data.map((taxonomy) => ({ name: taxonomy.name, id: taxonomy.id + '' }));

                this.setState({ 
                    isLoadingTaxonomies: false, 
                    taxonomies: someTaxonomies
                });
                
                return someTaxonomies;
            })
            .catch(error => {
                console.log('Error trying to fetch taxonomies: ' + error);
            });
    }

    resetTaxonomies() {

        this.setState({ 
            taxonomyId: null,
            taxonomyPage: 1,
            modalTaxonomies: []
        });
        this.fetchModalTaxonomies(); 
    }

    render() {
        return this.state.taxonomyId != null && this.state.taxonomyId != undefined ? (
            // Terms modal
            <Modal
                    className="wp-block-tainacan-modal"
                    title={__('Select the desired terms from taxonomy ' + this.state.taxonomyName, 'tainacan')}
                    onRequestClose={ () => this.cancelSelection() }
                    contentLabel={__('Select terms', 'tainacan')}>
                    
                <div>
                    <div className="modal-search-area">
                        <TextControl 
                                label={__('Search for a term', 'tainacan')}
                                value={ this.state.searchTermName }
                                onChange={(value) => {
                                    this.setState({ 
                                        searchTermName: value
                                    });
                                    _.debounce(this.fetchTerms(value), 300);
                                }}/>
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
                                        { term.header_image ?
                                            <img
                                                aria-hidden
                                                src={ term.header_image && term.header_image[0] && term.header_image[0].src ? term.header_image[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                                                alt={ term.header_image && term.header_image[0] ? term.header_image[0].alt : term.name }/>
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
                                        { term.header_image ?
                                            <img
                                                aria-hidden
                                                src={ term.header_image && term.header_image[0] && term.header_image[0].src ? term.header_image[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                                                alt={ term.header_image && term.header_image[0] ? term.header_image[0].alt : term.name }/>
                                            : null
                                        }
                                        <CheckboxControl
                                            label={ term.name }
                                            checked={ this.isTemporaryTermSelected(term.id) }
                                            onChange={ ( isChecked ) => { this.toggleSelectTemporaryTerm(term, isChecked) } } />
                                    </li>
                                )
                            }                                                
                            </ul>
                            { this.state.isLoadingTerms ? <div class="spinner-container"><Spinner /></div> : null }
                            <div className="modal-loadmore-section">
                                <p>{ __('Showing', 'tainacan') + " " + this.state.modalTerms.length + " " + __('of', 'tainacan') + " " + this.state.totalModalTerms + " " + __('terms', 'tainacan') + "."}</p>
                                {
                                    this.state.modalTerms.length < this.state.totalModalTerms ? (
                                    <Button 
                                        isDefault
                                        isSmall
                                        onClick={ () => this.fetchModalTerms(this.state.modalTerms.length, this.state.taxonomyId) }>
                                        {__('Load more', 'tainacan')}
                                    </Button>
                                    ) : null
                                }
                            </div>
                        </div>
                    ) : this.state.isLoadingTerms ? <div class="spinner-container"><Spinner /></div> :
                    <div className="modal-loadmore-section">
                        <p>{ __('Sorry, no terms found.', 'tainacan') }</p>
                    </div>
                )}
                <div className="modal-footer-area">
                    <Button
                        isDefault
                        onClick={ () => this.resetTaxonomies() }>
                        {__('Switch taxonomy', 'tainacan')}
                    </Button>
                    <Button 
                        isPrimary
                        onClick={ () => this.applySelectedTerms() }>
                        {__('Finish', 'tainacan')}
                    </Button>
                </div>
            </div>
        </Modal>
    ) : (
        // Taxonomies modal
        <Modal
                className="wp-block-tainacan-modal"
                title={__('Select a taxonomy to fetch terms from', 'tainacan')}
                onRequestClose={ () => this.cancelSelection() }
                contentLabel={__('Select terms', 'tainacan')}>
                <div>
                    <div className="modal-search-area">
                        <TextControl 
                                label={__('Search for a taxonomy', 'tainacan')}
                                value={ this.state.searchTaxonomyName }
                                onChange={(value) => {
                                    this.setState({ 
                                        searchTaxonomyName: value
                                    });
                                    _.debounce(this.fetchTaxonomies(value), 300);
                                }}/>
                    </div>
                    {(
                    this.state.searchTaxonomyName != '' ? (
                        this.state.taxonomies.length > 0 ?
                        (
                            <div>
                                <div className="modal-radio-list">
                                    {
                                    <RadioControl
                                        selected={ this.state.temporaryTaxonomyId }
                                        options={
                                            this.state.taxonomies.map((taxonomy) => {
                                                return { label: taxonomy.name, value: '' + taxonomy.id }
                                            })
                                        }
                                        onChange={ ( aTaxonomyId ) => { 
                                            this.setState({ temporaryTaxonomyId: aTaxonomyId });
                                        } } />
                                    }                                      
                                </div>
                            </div>
                        ) :
                        this.state.isLoadingTaxonomies ? (
                            <div class="spinner-container"><Spinner /></div>
                        ) :
                        <div className="modal-loadmore-section">
                            <p>{ __('Sorry, no taxonomy found.', 'tainacan') }</p>
                        </div> 
                    ):
                    this.state.modalTaxonomies.length > 0 ? 
                    (   
                        <div>
                            <div className="modal-radio-list">
                                {
                                <RadioControl
                                    selected={ this.state.temporaryTaxonomyId }
                                    options={
                                        this.state.modalTaxonomies.map((taxonomy) => {
                                            return { label: taxonomy.name, value: '' + taxonomy.id }
                                        })
                                    }
                                    onChange={ ( aTaxonomyId ) => { 
                                        this.setState({ temporaryTaxonomyId: aTaxonomyId });
                                    } } />
                                }                                     
                            </div>
                            <div className="modal-loadmore-section">
                                <p>{ __('Showing', 'tainacan') + " " + this.state.modalTaxonomies.length + " " + __('of', 'tainacan') + " " + this.state.totalModalTaxonomies + " " + __('taxonomies', 'tainacan') + "."}</p>
                                {
                                    this.state.modalTaxonomies.length < this.state.totalModalTaxonomies ? (
                                    <Button 
                                        isDefault
                                        isSmall
                                        onClick={ () => this.fetchModalTaxonomies() }>
                                        {__('Load more', 'tainacan')}
                                    </Button>
                                    ) : null
                                }
                            </div>
                        </div>
                    ) : this.state.isLoadingTaxonomies ? <div class="spinner-container"><Spinner /></div> :
                    <div className="modal-loadmore-section">
                        <p>{ __('Sorry, no taxonomy found.', 'tainacan') }</p>
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
                        disabled={ this.state.temporaryTaxonomyId == undefined || this.state.temporaryTaxonomyId == null || this.state.temporaryTaxonomyId == ''}
                        onClick={ () => this.selectTaxonomy(this.state.temporaryTaxonomyId) }>
                        {__('Select terms', 'tainacan')}
                    </Button>
                </div>
            </div>
        </Modal> 
        );
    }
}