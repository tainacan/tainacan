const { registerBlockType } = wp.blocks;

const { __ } = wp.i18n;

const { Button, Modal } = wp.components;
const { withState } = wp.compose;

const MyModal = withState( {
    isOpen: false,
} )( ( { isOpen, setState } ) => (
    <div>
        <Button isDefault onClick={ () => setState( { isOpen: true } ) }>Open Modal</Button>
        { isOpen ?
            <Modal
                title="This is my modal"
                onRequestClose={ () => setState( { isOpen: false } ) }>
                <Button isDefault onClick={ () => setState( { isOpen: false } ) }>
                    My custom close button
                </Button>
            </Modal>
            : null }
    </div>
) );

registerBlockType('tainacan/items-grid', {
    title: __('Tainacan Items Grid', 'tainacan'),
    icon: 'images-alt',
    category: 'tainacan-blocks',
    edit({ attributes, setAttributes, className }){
        console.log('edit', attributes);


        return (
            <div>
                <MyModal />
            </div>
        );
    },
    save({ attributes }){
        const { content } = attributes;

        console.log('save', attributes);

        return <div>{ content }</div>
    }
});