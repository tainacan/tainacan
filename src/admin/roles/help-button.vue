<template>
    <span class="help-wrapper">
        <a class="help-button has-text-secondary">
            <span class="icon is-small">
                <span class="dashicons dashicons-editor-help" />
            </span>
        </a>
        <div class="help-tooltip">
            <div class="help-tooltip-header">
                <p>{{ title }}</p>
            </div>
            <div class="help-tooltip-body">
                <p v-html="(message != undefined) ? message : ''"/>
                <p 
                        v-if="superCaps && superCaps.length && capabilities"
                        v-html="'<br>' + $i18n.get('If disabled, this capability may be affected by ') + ' ' + renderSuperCapsList()" />
            </div>
        </div> 
    </span>
</template>

<script>
export default {
    name: 'HelpButton',
    props: {
        title: String,
        message: String,
        superCaps: Array,
        capabilities: Array
    },
    methods: {
        renderSuperCapsList() {
            let htmlList = '';
            const validCaps = this.superCaps.filter((superCap) => this.capabilities[superCap] && this.capabilities[superCap].display_name );
            for (let i = 0; i < validCaps.length; i++) {
                htmlList += `<strong>${ this.capabilities[validCaps[i]].display_name }</strong>`;
                if (validCaps.length > 2 && i < validCaps.length - 1) {
                    if (i < validCaps.length - 2)
                        htmlList += ', '
                    else
                        htmlList += ' ' + this.$i18n.get('or') + ' ';
                } else if (validCaps.length == 2 && i == 0) {
                    htmlList += ' ' + this.$i18n.get('or') + ' ';
                }
                
            }

            return htmlList;
        } 
    }
}
</script>

<style lang="scss">

    .help-wrapper {
        position: absolute;
        margin-top: -4px;
        margin-left: 4px;
    }

    a.help-button .icon {
        i, i::before { font-size: 0.875rem !important }
    }

    .help-wrapper:hover .help-tooltip {
        margin-bottom: 12px;
        margin-left: -37px;
        visibility: visible;
        opacity: 1; 
    }
    .help-tooltip {
        z-index: 99999999999999999999;
        color: #555;
        background-color: white;
        border: none;
        display: block;
        border-radius: 3px;
        border: 1px solid #cbcbcb;
        min-width: 250px;
        max-width: 100%;
        transition: margin-bottom 0.2s ease, opacity 0.3s ease;
        position: absolute;
        bottom: calc(100% - 6px);
        left: 0%;            
        margin-bottom: -27px;
        visibility: hidden;
        opacity: 0;

        .help-tooltip-header {
            padding: 0.8rem 0.8rem 0rem 0.8rem;

            p {
                font-size: 0.875rem;
                font-weight: bold;
                margin: 0;
            }
        }

        .help-tooltip-body {
            padding: 0.5em 1.0rem 1.0rem 1.0rem;

            p {
                font-size: 0.875rem !important;
                font-weight: normal !important;
                white-space: normal !important;
                overflow: visible;
                margin: 0;
            }
        }

        &:before {
            content: "";
            display: block;
            position: absolute;
            left: 34px;
            width: 0;
            height: 0;
            border-style: solid;
        }
        &:before {
            border-color:#cbcbcb transparent transparent transparent;
            border-right-width: 12px;
            border-top-width: 9px;
            border-left-width: 12px;
            bottom: -12px;
        }
    }
    
</style>

