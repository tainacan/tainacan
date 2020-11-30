const { Button, ToolbarGroup, ToolbarButton } = wp.components;

export default function TainacanBlocksCompatToolbar({ label, icon, onClick, onClickParams, controls }) {
    
    if (controls !== undefined)
        return tainacan_blocks.wp_version < '5.4' ? <Toolbar controls={ controls } /> : <ToolbarGroup controls={ controls } /> 
    else
        return tainacan_blocks.wp_version < '5.4' ?
            <Button style={{ whiteSpace: 'nowrap', alignItems: 'center', borderTop: '1px solid #b5bcc2' }} onClick={ () => onClick(onClickParams) }>
                <p style={{ margin: 0 }}>
                { icon }
                </p>&nbsp;
                { label }
            </Button>
            : 
            <ToolbarGroup>
                { tainacan_blocks.wp_version < '5.5' ?
                    <Button style={{ whiteSpace: 'nowrap' }} onClick={ () => onClick(onClickParams) }>
                        <p>
                            { icon }
                        </p>&nbsp;
                        { label }
                    </Button>
                    :
                    <ToolbarButton onClick={ () => onClick(onClickParams) }>
                        <p>
                            { icon }
                        </p>&nbsp;
                        { label }
                    </ToolbarButton>
                }    
            </ToolbarGroup>
}
