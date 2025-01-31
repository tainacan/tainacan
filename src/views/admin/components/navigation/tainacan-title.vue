<template>
    <div class="tainacan-page-title">
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
    data() {
        return {
            isRepositoryLevel: true,
            pageTitle: '',
            activeRouteName: '',
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
                if (to.path != from.path) {
                    this.isRepositoryLevel = (to.params.collectionId == undefined);

                    this.activeRoute = to.name;
                    this.pageTitle = this.$route.meta.title;
                }
            },
            deep: true
        }
    },
    created() {
        this.isRepositoryLevel = (this.$route.params.collectionId == undefined);

        document.title = this.$route.meta.title;
        this.pageTitle = document.title;
    }
}
</script>

<style lang="scss" scoped>

    .tainacan-page-title {
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75em 1.5em;

        :deep(h1),
        :deep(h2) {
            font-size: 1.25em;
            line-height: 1.25;
            font-weight: 500;
            color: var(--tainacan-heading-color);
            display: inline-block;
            width: auto;
            flex-shrink: 1;
        }
      
        .level-left {
            .level-item {
                display: inline-block;
                margin-left: 268px;
            }  
        }
        @media screen and (max-width: 769px) {
            .level-left {
                margin-left: 0px !important;
                .level-item {
                    margin-left: 30px;
                }
            }
            .level-right {
                display: none;
            }

            top: 206px;
            margin-bottom: 0px !important;
        }
    }
</style>


