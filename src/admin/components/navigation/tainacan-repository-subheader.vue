<template>
    <div
            id="tainacan-repository-subheader" 
            class="level secondary-page"
            :class="{'is-menu-compressed': isMenuCompressed, 'is-repository-level' : isRepositoryLevel}">
        <h1 v-if="isRepositoryLevel">{{ repositoryName }}</h1>
        <h1 v-else>{{ $i18n.get('collection') + '' }} <span class="has-text-weight-bold">{{ collectionName }}</span></h1>
        <a
                :href="collectionURL"
                target="_blank"
                v-if="!isRepositoryLevel"
                class="button"
                id="view-collection-button">
            <span class="icon">
                <i class="tainacan-icon tainacan-icon-20px tainacan-icon-see"/>
            </span>
             {{ $i18n.get('label_view_collection') }}
        </a>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'TainacanRepositorySubheader',
    data() {
        return {
            repositoryName: tainacan_plugin.repository_name,
            collectionId: ''
        }
    },
    props: {
        isMenuCompressed: false,
        isRepositoryLevel: true
    },
    computed: {
        collectionName() {
            return this.getCollectionName();
        },
        collectionURL() {
            return this.getCollectionURL();
        }
    },
    methods: {
        ...mapActions('collection', [
            'fetchCollectionNameAndURL'
        ]),
        ...mapGetters('collection', [
            'getCollectionName',
            'getCollectionURL'
        ])
    },
    mounted() {
        if (!this.isRepositoryLevel) {
            this.collectionId = this.$route.params.collectionId;
            this.fetchCollectionNameAndURL(this.collectionId);
        }
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";
    
    // Tainacan Header
    #tainacan-repository-subheader {
        background-color: $turquoise5;
        height: 42px;
        max-height: 42px;
        width: 100%;
        overflow-y: hidden;
        padding-top: 10px;
        padding-bottom: 10px;
        padding-right: 0;
        padding-left: calc((4.166666667% - 6.666666667px) + 160px);
        margin: 0px;
        display: flex;
        vertical-align: middle; 
        left: 0;
        right: 0;
        top: $header-height;
        position: absolute;
        z-index: 98;
        transition: padding-left 0.2s linear, background-color 0.2s linear;

        &.is-repository-level {
            background-color: $blue5;
            padding-right: $page-side-padding;
        }

        &.is-menu-compressed {     
            padding-left: calc((4.166666667% - 2.083333333px)  + 50px);
        }

        h1 {
            font-size: 1.125rem;
            color: white;
            line-height: 1.125rem;
            max-width: 100%;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;  
            transition: all 0.2s linear;
        }

        #view-collection-button {
            border: none;
            border-radius: 0px !important;
            height: 42px !important;
            right: 0;
            position: relative;
            background-color: $turquoise4;
            color: white;
            
            .icon {
                margin-right: 0.75rem;
            }
        }

        @media screen and (max-width: 769px) {
            top: 102px;
            padding-left: 4.166666667% !important;
        }
    }
</style>


