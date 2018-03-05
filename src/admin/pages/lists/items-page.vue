<template>  
    <div>
        <h1>Items Page</h1>
        <router-link tag="button" class="button is-secondary"
                    :to="{ path: $routerHelper.getNewItemPath(collectionId) }">
            {{ $i18n.get('new') + ' ' + $i18n.get('item') }}
        </router-link>
        <items-list :collection-id="collectionId"></items-list>
    </div>
</template>

<script>
import ItemsList from '../../components/items-list.vue';

export default {
    name: 'ItemsPage',
    data(){
        return {
            collectionId: Number
        }
    },
    components: {
        ItemsList
    },
    created() {
        this.collectionId = this.$route.params.id;
    }
}
</script>

<style scoped>

</style>


