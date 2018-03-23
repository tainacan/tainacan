<template>
    <span class="help-wrapper">
        <a 
                class="help-button" 
                @click="isOpened = !isOpened"><b-icon 
                size="is-small" 
                icon="help-circle-outline"/></a>
        <div 
                class="help-tooltip" 
                :class="{ 'opened': isOpened }">
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
        color: $gray;
        &:hover {
            color: $primary !important;
        }
    }

    .help-tooltip.opened {
        visibility: visible;
        opacity: 1;
        margin-bottom: 14px;
    }

    .help-tooltip {
        color: #000;
        background-color: #e8f9f5;
        border: 1px solid #338591;
        border-radius: 10px;
        margin: 0px 0px 0px -37px;
        position: absolute;
        z-index: 999999999999999;
        bottom: 100%;
        left: 0%;
        min-width: 250px;
        display: block;
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.4s ease, margin-bottom 0.3s ease;

        .help-tooltip-header {
            padding: 0.8em 0.8em 0.6em 0.8em;
            border-bottom: 1px solid #909293;
            font-size: 18px;
            font-weight: bold;

            h5 {
                margin-right: 25px;
            }
            .icon {
                color: #909293 !important;
                right: 14px;
                top: 14px;
                position: absolute;
                &:hover {
                    color: black !important;
                }
            }
        }

        .help-tooltip-body {
            padding: 1.2em;
            font-size: 11px;
        }

        &:after,
        &:before {
            content: "";
            display: block;
            position: absolute;
            left: 24px;
            width: 0;
            height: 0;
            border-style: solid;
        }
        &:after {
            border-color:  #e8f9f5 transparent transparent transparent;
            border-right-width: 20px;
            border-top-width: 16px;
            border-left-width: 20px;
        }
        &:before {
            border-color: #338591 transparent transparent transparent;
            border-right-width: 20px;
            border-top-width: 16px;
            border-left-width: 20px;
            bottom: -20px;
        }
    }
</style>

