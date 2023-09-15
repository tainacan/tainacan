<template>
    <div>

        <div class="mappers-container">
            <div
                    class="mapper"
                    v-for="mapper in mappers"
                    :key="mapper.slug"
                    @click="goToMapperEditionPage(mapper.slug)">
                <h4>{{ mapper.name }}</h4>
                <p>{{ mapper.description }}</p>       
                <b-switch 
                        v-model="mapper.enabled"
                        size="is-small" />     
            </div>

        </div>
        
        <b-loading 
                :active="isLoading" 
                :can-cancel="false"/>

    </div>
</template>

<script>
    export default {
        name: 'MappersList',
        props: {
            isLoading: false,
            mappers: Array
        },
        methods: {
            goToMapperEditionPage(mapperSlug) {
                this.$router.push(this.$routerHelper.getMapperEditPath(mapperSlug));
            }
        },
    }
</script>

<style scoped lang="scss">
    .mappers-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin: 20px;

        .mapper {
            border: 1px solid var(--tainacan-gray2);
            padding: 15px;
            min-height: 100px;
            cursor: pointer;
            background-color: var(--tainacan-item-background-color);
            transition: border 0.3s ease, background-color 0.15s ease;

            &:hover {
                background-color: var(--tainacan-item-hover-background-color);
                border: 1px solid var(--tainacan-gray3);
            }
        }
    }
</style>
