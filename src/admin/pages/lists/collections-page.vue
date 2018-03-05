<template>
    <div>
        <div class="primary-page">
            <router-link tag="button" class="button is-secondary"
                        :to="{ path: $routerHelper.getNewCollectionPath() }">
                {{ $i18n.get('new') + ' ' + $i18n.get('collection') }}
            </router-link>
            <collections-list></collections-list>       
        </div>   
    </div>
</template>

<script>
import CollectionsList from '../../components/collections-list.vue';

export default {
    name: 'CollectionsPage',
    data(){
        return {
        }
    },
    components: {
        CollectionsList
    }
}
</script>

<style scoped>

</style>


