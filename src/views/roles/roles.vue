<template>
    <router-view class="has-mounted wrap" />
</template>

<script>
    export default { 
        name: "RolesPage"
    }
</script>

<style lang="scss">
    @import url('floating-vue/dist/style.css');

    .tainacan_page_tainacan_roles #wpbody {
        overflow-x: hidden;
    }
    #tainacan-roles-app {
        a:hover {
            cursor: pointer;
        }
    }
    .tainacan-roles-tooltip {
        display: block !important;
        z-index: 10000;
        max-width: 300px;

        .tooltip-inner {
            background: black;
            padding: 0.35em 0.45em;
            color: white;
            text-align: center;
        }

        .tooltip-arrow {
            width: 0;
            height: 0;
            border-style: solid;
            position: absolute;
            margin: 5px;
            border-color: black;
            z-index: 1;
        }
        &[x-placement^="top"] {
            margin-bottom: 5px;

            .tooltip-arrow {
                border-width: 5px 5px 0 5px;
                border-inline-start-color: transparent !important;
                border-inline-end-color: transparent !important;
                border-bottom-color: transparent !important;
                bottom: -5px;
                inset-inline-start: calc(50% - 5px);
                margin-top: 0;
                margin-bottom: 0;
            }
        }

        &[x-placement^="bottom"] {
            margin-top: 5px;

            .tooltip-arrow {
                border-width: 0 5px 5px 5px;
                border-inline-start-color: transparent !important;
                border-inline-end-color: transparent !important;
                border-top-color: transparent !important;
                top: -5px;
                inset-inline-start: calc(50% - 5px);
                margin-top: 0;
                margin-bottom: 0;
            }
        }

        &[x-placement^="right"] {
            margin-inline-start: 5px;

            .tooltip-arrow {
                border-width: 5px 5px 5px 0;
                border-inline-start-color: transparent !important;
                border-top-color: transparent !important;
                border-bottom-color: transparent !important;
                inset-inline-start: -5px;
                top: calc(50% - 5px);
                margin-inline-start: 0;
                margin-inline-end: 0;
            }
        }

        &[x-placement^="left"] {
            margin-inline-end: 5px;

            .tooltip-arrow {
                border-width: 5px 0 5px 5px;
                border-top-color: transparent !important;
                border-inline-end-color: transparent !important;
                border-bottom-color: transparent !important;
                inset-inline-end: -5px;
                top: calc(50% - 5px);
                margin-inline-start: 0;
                margin-inline-end: 0;
            }
        }

        &[aria-hidden='true'] {
            visibility: hidden;
            opacity: 0;
            transition: opacity .15s, visibility .15s;
        }

        &[aria-hidden='false'] {
            visibility: visible;
            opacity: 1;
            transition: opacity .15s;
        }
    }
</style>