<template>
    <div>

        <div class="mappers-container">
            <div
                    class="mapper"
                    v-for="mapper in mappers"
                    :key="mapper.slug">
                <button 
                        class="mapper-clickable"
                        @click="goToMapperEditionPage(mapper.slug)">
                    <h4>{{ mapper.name }}</h4>
                    <p>{{ mapper.description }}</p>  
                </button>     
                <b-switch
                        v-if="!isRepositoryLevel"
                        style="z-index: 1;position: relative;"
                        :false-value="true"
                        :true-value="false" 
                        :value="mapper.disabled"
                        @input="updateCurrentMapper($event, mapper.slug)"
                        size="is-small" />     
            </div>

        </div>
        
        <b-loading 
                :active="isLoading" 
                :can-cancel="false"/>

    </div>
</template>

<script>
    import { mapActions } from 'vuex';

    export default {
        name: 'MappersList',
        props: {
            isLoading: false,
            isRepositoryLevel: false,
            mappers: Array
        },
        methods: {
            ...mapActions('metadata', [
                'updateMapper',
            ]),
            goToMapperEditionPage(mapperSlug) {
                if ( this.isRepositoryLevel )
                    this.$router.push(this.$routerHelper.getMapperEditPath(mapperSlug));
                else
                    this.$router.push(this.$routerHelper.getCollectionMapperEditPath(this.$route.params.collectionId, mapperSlug));
            },
            updateCurrentMapper($event, mapperSlug) {
                this.updateMapper({
                    isRepositoryLevel: this.isRepositoryLevel,
                    collectionId: this.$route.params.collectionId,
                    disabled: $event,
                    mapper: mapperSlug
                });
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
            background-color: var(--tainacan-item-background-color);
            transition: border 0.3s ease, background-color 0.15s ease;

            &:hover {
                background-color: var(--tainacan-item-hover-background-color);
                border: 1px solid var(--tainacan-gray3);
            }

            .switch {
                float: left;
                margin-top: 12px;
            }

            .mapper-clickable {
                text-align: start;
                border: none;
                background: none;
                width: 100%;
                cursor: pointer;
            }
        }
    }
</style>
