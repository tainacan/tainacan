<template>
    <div class="primary-page page-container">
        <title-row/>
        <filters-list/>
    </div>
</template>

<script>
import FiltersList from '../../components/lists/filters-list.vue';
import TitleRow from '../../components/navigation/title-row.vue';

export default {
    name: 'FiltersPage',
    data(){
        return {
        }
    },
    components: {
        FiltersList,
        TitleRow
    }
}
</script>

<style scoped>

</style>


