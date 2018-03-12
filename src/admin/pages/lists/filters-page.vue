<template>
    <div class="primary-page page-container">
        <h1>Filters Page</h1>
        <router-link tag="button" class="button is-secondary"
                    :to="{ path: $routerHelper.getNewFilterPath() }">
            {{ $i18n.get('new') + ' ' + $i18n.get('filter') }}
        </router-link>      
    </div>
</template>

<script>
export default {
    name: 'FiltersPage',
    data(){
        return {
        }
    }
}
</script>

<style scoped>

</style>


