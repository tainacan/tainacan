<template>
    <div>
        <nav class="level header"><h1>Filters Page</h1></nav>
        <div class="primary-page">
            <router-link tag="button" class="button is-secondary"
                        :to="{ path: $routerHelper.getNewFilterPath() }">
                {{ $i18n.get('new') + ' ' + $i18n.get('filter') }}
            </router-link>      
        </div>
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


