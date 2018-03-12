<template>
        <div class="primary-page page-container">
            <router-link tag="button" class="button is-secondary"
                        :to="{ path: $routerHelper.getNewFieldPath() }">
                {{ $i18n.get('new') + ' ' + $i18n.get('field') }}
            </router-link>
            <fields-list></fields-list>    
        </div>
</template>

<script>
import FieldsList from '../../components/lists/fields-list.vue';

export default {
    name: 'FieldsPage',
    data(){
        return {
        }
    },
    components: {
        FieldsList
    }
}
</script>

<style scoped>

</style>


