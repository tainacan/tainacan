<template>
    <div :class="{ 'repository-level-page page-container': isRepositoryLevel }">
        <tainacan-title 
                    :bread-crumb-items="[{ path: '', label: this.$i18n.get('metadata') }]"/>
        
        <template v-if="isRepositoryLevel">
            <p>{{ $i18n.get('info_repository_metadata_inheritance') }}</p>
            <br>
        </template>
        
        <metadata-list :is-repository-level="isRepositoryLevel"/>
    </div>
</template>

<script>
import MetadataList from '../../components/lists/metadata-list.vue';

export default {
    name: 'MetadataPage',
    components: {
        MetadataList
    },
    data() {
        return {
            isRepositoryLevel: false
        }
    },
    created() {
        this.isRepositoryLevel = (this.$route.params.collectionId === undefined);
    }
}
</script>

<style scoped>

</style>


