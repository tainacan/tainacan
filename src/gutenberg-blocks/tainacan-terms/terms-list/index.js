const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { TextControl, IconButton, Button, Modal, CheckboxControl, RadioControl, Spinner, ToggleControl, Placeholder, Toolbar } = wp.components;



const { InspectorControls, BlockControls } = wp.editor;

import Autocomplete from 'react-autocomplete';

import tainacan from '../../api-client/axios.js';
import qs from 'qs';

registerBlockType('tainacan/terms-list', {
    title: __('Tainacan Terms List', 'tainacan'),
    icon:
        <svg width="24" height="24" viewBox="0 -2 12 16">
            <path
                d="M 4.4,2.5 H 0 V 0 h 4.4 l 1.2,1.3 z m -1.9,5 v 3.1 H 5 v 1.2 H 1.3 v -8 H 2.5 V 6.3 H 5 V 7.6 H 2.5 Z m 8.2,0.7 H 6.3 V 5.7 h 4.4 l 1.2,1.2 z M 11.9,11.3 10.7,10 H 6.3 v 2.5 h 4.4 z"/>       
        </svg>,
    category: 'tainacan-blocks',
    keywords: [ __( 'Tainacan', 'tainacan' ), __( 'terms', 'tainacan' ), __( 'taxonomy', 'tainacan' ) ],
    attributes: {
        selectedTermsObject: {
            type: 'array',
            source: 'query',
            selector: 'a',
            query: {
                id: {
                    type: 'string',
                    source: 'attribute',
                    attribute: 'id'
                },
                url: {
                    type: 'string',
                    source: 'attribute',
                    attribute: 'href'
                },
                name: {
                    type: 'string',
                    source: 'text'
                },
                header_image: {
                    source: 'query',
                    selector: 'img',
                    query: {
                        src: {
                            source: 'attribute',
                            attribute: 'src'
                        },
                        alt: {
                            source: 'attribute',
                            attribute: 'alt'
                        },
                    }
                }, 
            },
            default: []
        },
        content: {
            type: 'array',
            source: 'children',
            selector: 'div'
        },
        termsPerPage: {
            type: Number,
            default: 12
        },
        query: {
            type: Object,
            default: {}
        },
        taxonomyId: {
            type: String,
            default: ''
        },
        taxonomyName: {
            type: String,
            default: ''
        },
        isLoadingTaxonomies: {
            type: Boolean,
            default: false
        },
        isLoadingTerms: {
            type: Boolean,
            default: false
        },
        taxonomies: {
            type: Array,
            default: []
        },
        terms: {
            type: Array,
            default: []
        },
        selectedTermsHTML: {
            type: Array,
            default: []
        },
        searchTermName: {
            type: String,
            default: ''
        },
        showImage: {
            type: Boolean,
            default: true
        },
        layout: {
            type: String,
            default: 'grid'
        },
        isModalOpen: {
            type: Boolean,
            default: false
        },
        modalTerms: {
            type: Array,
            default: []
        },
        totalModalTerms: {
            type: Number,
            default: 0
        },
        modalTaxonomies: {
            type: Array,
            default: []
        },
        totalModalTaxonomies: {
            type: Number,
            default: 0
        },
        searchTaxonomyName: {
            type: String,
            default: ''
        },
    },
    supports: {
        align: ['full', 'left', 'right', 'wide'],
        html: false,
    },
    edit({ attributes, setAttributes, className, isSelected }){
        let { 
            selectedTermsObject, 
            selectedTermsHTML, 
            terms, 
            content, 
            searchTermName, 
            taxonomyId, 
            taxonomyName, 
            isLoadingTerms, 
            isLoadingTaxonomies, 
            taxonomies,
            modalTaxonomies,
            totalModalTaxonomies, 
            searchTaxonomyName, 
            showImage,
            layout,
            isModalOpen,
            modalTerms,
            totalModalTerms
        } = attributes;

        function prepareTerm(term) {
            return (
                <li 
                    key={ term.id }
                    className="term-list-item">
                    <IconButton
                        onClick={ () => removeTermOfId(term.id) }
                        icon="no-alt"
                        label={__('Remove', 'tainacan')}/>         
                    <a 
                        id={ isNaN(term.id) ? term.id : 'term-id-' + term.id }
                        href={ term.url } 
                        target="_blank">
                        { term.header_image && showImage ?
                        <img
                            src={ term.header_image && term.header_image[0] && term.header_image[0].src ? term.header_image[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                            alt={ term.header_image && term.header_image[0] ? term.header_image[0].alt : term.name }/>
                        : null
                        }
                        { term.name ? term.name : '' }
                    </a>
                </li>
            );
        }

        function renderTaxonomyModalContent() {
            return (
                <div>
                    <TextControl 
                            label={__('Search for a taxonomy', 'tainacan')}
                            value={ searchTaxonomyName }
                            onChange={(value) => {
                                setAttributes({ 
                                    searchTaxonomyName: value
                                });
                                _.debounce(fetchTaxonomies(value), 300);
                            }}/>
                    {(
                    taxonomies.length > 0 && searchTaxonomyName != '' ? 
                    (
                        <div>
                            <ul className="modal-terms-list">
                            {
                                taxonomies.map((taxonomy) =>
                                <li 
                                    key={ taxonomy.id }
                                    className="modal-terms-list-item">
                                    <RadioControl
                                        label={ taxonomy.name }
                                        selected={ isTaxonomySelected(taxonomy.id) }
                                        onChange={ ( isChecked ) => { toggleSelectTaxonomy(taxonomy, isChecked) } }
                                    />
                                </li>
                                )
                            }                                                
                            </ul>
                            { isLoadingTaxonomies ? <Spinner /> : null }
                        </div>
                    )
                    :
                    modalTaxonomies.length > 0 ? 
                    (   
                        <div>
                            <ul className="modal-terms-list">
                            {
                                modalTaxonomies.map((taxonomy) =>
                                    <li 
                                        key={ taxonomy.id }
                                        className="modal-terms-list-item">
                                        {<RadioControl
                                            label={ taxonomy.name }
                                            selected={ isTaxonomySelected(taxonomy.id) }
                                            onChange={ ( isChecked ) => { 
                                                // toggleSelectTaxonomy(term, isChecked)
                                                console.log(isChecked)
                                            } } />
                                        }
                                    </li>
                                )
                            }                                                
                            </ul>
                            { isLoadingTaxonomies ? <Spinner /> : null }
                            <div className="modal-terms-footer">
                                <p>{ __('Showing', 'tainacan') + " " + modalTaxonomies.length + " " + __('of', 'tainacan') + " " + totalModalTaxonomies + " " + __('taxonomies', 'tainacan') + "."}</p>
                                {
                                    modalTaxonomies.length < totalModalTaxonomies ? (
                                    <Button 
                                        isDefault
                                        onClick={ () => fetchModalTaxonomies(modalTaxonomies.length) }>
                                        {__('Load more', 'tainacan')}
                                    </Button>
                                    ) : null
                                }
                            </div>
                        </div>
                    ) : null
                )}
            </div>
            );
        }

        function renderTermsModalContent() {
            return (
                <div>
                    <TextControl 
                            label={__('Search for a term', 'tainacan')}
                            value={ searchTermName }
                            onChange={(value) => {
                                setAttributes({ 
                                    searchTermName: value
                                });
                                _.debounce(fetchTerms(value), 300);
                            }}/>
                    {(
                    terms.length > 0 && searchTermName != '' ? 
                    (
                        <div>
                            <ul className="modal-terms-list">
                            {
                                terms.map((term) =>
                                <li 
                                    key={ term.id }
                                    className="modal-terms-list-item">
                                    <CheckboxControl
                                        label={ term.name }
                                        checked={ isTermSelected(term.id) }
                                        onChange={ ( isChecked ) => { toggleSelectTerm(term, isChecked) } }
                                    />
                                    { term.header_image && showImage ?
                                        <img
                                            aria-hidden
                                            src={ term.header_image && term.header_image[0] && term.header_image[0].src ? term.header_image[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                                            alt={ term.header_image && term.header_image[0] ? term.header_image[0].alt : term.name }/>
                                        : null
                                    }
                                </li>
                                )
                            }                                                
                            </ul>
                            { isLoadingTerms ? <Spinner /> : null }
                        </div>
                    )
                    :
                    modalTerms.length > 0 ? 
                    (   
                        <div>
                            <ul className="modal-terms-list">
                            {
                                modalTerms.map((term) =>
                                    <li 
                                        key={ term.id }
                                        className="modal-terms-list-item">
                                        <CheckboxControl
                                            label={ term.name }
                                            checked={ isTermSelected(term.id) }
                                            onChange={ ( isChecked ) => { toggleSelectTerm(term, isChecked) } } />
                                        { term.header_image && showImage ?
                                            <img
                                                aria-hidden
                                                src={ term.header_image && term.header_image[0] && term.header_image[0].src ? term.header_image[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                                                alt={ term.header_image && term.header_image[0] ? term.header_image[0].alt : term.name }/>
                                            : null
                                        }
                                    </li>
                                )
                            }                                                
                            </ul>
                            { isLoadingTerms ? <Spinner /> : null }
                            <div className="modal-terms-footer">
                                <p>{ __('Showing', 'tainacan') + " " + modalTerms.length + " " + __('of', 'tainacan') + " " + totalModalTerms + " " + __('terms', 'tainacan') + "."}</p>
                                {
                                    modalTerms.length < totalModalTerms ? (
                                    <Button 
                                        isDefault
                                        onClick={ () => fetchModalTerms(modalTerms.length) }>
                                        {__('Load more', 'tainacan')}
                                    </Button>
                                    ) : null
                                }
                                <Button
                                    isPrimary
                                    onClick={ () => resetTaxonomies() }>
                                    {__('Switch taxonomy', 'tainacan')}
                                </Button>
                            </div>
                        </div>
                    ) : null
                )}
            </div>
            );
        }

        function setContent(){

            selectedTermsHTML = [];

            for (let i = 0; i < selectedTermsObject.length; i++)
                selectedTermsHTML.push(prepareTerm(selectedTermsObject[i]));

            setAttributes({
                content: (
                    <ul className={'terms-list  terms-layout-' + layout}>{ selectedTermsHTML }</ul>
                ),
                selectedTermsHTML: selectedTermsHTML
            });
        }

        function fetchTaxonomies(name) {
            isLoadingTaxonomies = true;
            taxonomies = [];
            terms = []

            setAttributes({ 
                isLoadingTaxonomies: isLoadingTaxonomies, 
                taxonomies: taxonomies,
                terms: terms
            });

            let endpoint = '/taxonomies/?perpage=12';
            if (name != undefined && name != '')
                endpoint += '&search=' + name;

            tainacan.get(endpoint)
                .then(response => {
                    taxonomies = response.data.map((taxonomy) => ({ name: taxonomy.name, value: taxonomy.id + "" }));
                    isLoadingTaxonomies = false; 

                    setAttributes({ 
                        isLoadingTaxonomies: isLoadingTaxonomies, 
                        taxonomies: taxonomies
                    });
                    
                    return taxonomies;
                })
                .catch(error => {
                    console.log('Error trying to fetch taxonomies: ' + error);
                });
        }

        function fetchModalTaxonomies(page) {

            if (page <= 1)
                modalTaxonomies = [];

            let endpoint = '/taxonomies/?perpage=12&paged=' + page;

            isLoadingTaxonomies = true;

            setAttributes({ 
                isLoadingTaxonomies: isLoadingTaxonomies, 
                modalTaxonomies: modalTaxonomies
            });

            tainacan.get(endpoint)
                .then(response => {

                    for (let taxonomy of response.data) {
                        modalTaxonomies.push({ 
                            name: taxonomy.name, 
                            id: taxonomy.id
                        });
                    }
                    console.log(modalTaxonomies)
                    isLoadingTaxonomies = false;
                    totalModalTaxonomies = response.headers['x-wp-total']; 

                    setAttributes({ 
                        isLoadingTaxonomies: isLoadingTaxonomies, 
                        modalTaxonomies: modalTaxonomies,
                        totalModalTaxonomies: totalModalTaxonomies
                    });
                    
                    return modalTaxonomies;
                })
                .catch(error => {
                    console.log('Error trying to fetch taxonomies: ' + error);
                });
        }

        function fetchTerms(name) {

            let endpoint = '/taxonomy/'+ taxonomyId + '/terms/?number=12';

            if (name != undefined && name != '')
                endpoint += '&searchterm=' + name;

            tainacan.get(endpoint)
                .then(response => {

                    terms = response.data.map((term) => ({ 
                        name: term.name, 
                        value: term.id + "", // same as string version of id, because autocomplete expects value
                        id: term.id,
                        url: term.url,
                        header_image: [{
                            src: term.header_image,
                            alt: term.name
                        }]
                    }));
                    isLoadingTerms = false; 

                    setAttributes({ 
                        isLoadingTerms: isLoadingTerms, 
                        terms: terms
                    });
                    
                    return terms;
                })
                .catch(error => {
                    console.log('Error trying to fetch terms: ' + error);
                });
        }


        function fetchModalTerms(offset) {

            if (offset <= 0)
                modalTerms = [];

            let endpoint = '/taxonomy/'+ taxonomyId + '/terms/?number=12&offset=' + offset;

            isLoadingTerms = true;

            setAttributes({ 
                isLoadingTerms: isLoadingTerms, 
                modalTerms: modalTerms
            });

            tainacan.get(endpoint)
                .then(response => {

                    for (let term of response.data) {
                        modalTerms.push({ 
                            name: term.name, 
                            value: term.id + "", // same as string version of id, because autocomplete expects value
                            id: term.id,
                            url: term.url,
                            header_image: [{
                                src: term.header_image,
                                alt: term.name
                            }]
                        });
                    }
                    isLoadingTerms = false;
                    totalModalTerms = response.headers['x-wp-total']; 

                    setAttributes({ 
                        isLoadingTerms: isLoadingTerms, 
                        modalTerms: modalTerms,
                        totalModalTerms: totalModalTerms
                    });
                    
                    return modalTerms;
                })
                .catch(error => {
                    console.log('Error trying to fetch terms: ' + error);
                });
        }

        function fetchTaxonomy() {
            tainacan.get('/taxonomies/' + taxonomyId)
                .then((response) => {
                    taxonomyName = response.data.name;
                    setAttributes({ taxonomyName: taxonomyName });
                }).catch(error => {
                    console.log('Error trying to fetch taxonomy: ' + error);
                });
        }

        function resetTaxonomies() {
            taxonomyId = null; 
            
            setAttributes({ 
                taxonomyId: taxonomyId,
            });
            fetchModalTaxonomies(1); 
        }

        function openTermsModal() {
            if (taxonomyId != null && taxonomyId != undefined)
                fetchModalTerms(0);
            else
                fetchModalTaxonomies(1)

            setAttributes( { isModalOpen: true, terms: [] } );
        }

        function isTermSelected(termId) {
            return selectedTermsObject.findIndex(term => (term.id == termId) || (term.id == 'term-id-' + termId)) >= 0;
        }

        function toggleSelectTerm(term, isChecked) {
            if (isChecked)
                selectTerm(term);
            else
                removeTermOfId(term.id);
        }

        function isTaxonomySelected(aTaxonomyId) {
            return aTaxonomyId == taxonomyId;
        }

        function toggleSelectTaxonomy(taxonomy, isChecked) {
            if (isChecked) {
                taxonomyId = taxonomy.id;
                taxonomyName = taxonomy.name;

                setAttributes({
                    taxonomyId: taxonomyId,
                    taxonomyName: taxonomyName
                });
                setContent();
            }
        }

        function selectTerm(term) {
            let existingTermIndex = selectedTermsObject.findIndex((existingTerm) => (existingTerm.id == 'term-id-' + term.id) || (existingTerm.id == term.id));
   
            if (existingTermIndex < 0) {
                let termId = isNaN(term.id) ? term.id : 'term-id-' + term.id;
                selectedTermsObject.push({
                    id: termId,
                    name: term.name,
                    url: term.url,
                    header_image: term.header_image
                });
            }

            setContent();
        }

        function removeTermOfId(termId) {

            let existingTermIndex = selectedTermsObject.findIndex((existingTerm) => ((existingTerm.id == 'term-id-' + termId) || (existingTerm.id == termId)));

            if (existingTermIndex >= 0)
                selectedTermsObject.splice(existingTermIndex, 1);

            setContent();
        }

        function updateLayout(newLayout) {
            layout = newLayout;

            setAttributes({ layout: newLayout });
            setContent();
        }

        // Executed every time Edit function runs
        if (taxonomyId != null && taxonomyId != '')
            fetchTaxonomy();
        
        // Executed only on the first load of page
        if(content && content.length && content[0].type)
            setContent();

        const layoutControls = [
            {
                icon: 'grid-view',
                title: __( 'Grid View' ),
                onClick: () => updateLayout('grid'),
                isActive: layout === 'grid',
            },
            {
                icon: 'list-view',
                title: __( 'List View' ),
                onClick: () => updateLayout('list'),
                isActive: layout === 'list',
            }
        ];

        return (
            <div className={className}>

                <div>
                    <BlockControls>
                        <Toolbar controls={ layoutControls } />
                    </BlockControls>
                </div>

                <div>
                    <InspectorControls>
                        <div style={{ marginTop: '24px' }}>
                            <ToggleControl
                                label={__('Image', 'tainacan')}
                                help={ showImage ? __('Toggle to show term\'s image', 'tainacan') : __('Do not show term\'s image', 'tainacan')}
                                checked={ showImage }
                                onChange={ ( isChecked ) => {
                                        showImage = isChecked;
                                        setAttributes({ showImage: showImage });
                                        setContent();
                                    } 
                                }
                            />
                        </div>
                    </InspectorControls>
                </div>

                { isSelected ? 
                    (
                    <div>
                        { isModalOpen && (
                            <Modal
                                className="wp-block-tainacan-terms-modal"
                                title={__('Select the desired terms', 'tainacan')}
                                onRequestClose={ () => setAttributes( { isModalOpen: false } ) }
                                contentLabel={__('Select terms', 'tainacan')}>
                            
                                { taxonomyId != null && taxonomyId != undefined ? renderTermsModalContent() : renderTaxonomyModalContent() }
                                
                            </Modal>
                        ) }

                        { isLoadingTaxonomies || isLoadingTerms ? <Spinner /> : null }

                        <div className="block-control">
{/*                             
                            <div className="block-control-item">
                                <label 
                                    className="autocomplete-label"
                                    htmlFor="taxonomy-autocomplete">
                                    {__('Select a taxonomy', 'tainacan')}
                                </label>
                                
                                <Autocomplete
                                    inputProps={{ id: 'taxonomy-autocomplete' }}
                                    wrapperProps={{ className: 'react-autocomplete' }}
                                    value={ taxonomyName }
                                    items={ taxonomies }
                                    onSelect={(value, item) => {
                                            taxonomyId = value;
                                            taxonomyName = item.name;
                                            setAttributes({ taxonomyId: taxonomyId, taxonomyName: taxonomyName, taxonomies: [ item ] });
                                        }
                                    }
                                    getItemValue={(taxonomy) => taxonomy.value }
                                    onChange={(event, value) => {
                                            taxonomyId = null;
                                            taxonomyName = value;
                                            setAttributes({ taxonomyId: taxonomyId, taxonomyName: taxonomyName });    
                                            _.debounce(fetchTaxonomies(value), 300);
                                        }
                                    }
                                    renderMenu={ children => (
                                        children.length > 0 ? (
                                        <div className="menu">
                                            { children }
                                        </div>
                                        ) : <span></span>
                                    )}
                                    renderItem={(item, isHighlighted) => (
                                        <div
                                            className={`item ${isHighlighted ? 'item-highlighted' : ''}`}
                                            key={item.value}>
                                            {item.name}
                                        </div>
                                    )}/>
                                </div>    */}
                                <div className="block-control-item">

                                    <label
                                        className="autocomplete-label"
                                        htmlFor="taxonomy-autocomplete">
                                        {__('Select terms to add', 'tainacan')}
                                    </label>
                                    <Button
                                        isPrimary
                                        onClick={ () => openTermsModal() }>
                                        {__('Select terms', 'tainacan')}
                                    </Button>
                                </div>       
                        </div>
                        <hr/>
                    </div>
                    ) : null
                }

                { !selectedTermsHTML.length ? (
                    <Placeholder
                        icon={(
                            <img
                                width={148}
                                src={ `${tainacan_plugin.base_url}/admin/images/tainacan_logo_header.svg` }
                                alt="Tainacan Logo"/>
                        )}
                    />) : null
                }

                <ul className={'terms-list-edit terms-layout-' + layout}>{ selectedTermsHTML }</ul>
                
            </div>
        );
    },
    save({ attributes, className }){
        const { content } = attributes;
        return <div className={className}>{ content }</div>
    }
});