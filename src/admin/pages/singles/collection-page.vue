<template>
    <div class="columns is-fullheight">
        <secondary-menu :id="collectionId"/>
        <section class="column is-secondary-content">
            <router-view class="page-container"/>
        </section>
    </div>
</template>

<script>
import SecondaryMenu from '../../components/navigation/secondary-menu.vue';

export default {
    name: 'CollectionPage',
    data(){
        return {
            collectionId: Number
        }
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


