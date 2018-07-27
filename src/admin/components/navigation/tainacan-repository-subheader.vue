<template>
    <div
            id="tainacan-repository-subheader" 
            class="level secondary-page"
            :class="{'is-menu-compressed': isMenuCompressed, 'is-repository-level' : isRepositoryLevel}">
        <h1 v-if="isRepositoryLevel">{{ repositoryName }}</h1>
        <h1 v-else>{{ $i18n.get('collection') + '' }} <span class="has-text-weight-bold">{{ collectionName }}</span></h1>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'TainacanRepositorySubheader',
    data() {
        return {
            repositoryName: tainacan_plugin.repository_name
        }
    },
    props: {
        isMenuCompressed: false,
        isRepositoryLevel: true
    },
    computed: {
        collectionName() {
            return this.getCollectionName();
        }
    },
    methods: {
        ...mapActions('collection', [
            'fetchCollectionName'
        ]),
        ...mapGetters('collection', [
            'getCollectionName',
        ])
    },
    mounted() {
        this.fetchCollectionName();
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
        padding-right: $page-side-padding;
        padding-left: calc((4.166666667% - 6.666666667px) + 160px);
        margin: 0px;
        vertical-align: middle; 
        left: 0;
        right: 0;
        top: $header-height;
        position: absolute;
        z-index: 9;
        transition: padding-left 0.2s linear, background-color 0.2s linear;

        &.is-repository-level {
            background-color: $blue5;
        }

        &.is-menu-compressed {     
            padding-left: calc((4.166666667% - 2.083333333px)  + 50px);
        }

        h1 {
            font-size: 18px;
            color: white;
            line-height: 18px;
            max-width: 100%;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;  
            transition: all 0.2s linear;
        }
    }
</style>


