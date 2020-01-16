<template>
    <div :class="{ 'repository-level-page page-container': isRepositoryLevel }">
        <tainacan-title 
                    :bread-crumb-items="[{ path: '', label: this.$i18n.get('filters') }]"/>
        
        <template v-if="isRepositoryLevel">
            <p>{{ $i18n.get('info_repository_filters_inheritance') }}</p>
            <br>
        </template>
        
        <filters-list :is-repository-level="isRepositoryLevel"/>
    </div>
</template>

<script>
import FiltersList from '../../components/lists/filters-list.vue';

export default {
    name: 'FiltersPage',
    components: {
        FiltersList
    },
    data(){
        return {
            isRepositoryLevel: false,
        }
    },
    created() {
        this.isRepositoryLevel = (this.$route.params.collectionId === undefined);
    }
}
</script>

<style scoped>

</style>


