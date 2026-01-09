<template>
    <a 
            v-if="!$adminOptions.hideExternalEntityLinks"
            :href="linkUrl"
            target="_blank"
            class="button is-primary tainacan-external-link">
        <span class="icon">
            <i class="tainacan-icon tainacan-icon-1-125em tainacan-icon-openurl" />
        </span>
        <span class="is-hidden-mobile">{{ linkLabel }}</span>
    </a>
</template>

<script>
export default {
    name: 'TainacanExternalLink',
    props: {
        linkLabel: String,
        linkUrl: String
    }
}
</script>

<style lang="scss" scoped>
    .tainacan-external-link {
        border-color: transparent;
        border-start-end-radius: var(--tainacan-page-container-border-radius, 12px) !important;
        border-end-start-radius: var(--tainacan-admin-button-border-radius, 8px) !important;
        border-end-end-radius: 0 !important;
        border-start-start-radius: 0 !important;
        position: fixed;
        inset-inline-end: var(--tainacan-page-container--inner-padding-x, 1rem);
        top: var(--wp-admin--admin-bar--height, 32px) - var(--tainacan-page-container-margin-top, 1rem) - var(--tainacan-breadcumbs-list-height, 1rem) - var(--tainacan-page-container--inner-padding-y, 1rem);
        z-index: 1000;
        height: auto !important;
        padding-inline-end: 0.5em !important;
    }
    @media screen and (min-width: 769px) {
        .tainacan-external-link {
            padding-left: 1.5em !important;
            padding-right: 1.5em !important;
        }
    }
</style>