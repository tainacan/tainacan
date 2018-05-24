<template>
    <span class="data-wrapper">
        <p 
            class="data-area"
            v-html="data" />
        <div class="data-tooltip">
            <div class="data-tooltip-body">
                <p v-html="data" />
            </div>
        </div> 
    </span>
</template>

<script>
export default {
    name: 'DataAndTooltip',
    props: {
        data: '',
    }
}
</script>

<style lang="scss">

    @import "../../scss/_variables.scss";

    .data-wrapper {
        position: relative;
    }

    .data-area {
        text-overflow: ellipsis;
        overflow-x: hidden;
        white-space: nowrap;
        margin-bottom: 0;
    }

    .data-wrapper:hover .data-tooltip {
        margin-bottom: -8px;
        margin-left: -8px;
        visibility: visible;
        opacity: 1; 
    }
    .data-tooltip {
        z-index: 99999999999999999999;
        color: $tertiary;
        background-color: $primary-light;
        border: none;
        display: block;
        border-radius: 5px;
        max-width: 280px;
        min-width: 100px;
        height: auto;
        transition: margin-bottom 0.2s ease, opacity 0.3s ease;
        position: absolute;
        bottom: calc(100% - 6px);
        left: 0;         
        margin-bottom: -27px;
        visibility: hidden;
        opacity: 0;

        .data-tooltip-body {
            padding: 20px;
            p {
                font-size: 11px;
                font-weight: normal;
            }
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
            border-right-width: 6px;
            border-top-width: 7px;
            border-left-width: 6px;
            bottom: -10px;
        }
    }
    
</style>

