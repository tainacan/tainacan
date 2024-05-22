const { ToolbarGroup, ToolbarButton } = wp.components;

export default function TainacanBlocksCompatToolbar({ label, icon, onClick, onClickParams, controls, extraComponents }) {
    
    if (controls !== undefined)
        return <ToolbarGroup controls={ controls }>
            { extraComponents }
        </ToolbarGroup> 
    else
        return <ToolbarGroup>
                <ToolbarButton onClick={ () => onClick(onClickParams) }>
                    <p>
                        { icon }
                    </p>&nbsp;
                    { label }
                </ToolbarButton>
                { extraComponents }
            </ToolbarGroup>
}
