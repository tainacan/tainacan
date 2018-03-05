<template>
    <div>
        <nav class="level header"><h1>Category Page</h1></nav>
        <div class="primary-page">
            <router-link tag="button" class="button is-secondary"
                        :to="{ path: $routerHelper.getNewCategoryPath() }">
                {{ $i18n.get('new') + ' ' + $i18n.get('category') }}
            </router-link>    
        </div>
    </div>
</template>

<script>
export default {
    name: 'CategoriesPage',
    data(){
        return {
        }
    }
}
</script>

<style scoped>

</style>


