<template>
    <span class="help-wrapper">
        <a 
                :class="forcedColor ? 'has-text-' + forcedColor : 'has-text-secondary'"
                class="help-button">
            <span class="icon is-small">
                <i class="tainacan-icon tainacan-icon-help" />
            </span>
        </a>
        <div class="help-tooltip">
            <div class="help-tooltip-header">
                <h5>{{ title }}</h5>
            </div>
            <div class="help-tooltip-body">
                <p v-html="(message != '' && message != undefined) ? message : $i18n.get('info_no_description_provided')"/>
            </div>
        </div> 
    </span>
</template>

<script>
export default {
    name: 'HelpButton',
    props: {
        title: '',
        message: '',
        forcedColor: ''
    }
}
</script>

<style lang="scss">

    @import "../../scss/_variables.scss";

    .help-wrapper {
        position: absolute;
        margin-top: -4px;
        margin-left: 4px;
    }

    a.help-button .icon {
        i, i::before { font-size: 0.875em !important }
    }

    .help-wrapper:hover .help-tooltip {
        margin-bottom: 12px;
        margin-left: -37px;
        visibility: visible;
        opacity: 1; 
    }
    .help-tooltip {
        z-index: 99999999999999999999;
        color: var(--tainacan-turquoise5);
        background-color: var(--tainacan-turquoise2);
        border: none;
        display: block;
        border-radius: 5px;
        min-width: 280px;
        max-width: 100%;
        transition: margin-bottom 0.2s ease, opacity 0.3s ease;
        position: absolute;
        bottom: calc(100% - 6px);
        left: 0%;            
        margin-bottom: -27px;
        visibility: hidden;
        opacity: 0;

        .help-tooltip-header {
            padding: 0.8em 0.8em 0em 0.8em;

            h5 {
                font-size: 0.875em;
                font-weight: bold;
            }
        }

        .help-tooltip-body {
            padding: 0.5em 1.0em 1.0em 1.0em;

            p {
                font-size: 0.875em !important;
                font-weight: normal !important;
                white-space: normal !important;
                overflow: visible !important;
                max-height: 100% !important;
            }
        }

        &:before {
            content: "";
            display: block;
            position: absolute;
            left: 30px;
            width: 0;
            height: 0;
            border-style: solid;
        }
        &:before {
            border-color: var(--tainacan-turquoise2) transparent transparent transparent;
            border-right-width: 15px;
            border-top-width: 12px;
            border-left-width: 15px;
            bottom: -15px;
        }
    }
    
</style>

