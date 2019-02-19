const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { IconButton, Spinner, ToggleControl, Placeholder, Toolbar } = wp.components;

const { InspectorControls, BlockControls } = wp.editor;

import Autocomplete from 'react-autocomplete';

import tainacan from '../../api-client/axios.js';
import qs from 'qs';

registerBlockType('tainacan/terms-list', {
    title: __('Tainacan Terms List', 'tainacan'),
    icon: 'list-view',
    category: 'tainacan-blocks',
    keywords: [ __( 'Tainacan', 'tainacan' ), __( 'terms', 'tainacan' ), __( 'taxonomy', 'tainacan' ) ],
    attributes: {
        selectedTermsObject: {
            type: 'array',
            source: 'query',
            selector: 'a',
            query: {
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
        currentTermName: {
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
            currentTermName, 
            taxonomyId, 
            taxonomyName, 
            isLoadingTerms, 
            isLoadingTaxonomies, 
            taxonomies, 
            showImage,
            layout 
        } = attributes;
        
        console.log("Editando...");
        // console.log(selectedTerms);
        // console.log(selectedTerms);
        // console.log(content);

        function prepareTerm(term, index) {
            return (
                <li 
                    key={term.url}
                    className="term-list-item">
                    <IconButton
                        onClick={ () => removeTermAtIndex(index) }
                        icon="no-alt"
                        label={__('Remove', 'tainacan')}/>         
                    <a href={ term.url } target="_blank">
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

        function setContent(selectedTermsHTML){
            setAttributes({
                content: (
                    <ul className={'terms-list  terms-layout-' + layout}>{ selectedTermsHTML }</ul>
                )
            });
        }

        function mountBlock(termsFromHTML) {

            let termsOnObject = [];
            let termsOnHTML = [];

            for (let i = 0; i < termsFromHTML.length; i++){
                termsOnHTML.push(prepareTerm(termsFromHTML[i], i));
                termsOnObject.push(termsFromHTML[i]);
            }

            selectedTermsHTML = termsOnHTML;
            selectedTermsObject = termsOnObject;

            setAttributes({ 
                selectedTermsHTML: termsOnHTML, 
                selectedTermsObject: termsOnObject
            });
            setContent(selectedTermsHTML);
        }

        function fetchTaxonomies(name) {
            isLoadingTaxonomies = true;
            taxonomies = [];

            setAttributes({ 
                isLoadingTaxonomies: isLoadingTaxonomies, 
                taxonomies: taxonomies
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

        function fetchTerms(name) {
            isLoadingTerms = true;
            terms = [];

            setAttributes({ 
                isLoadingTerms: isLoadingTerms, 
                terms: terms
            });

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

        function fetchTaxonomy() {
            tainacan.get('/taxonomies/' + taxonomyId)
                .then((response) => {
                    taxonomyName = response.data.name;
                    setAttributes({ taxonomyName: taxonomyName });
                }).catch(error => {
                    console.log('Error trying to fetch taxonomy: ' + error);
                });
        }

        function selectTerm(term) {
            let existingTermIndex = selectedTermsObject.findIndex((existingTerm) => existingTerm.key == term.id);
            if (existingTermIndex < 0) {
                selectedTermsObject.push(term);
                selectedTermsHTML.push(prepareTerm(term, selectedTermsHTML.length));
            }

            setAttributes({ 
                selectedTermsObject: selectedTermsObject,
                selectedTermsHTML: selectedTermsHTML
            });
            setContent(selectedTermsHTML);
        }

        function removeTermAtIndex(index) {
            selectedTermsHTML.splice(index, 1);
            selectedTermsObject.splice(index, 1);

            setAttributes({ 
                selectedTermsObject: selectedTermsObject,
                selectedTermsHTML: selectedTermsHTML
            });
            setContent(selectedTermsHTML);
        }

        function updateTermsList() {

            let currentSelectedTermsObject = [];
            let currentSelectedTermsHTML = [];

            for (let term of selectedTermsObject) {
                currentSelectedTermsObject.push(term);
                currentSelectedTermsHTML.push(prepareTerm(term, selectedTermsHTML.length));
            }

            setAttributes({ 
                selectedTermsObject: currentSelectedTermsObject,
                selectedTermsHTML: currentSelectedTermsHTML,
                showImage: showImage
            });
            setContent(currentSelectedTermsHTML);
        }
        
        // Executed every time Edit function runs
        if (taxonomyId != null && taxonomyId != '')
            fetchTaxonomy();

        if(content && content.length && content[0].type)
            mountBlock(selectedTermsObject);

        const layoutControls = [
            {
                icon: 'grid-view',
                title: __( 'Grid View' ),
                onClick: () => setAttributes( { layout: 'grid' } ),
                isActive: layout === 'grid',
            },
            {
                icon: 'list-view',
                title: __( 'List View' ),
                onClick: () => setAttributes( { layout: 'list' } ),
                isActive: layout === 'list',
            },            
            {
                icon: 'exerpt-view',
                title: __( 'Card View' ),
                onClick: () => setAttributes( { layout: 'card' } ),
                isActive: layout === 'card',
            },
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
                        <div style={{ marginTop: '20px' }}>
                            <ToggleControl
                                label={__('Image', 'tainacan')}
                                help={ showImage ? __('Toggle to show term\'s image', 'tainacan') : __('Do not show term\'s image', 'tainacan')}
                                checked={ showImage }
                                onChange={ ( isChecked ) => {
                                        showImage = isChecked;

                                        updateTermsList();
                                    } 
                                }
                            />
                        </div>
                    </InspectorControls>
                </div>

                { isSelected ? 
                    
                        (<div>
                            { isLoadingTaxonomies ? <Spinner /> : null }

                            <div className="block-control">
                                
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
                                    </div>   
                                    <div className={'block-control-item' + (taxonomyId == null || taxonomyId == undefined ? ' disabled' : '')}>

                                        <label
                                            className="autocomplete-label"
                                            htmlFor="taxonomy-autocomplete">
                                            {__('Select a term to add', 'tainacan')}
                                        </label>
                                        
                                        <Autocomplete
                                            inputProps={{ 
                                                id: 'term-autocomplete', 
                                                disabled: taxonomyId == null || taxonomyId == undefined
                                            }}
                                            wrapperProps={{ className: 'react-autocomplete' }}
                                            value={ currentTermName }
                                            items={ terms }
                                            onSelect={(value, item) => {
                                                    currentTermName = '';
                                                    setAttributes({ currentTermName: currentTermName });
                                                    selectTerm(item);
                                                }
                                            }
                                            getItemValue={(term) => term.value }
                                            onChange={(event, value) => {   
                                                    currentTermName = value;
                                                    setAttributes({ currentTermName: currentTermName });
                                                    _.debounce(fetchTerms(value), 300);
                                                }
                                            }
                                            renderMenu={ children => (
                                                children.length > 0 ? (
                                                <div 
                                                    className="menu">
                                                    { children }
                                                </div>
                                                ) : <span></span>
                                            )}
                                            renderItem={(item, isHighlighted) => (
                                                <div
                                                    className={`item ${isHighlighted ? 'item-highlighted' : ''}`}
                                                    key={item.id}>
                                                    {item.name}
                                                </div>
                                            )}/>
                                    </div>       
                            </div>
                            <hr/>
                        </div>
                        ) : null
                }

                { !selectedTermsObject.length ? (
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
    save({ attributes }){
        const { content } = attributes;
        return <div>{ content }</div>
    }
});