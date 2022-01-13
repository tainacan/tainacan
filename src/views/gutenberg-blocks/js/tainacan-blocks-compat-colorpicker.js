const { ColorPicker, ColorPalette } = wp.components;

export default function TainacanBlocksCompatColorPicker({ value, onChange, enableAlpha, disableAlpha }) {
    
    const currentWPVersion = (typeof tainacan_blocks != 'undefined') ? tainacan_blocks.wp_version : tainacan_plugin.wp_version;

    return currentWPVersion < '5.9' ?
        <ColorPicker
            color={ value }
            onChangeComplete={ (value) => onChange(value.hex) }
            enableAlpha={ enableAlpha }
            disableAlpha={ disableAlpha }
            />
        : 
        <ColorPalette
            value={ value }
            onChange={ onChange }
            enableAlpha={ enableAlpha }
            disableAlpha={ disableAlpha }
            />
}
