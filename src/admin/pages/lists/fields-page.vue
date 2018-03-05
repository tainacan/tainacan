<template>
    <div>
        <nav class="level header"><h1>Fields Page</h1></nav>
        <div class="primary-page">
            <router-link tag="button" class="button is-secondary"
                        :to="{ path: $routerHelper.getNewFieldPath() }">
                {{ $i18n.get('new') + ' ' + $i18n.get('field') }}
            </router-link>    
        </div>
    </div>
</template>

<script>
export default {
    name: 'FieldsPage',
    data(){
        return {
        }
    }
}
</script>

<style scoped>

</style>


