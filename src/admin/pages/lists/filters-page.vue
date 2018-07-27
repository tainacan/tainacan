<template>
    <div class="repository-level-page page-container">
        <tainacan-title />
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


