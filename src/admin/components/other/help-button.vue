<template>
    <span class="help-wrapper">
        <a 
                class="help-button" 
                @click="isOpened = !isOpened"><b-icon 
                size="is-small" 
                icon="help-circle-outline"/></a>
        <div
                @click="isOpened = false" 
                class="help-backdrop" 
                :class="{ 'opened': isOpened }" />   
        <div 
                :class="{ 'opened': isOpened }"
                class="help-tooltip">
            <div class="help-tooltip-header">
                <h5>{{ title }}</h5><a @click="isOpened = false"><b-icon icon="close"/></a>
            </div>
            <div class="help-tooltip-body">
                <p>{{ (message != '' && message != undefined) ? message : $i18n.get('info_no_description_provided') }}</p>
            </div>
        </div> 
    </span>
</template>

<script>
export default {
    name: 'HelpButton',
    data() {
        return {
            isOpened: false
        }
    },
    props: {
        title: '',
        message: ''
    }
}
</script>

<style lang="scss">

    @import "../../scss/_variables.scss";

    .help-wrapper {
        position: relative;
    }

    a.help-button .icon {
        i, i::before { font-size: 0.9em !important; }
    }

    .help-backdrop {
        background-color: transparent;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 9999999999999999999;
        display: none;
        visibility: hidden;
        opacity: 0;

        &.opened {
            visibility: visible;
            display: block;
        }
        
        
    }
    .help-tooltip {
        z-index: 99999999999999999999;
        color: $secondary;
        background-color: $primary-light;
        border: none;
        border-radius: 5px;
        margin: 0px 0px 0px -37px;
        position: absolute;
        bottom: calc(100% - 6px);
        left: 0%;
        min-width: 250px;
        display: block;
        transition: margin-bottom 0.3s ease, opacity 0.4s ease;
        visibility: hidden;
        opacity: 0;

        &.opened {
            margin-bottom: 14px;
            visibility: visible;
            opacity: 1;    
        }

        .help-tooltip-header {
            padding: 0.8em 0.8em 0em 0.8em;

            h5 {
                font-size: 16px;
                font-weight: 700;
                margin-right: 25px;
            }
            .icon {
                right: 12px;
                top: 12px;
                position: absolute;
            }
        }

        .help-tooltip-body {
            padding: 1.2em;
            font-size: 11px;
        }

        &:before {
            content: "";
            display: block;
            position: absolute;
            left: 28px;
            width: 0;
            height: 0;
            border-style: solid;
        }
        &:before {
            border-color: $primary-light transparent transparent transparent;
            border-right-width: 18px;
            border-top-width: 12px;
            border-left-width: 18px;
            bottom: -15px;
        }
    }
</style>

