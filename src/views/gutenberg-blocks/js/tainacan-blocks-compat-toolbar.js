const { Button, Toolbar, ToolbarGroup, ToolbarButton } = wp.components;

export default function TainacanBlocksCompatToolbar({ label, icon, onClick, onClickParams, controls }) {
    
    const currentWPVersion = (typeof tainacan_blocks != 'undefined') ? tainacan_blocks.wp_version : tainacan_plugin.wp_version;

    if (controls !== undefined)
        return currentWPVersion < '5.4' ? <Toolbar controls={ controls } /> : <ToolbarGroup controls={ controls } /> 
    else
        return currentWPVersion < '5.4' ?
            <Button style={{ whiteSpace: 'nowrap', backgroundColor: '#fff', alignItems: 'center', borderTop: '1px solid #b5bcc2', borderBottom: '1px solid #b5bcc2' }} onClick={ () => onClick(onClickParams) }>
                <p style={{ margin: 0 }}>
                { icon }
                </p>&nbsp;
                { label }
            </Button>
            : 
            <ToolbarGroup>
                { currentWPVersion < '5.5' ?
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
