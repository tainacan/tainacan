const { registerBlockType } = wp.blocks;

const { Modal, Button, Autocomplete } = wp.components;
const { sprintf, __ } = wp.i18n;

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
                options: [
                    { visual: 'a', name: 'Apple', id: 1 },
                    { visual: 'o', name: 'Orange', id: 2 },
                    { visual: 'g', name: 'Grapes', id: 3 },
                ],
                // Returns a label for an option like "🍊 Orange"
                getOptionLabel: option => (
                    <span>
                        <span className="icon" >{ option.visual }</span> { option.name }
                    </span>
                ),
                // Declares that options should be matched by their name
                getOptionKeywords: option => [ option.name ],
                // Declares completions should be inserted as abbreviations
                getOptionCompletion: option => (
                    <abbr title={ option.name }>{ option.visual }</abbr>
                ),
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
                            <p>Type / for triggering the autocomplete.</p>
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