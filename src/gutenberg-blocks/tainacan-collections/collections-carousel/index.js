const { registerBlockType } = wp.blocks;

const { Modal, Button, Autocomplete } = wp.components;
const { __ } = wp.i18n;

import tainacan from '../../api-client/axios.js';

registerBlockType('tainacan/collections-carousel', {
    title: 'Tainacan Collections Carousel',
    icon: 'images-alt',
    category: 'widgets',
    attributes: {
        hello: {
            type: String,
            default: 'Collections Carousel'
        },
        isOpen: {
            type: Boolean,
            default: false
        },
    },
    edit({ attributes, setAttributes, className }) {
        const autoCompleters = [
            {
                name: __('Collections', 'tainacan'),
                // The prefix that triggers this completer
                triggerPrefix: '/',
                // The option data
                options: (keyword) => {
                    if(!keyword){
                        return [];
                    }

                    return tainacan.get(`/collections?search=${keyword}`)
                        .then(response => {
                            return response.data;
                        })
                        .catch(error => {
                            console.log(error);
                        });
                },
                getOptionLabel: option => (
                    <span>
                        <span className="icon" >{ option.name }</span>
                    </span>
                ),
                getOptionKeywords: option => [ option.name ],
                getOptionCompletion: option => {
                    return ( <abbr title={ option.name }>{ option.name }</abbr> );
                },
                isDebounced: true,
            }
        ];

        return (
            <div className={ className }>
                <Button isDefault onClick={ () => setAttributes( { isOpen: true } ) }>{ __('Add collection', 'tainacan') }</Button>
                { attributes.isOpen ?
                    <Modal
                        shouldCloseOnClickOutside={ false }
                        title={ __('Add collection', 'tainacan') }
                        onRequestClose={ () => setAttributes( { isOpen: false } ) }>

                        <div>
                            <Autocomplete completers={ autoCompleters }>
                                { ( { isExpanded, listBoxId, activeId } ) => (
                                    <div
                                        contentEditable
                                        suppressContentEditableWarning
                                        aria-autocomplete="list"
                                        aria-expanded={ isExpanded }
                                        aria-owns={ listBoxId }
                                        aria-activedescendant={ activeId }
                                    >
                                    </div>
                                ) }
                            </Autocomplete>
                            <p>{ __('Type / for triggering the autocomplete.', 'tainacan') }</p>
                        </div>

                        <Button isDefault onClick={ () => setAttributes( { isOpen: false } ) }>
                            { __('Close', 'tainacan') }
                        </Button>
                    </Modal>
                    : null }
            </div>
        );
    },
    save({ attributes }) {
        return (
            <div>
                <pre>{`Save`}</pre>
            </div>
        );
    },
});