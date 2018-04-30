<template>
        <div class="primary-page page-container">
            <title-row/>
            <fields-list/>    
        </div>
</template>

<script>
import FieldsList from '../../components/lists/fields-list.vue';
import TitleRow from '../../components/navigation/title-row.vue';

export default {
    name: 'FieldsPage',
    data(){
        return {
        }
    },
    components: {
        FieldsList,
        TitleRow
    }
}
</script>

<style scoped>

</style>


