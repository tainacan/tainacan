const { ToolbarGroup, ToolbarButton } = wp.components;

export default function TainacanBlocksCompatToolbar({ label, icon, onClick, onClickParams, controls }) {
    
    if (controls !== undefined)
        return <ToolbarGroup controls={ controls } /> 
    else
        return <ToolbarGroup>
                <ToolbarButton onClick={ () => onClick(onClickParams) }>
                    <p>
                        { icon }
                    </p>&nbsp;
                    { label }
                </ToolbarButton>
            </ToolbarGroup>
}
