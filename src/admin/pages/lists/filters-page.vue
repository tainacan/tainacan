<template>
    <div class="repository-level-page page-container">
        <tainacan-title 
                :bread-crumb-items="[{ path: '', label: this.$i18n.get('filters') }]"/>
        <filters-list/>
    </div>
</template>

<script>
import FiltersList from '../../components/lists/filters-list.vue';

export default {
    name: 'FiltersPage',
    data(){
        return {
        }
    },
    components: {
        FiltersList
    }
}
</script>

<style scoped>

</style>


