<template>
    <div>
        <h1>Collections Page</h1>
        <router-link tag="button" class="button is-primary"
                    :to="{ path: `/collections/new` }">
            Criar Coleção
        </router-link>
        <collections-list></collections-list>          
    </div>
</template>

<script>
import CollectionsList from '../components/collections-list.vue';

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


