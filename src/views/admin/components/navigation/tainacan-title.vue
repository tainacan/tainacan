<template>
    <div 
            class="tainacan-page-title"
            :class="{ 'tainacan-page-title--sticky': isSticky }">
        <slot />
        <h1 v-if="!slotPassed">
            {{ pageTitle }} <span class="is-italic has-text-weight-semibold">{{ !isRepositoryLevel && collection && collection.name ? collection.name : '' }}</span>
        </h1>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import { useSlots } from 'vue';

export default {
    name: 'TainacanTitle',
    props: {
        isSticky: true
    },
    data() {
        return {
            isRepositoryLevel: true,
            pageTitle: ''
        }
    },
    computed: {
        ...mapGetters('collection', {
            'collection': 'getCollection'
        }),
        slotPassed() {
            const slots = useSlots();
            return !!slots['default'];
        },
    },
    watch: {
        '$route': {
            handler(to, from) {
                if (!from || to.path != from.path) {
                    this.isRepositoryLevel = (to.params.collectionId == undefined);
                    this.pageTitle = to.meta.title;

                    if ( !this.isRepositoryLevel && this.collection && this.collection.name )
                        this.$routerHelper.appendToPageTitle(this.collection.name);
                }
            },
            immediate: true,
            deep: true
        },
        'collection': {
            handler() {
                if ( !this.isRepositoryLevel && this.collection && this.collection.name )
                    this.$routerHelper.appendToPageTitle(this.collection.name);
            },
            deep: true,
            immediate: true,
        }
    },
    mounted() {
        // Set the initial title
        this.pageTitle = this.$route.meta.title;

        if ( !this.isRepositoryLevel && this.collection && this.collection.name )
            this.$routerHelper.appendToPageTitle(this.collection.name);
    },
}
</script>

<style lang="scss" scoped>

    .tainacan-page-title {
        padding-top: calc(0.125rem + var(--tainacan-container-padding));
        padding-bottom: 0.5rem;
        margin-bottom: var(--tainacan-container-padding);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75em 1.5em;
        min-height: calc( 0.125rem + var(--tainacan-container-padding) + 0.5rem + var(--tainacan-button-min-height, 2.571em) );

        &.tainacan-page-title--sticky {
            margin-bottom: 0px;
            z-index: 999;
            position: sticky;
            top: 0;
            background-color: var(--tainacan-background-color);
            container-type: scroll-state;

            &::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                height: 1px;
                background-color: var(--tainacan-input-border-color);
                transition: background 0.2s ease;
            }
        }
      
        @media screen and (max-width: 768px) {
            top: 206px;
            margin-bottom: 0px !important;

            :deep(h1),
            :deep(h2) {
                padding: 0 1.5rem;
            }
        }

        :deep(h1),
        :deep(h2) {
            font-size: 1.25em;
            line-height: 1.25em;
            font-weight: 500;
            color: var(--tainacan-heading-color);
            display: inline-block;
            width: auto;
            flex-shrink: 1;
        }
    }

    @media screen and (min-width: 769px) {
        .tainacan-external-link + .tainacan-page-title--sticky {
            padding-right: 200px;
        }
    }

    @container scroll-state(stuck: top) {
        .tainacan-page-title.tainacan-page-title--sticky::after {
            background: var(--tainacan-input-border-color) !important;
        }
    }
    @supports (container-type: scroll-state) {
        .tainacan-page-title.tainacan-page-title--sticky::after {
                background-color: var(--tainacan-background-color);
        }
    }

</style>


