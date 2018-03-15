<template>
    <div class="primary-page page-container">
        <filters-list></filters-list>
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


