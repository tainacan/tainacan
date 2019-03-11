const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { TextControl, IconButton, Button, Modal, CheckboxControl, RadioControl, Spinner, ToggleControl, Placeholder, Toolbar } = wp.components;

const { InspectorControls, BlockControls } = wp.editor;

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
            default: 24
        },
        query: {
            type: Object,
            default: {}
        },
        taxonomyId: {
            type: String,
            default: undefined
        },
        temporaryTaxonomyId: {
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
        temporarySelectedTerms: {
            type: Array,
            default: []
        },
        searchTermName: {
            type: String,
            default: ''
        },
        taxonomyName: {
            type: String,
            default: ''
        },
        showImage: {
            type: Boolean,
            default: true
        },
        showName: {
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
        taxonomyPage: {
            type: Number,
            default: 1
        },
        searchTaxonomyName: {
            type: String,
            default: ''
        },
    },
    supports: {
        align: ['full', 'wide'],
        html: false,
    },
    edit({ attributes, setAttributes, className, isSelected }){
        let { 
            selectedTermsObject, 
            selectedTermsHTML, 
            temporarySelectedTerms,
            terms, 
            content, 
            searchTermName, 
            taxonomyId,  
            taxonomyName, 
            temporaryTaxonomyId, 
            isLoadingTerms, 
            isLoadingTaxonomies, 
            taxonomies,
            modalTaxonomies,
            totalModalTaxonomies, 
            searchTaxonomyName,
            taxonomyPage, 
            showImage,
            showName,
            layout,
            isModalOpen,
            modalTerms,
            totalModalTerms,
            termsPerPage
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
                        target="_blank"
                        className={ (!showName ? 'term-without-name' : '') + ' ' + (!showImage ? 'term-without-image' : '') }>
                        <img
                            src={ term.header_image && term.header_image[0] && term.header_image[0].src ? term.header_image[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                            alt={ term.header_image && term.header_image[0] ? term.header_image[0].alt : term.name }/>
                        <span>{ term.name ? term.name : '' }</span>
                    </a>
                </li>
            );
        }

        function renderTaxonomyModalContent() {
            return (
                <Modal
                    className="wp-block-tainacan-terms-modal"
                    title={__('Select a taxonomy to fetch terms from', 'tainacan')}
                    onRequestClose={ () => setAttributes( { isModalOpen: false } ) }
                    contentLabel={__('Select terms', 'tainacan')}>
                    <div>
                        <div className="modal-terms-search-area">
                            <TextControl 
                                    label={__('Search for a taxonomy', 'tainacan')}
                                    value={ searchTaxonomyName }
                                    onChange={(value) => {
                                        setAttributes({ 
                                            searchTaxonomyName: value
                                        });
                                        _.debounce(fetchTaxonomies(value), 300);
                                    }}/>
                        </div>
                        {(
                        searchTaxonomyName != '' ? (
                            taxonomies.length > 0 ?
                            (
                                <div>
                                    <div className="modal-taxonomies-list">
                                        {
                                        <RadioControl
                                            selected={ temporaryTaxonomyId }
                                            options={
                                                taxonomies.map((taxonomy) => {
                                                    return { label: taxonomy.name, value: '' + taxonomy.id }
                                                })
                                            }
                                            onChange={ ( aTaxonomyId ) => { 
                                                temporaryTaxonomyId = aTaxonomyId;
                                                setAttributes({ temporaryTaxonomyId: temporaryTaxonomyId });
                                            } } />
                                        }                                      
                                    </div>
                                </div>
                            ) :
                            isLoadingTaxonomies ? (
                                <Spinner />
                            ) :
                            <div className="modal-terms-loadmore-section">
                                <p>{ __('Sorry, no taxonomy found.', 'tainacan') }</p>
                            </div> 
                        ):
                        modalTaxonomies.length > 0 ? 
                        (   
                            <div>
                                <div className="modal-taxonomies-list">
                                    {
                                    <RadioControl
                                        selected={ temporaryTaxonomyId }
                                        options={
                                            modalTaxonomies.map((taxonomy) => {
                                                return { label: taxonomy.name, value: '' + taxonomy.id }
                                            })
                                        }
                                        onChange={ ( aTaxonomyId ) => { 
                                            temporaryTaxonomyId = aTaxonomyId;
                                            setAttributes({ temporaryTaxonomyId: temporaryTaxonomyId });
                                        } } />
                                    }                                     
                                </div>
                                <div className="modal-terms-loadmore-section">
                                    <p>{ __('Showing', 'tainacan') + " " + modalTaxonomies.length + " " + __('of', 'tainacan') + " " + totalModalTaxonomies + " " + __('taxonomies', 'tainacan') + "."}</p>
                                    {
                                        modalTaxonomies.length < totalModalTaxonomies ? (
                                        <Button 
                                            isDefault
                                            isSmall
                                            onClick={ () => fetchModalTaxonomies() }>
                                            {__('Load more', 'tainacan')}
                                        </Button>
                                        ) : null
                                    }
                                </div>
                            </div>
                        ) : isLoadingTaxonomies ? <Spinner/> :
                        <div className="modal-terms-loadmore-section">
                            <p>{ __('Sorry, no taxonomy found.', 'tainacan') }</p>
                        </div>
                    )}
                    <div className="modal-terms-footer">
                        <Button 
                            isDefault
                            onClick={ () => {
                                isModalOpen = false;
                                setAttributes({ isModalOpen: isModalOpen })
                            }}>
                            {__('Cancel', 'tainacan')}
                        </Button>
                        <Button 
                            isPrimary
                            disabled={ temporaryTaxonomyId == undefined || temporaryTaxonomyId == null || temporaryTaxonomyId == ''}
                            onClick={ () => selectTaxonomy(temporaryTaxonomyId) }>
                            {__('Select terms', 'tainacan')}
                        </Button>
                    </div>
                </div>
            </Modal>
            );
        }

        function renderTermsModalContent() {
            return (
                <Modal
                        className="wp-block-tainacan-terms-modal"
                        title={__('Select the desired terms for taxonomy ' + taxonomyName, 'tainacan')}
                        onRequestClose={ () => setAttributes( { isModalOpen: false } ) }
                        contentLabel={__('Select terms', 'tainacan')}>
                    <div>
                        <div className="modal-terms-search-area">
                            <TextControl 
                                    label={__('Search for a term', 'tainacan')}
                                    value={ searchTermName }
                                    onChange={(value) => {
                                        setAttributes({ 
                                            searchTermName: value
                                        });
                                        _.debounce(fetchTerms(value), 300);
                                    }}/>
                        </div>
                        {(
                        searchTermName != '' ? ( 

                            terms.length > 0 ?
                            (
                                <div>
                                    <ul className="modal-terms-list">
                                    {
                                        terms.map((term) =>
                                        <li 
                                            key={ term.id }
                                            className="modal-terms-list-item">
                                            { term.header_image && showImage ?
                                                <img
                                                    aria-hidden
                                                    src={ term.header_image && term.header_image[0] && term.header_image[0].src ? term.header_image[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                                                    alt={ term.header_image && term.header_image[0] ? term.header_image[0].alt : term.name }/>
                                                : null
                                            }
                                            <CheckboxControl
                                                label={ term.name }
                                                checked={ isTemporaryTermSelected(term.id) }
                                                onChange={ ( isChecked ) => { toggleSelectTemporaryTerm(term, isChecked) } }
                                            />
                                        </li>
                                        )
                                    }                                                
                                    </ul>
                                </div>
                            )
                            : isLoadingTerms ? <Spinner/> :
                            <div className="modal-terms-loadmore-section">
                                <p>{ __('Sorry, no terms found.', 'tainacan') }</p>
                            </div>
                        ) : 
                        modalTerms.length > 0 ? 
                        (   
                            <div>
                                <ul className="modal-terms-list">
                                {
                                    modalTerms.map((term) =>
                                        <li 
                                            key={ term.id }
                                            className="modal-terms-list-item">
                                            { term.header_image && showImage ?
                                                <img
                                                    aria-hidden
                                                    src={ term.header_image && term.header_image[0] && term.header_image[0].src ? term.header_image[0].src : `${tainacan_plugin.base_url}/admin/images/placeholder_square.png`}
                                                    alt={ term.header_image && term.header_image[0] ? term.header_image[0].alt : term.name }/>
                                                : null
                                            }
                                            <CheckboxControl
                                                label={ term.name }
                                                checked={ isTemporaryTermSelected(term.id) }
                                                onChange={ ( isChecked ) => { toggleSelectTemporaryTerm(term, isChecked) } } />
                                        </li>
                                    )
                                }                                                
                                </ul>
                                <div className="modal-terms-loadmore-section">
                                    <p>{ __('Showing', 'tainacan') + " " + modalTerms.length + " " + __('of', 'tainacan') + " " + totalModalTerms + " " + __('terms', 'tainacan') + "."}</p>
                                    {
                                        modalTerms.length < totalModalTerms ? (
                                        <Button 
                                            isDefault
                                            isSmall
                                            onClick={ () => fetchModalTerms(modalTerms.length) }>
                                            {__('Load more', 'tainacan')}
                                        </Button>
                                        ) : null
                                    }
                                </div>
                            </div>
                        ) : isLoadingTerms ? <Spinner /> :
                        <div className="modal-terms-loadmore-section">
                            <p>{ __('Sorry, no terms found.', 'tainacan') }</p>
                        </div>
                    )}
                    <div className="modal-terms-footer">
                        <Button
                            isDefault
                            onClick={ () => resetTaxonomies() }>
                            {__('Switch taxonomy', 'tainacan')}
                        </Button>
                        <Button 
                            isPrimary
                            onClick={ () => applySelectedTerms() }>
                            {__('Finish', 'tainacan')}
                        </Button>
                    </div>
                </div>
            </Modal>
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

            let endpoint = '/taxonomies/?perpage=' + termsPerPage;
            if (name != undefined && name != '')
                endpoint += '&search=' + name;

            tainacan.get(endpoint)
                .then(response => {
                    taxonomies = response.data.map((taxonomy) => ({ name: taxonomy.name, id: taxonomy.id + '' }));
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

        function fetchModalTaxonomies() {

            if (taxonomyPage <= 1)
                modalTaxonomies = [];

            let endpoint = '/taxonomies/?perpage=' + termsPerPage + '&paged=' + taxonomyPage;

            taxonomyPage++;
            isLoadingTaxonomies = true;

            setAttributes({ 
                isLoadingTaxonomies: isLoadingTaxonomies,
                taxonomyPage: taxonomyPage, 
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

            let endpoint = '/taxonomy/'+ taxonomyId + '/terms/?hideempty=0number=' + termsPerPage;

            if (name != undefined && name != '')
                endpoint += '&searchterm=' + name;

            tainacan.get(endpoint)
                .then(response => {

                    terms = response.data.map((term) => ({ 
                        name: term.name, 
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

            let endpoint = '/taxonomy/'+ taxonomyId + '/terms/?hideempty=0&number=' + termsPerPage + '&offset=' + offset;

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

        function resetTaxonomies() {
            taxonomyId = null; 
            taxonomyPage = 1;
            
            setAttributes({ 
                taxonomyId: taxonomyId,
                taxonomyPage: taxonomyPage
            });
            fetchModalTaxonomies(); 
        }

        function openTermsModal() {
            temporarySelectedTerms = JSON.parse(JSON.stringify(selectedTermsObject));

            if (taxonomyId != null && taxonomyId != undefined) {
                fetchTaxonomy();
                fetchModalTerms(0);
            } else {
                taxonomyPage = 1;
                fetchModalTaxonomies()
            }
            setAttributes( { 
                isModalOpen: true, 
                terms: [], 
                temporarySelectedTerms: temporarySelectedTerms
            } );
        }

        function isTemporaryTermSelected(termId) {
            return temporarySelectedTerms.findIndex(term => (term.id == termId) || (term.id == 'term-id-' + termId)) >= 0;
        }

        function toggleSelectTemporaryTerm(term, isChecked) {
            if (isChecked)
                selectTemporaryTerm(term);
            else
                removeTemporaryTermOfId(term.id);
            
            setAttributes({ temporarySelectedTerms: temporarySelectedTerms });
            setContent();
        }

        function selectTaxonomy(selectedTaxonomyId) {

            taxonomyId = selectedTaxonomyId;

            setAttributes({
                taxonomyId: taxonomyId
            });
            fetchTaxonomy();
            fetchModalTerms(0);
            setContent();
            
        }

        function selectTemporaryTerm(term) {
            let existingTermIndex = temporarySelectedTerms.findIndex((existingTerm) => (existingTerm.id == 'term-id-' + term.id) || (existingTerm.id == term.id));
   
            if (existingTermIndex < 0) {
                let termId = isNaN(term.id) ? term.id : 'term-id-' + term.id;
                temporarySelectedTerms.push({
                    id: termId,
                    name: term.name,
                    url: term.url,
                    header_image: term.header_image
                });
            }
        }

        function removeTemporaryTermOfId(termId) {

            let existingTermIndex = temporarySelectedTerms.findIndex((existingTerm) => ((existingTerm.id == 'term-id-' + termId) || (existingTerm.id == termId)));

            if (existingTermIndex >= 0)
                temporarySelectedTerms.splice(existingTermIndex, 1);
        }

        function applySelectedTerms() {
            selectedTermsObject = JSON.parse(JSON.stringify(temporarySelectedTerms));
            isModalOpen = false;

            setAttributes({ 
                selectedTermsObject: selectedTermsObject, 
                isModalOpen: isModalOpen
            });

            setContent();
        }

        function removeTermOfId(termId) {

            let existingTermIndex = selectedTermsObject.findIndex((existingTerm) => ((existingTerm.id == 'term-id-' + termId) || (existingTerm.id == termId)));

            if (existingTermIndex >= 0)
                selectedTermsObject.splice(existingTermIndex, 1);

            setContent();
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

        function updateLayout(newLayout) {
            layout = newLayout;

            if (layout == 'grid' && showImage == false)
                showImage = true;

            if (layout == 'list' && showName == false)
                showName = true;

            setAttributes({ 
                layout: layout, 
                showImage: showImage,
                showName: showName
            });
            setContent();
        }

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
                            { layout == 'list' ? 
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
                            : null }
                            { layout == 'grid' ? 
                                <ToggleControl
                                    label={__('Name', 'tainacan')}
                                    help={ showName ? __('Toggle to show term\'s name', 'tainacan') : __('Do not show term\'s name', 'tainacan')}
                                    checked={ showName }
                                    onChange={ ( isChecked ) => {
                                            showName = isChecked;
                                            setAttributes({ showName: showName });
                                            setContent();
                                        } 
                                    }
                                />
                            : null }
                        </div>
                    </InspectorControls>
                </div>

                { isSelected ? 
                    (
                    <div>
                        { isModalOpen && (
                            taxonomyId != null && taxonomyId != undefined ? renderTermsModalContent() : renderTaxonomyModalContent()                 
                        ) }

                        <div className="block-control">
                            <Button
                                isPrimary
                                onClick={ () => openTermsModal() }>
                                {__('Select terms', 'tainacan')}
                            </Button>   
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