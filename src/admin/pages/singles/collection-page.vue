<template>
    <div class="columns is-fullheight">
        <secondary-menu :id="collectionId"></secondary-menu>
        <section class="container column is-secondary-content"> 
            <router-view></router-view>
        </section>
    </div>
</template>

<script>
import SecondaryMenu from '../../components/secondary-menu.vue';

export default {
    name: 'CollectionPage',
    data(){
        return {
            collectionId: Number
        }
    },
    methods: {
    },
    components: {
        SecondaryMenu
    },
    created(){
        this.collectionId = parseInt(this.$route.params.collectionId);
    }

}
</script>

<style scoped>

</style>


