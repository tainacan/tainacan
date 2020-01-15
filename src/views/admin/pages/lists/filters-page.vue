<template>
    <div class="repository-level-page page-container">
        <filters-list/>
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
        }
    }
}
</script>

<style scoped>

</style>


