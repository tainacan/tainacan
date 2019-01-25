<template>
    <form action="">
        <div 
                class="tainacan-modal-content" 
                style="width: auto">
            <header class="tainacan-modal-title">
                <h2>{{ this.$i18n.get('collections') }}</h2>
                <hr>
            </header>
            <section class="tainacan-form">
                 <p>{{ $i18n.get('instruction_select_a_target_collection') }}</p>
                <div 
                        v-if="!isLoading" 
                        class="collection-types-container">
                    <div
                            class="collection-type"
                            v-for="(collection, index) in collections"
                            :key="index"
                            @click="onSelectCollection(collection)">
                        <h4>{{ collection.name }}</h4>
                        <p>{{ collection.length > 200 ? (collection.description.substring(0,197) + '...') : collection.description }}</p>            
                    </div>

                     <b-loading 
                        :is-full-page="false"
                        :active.sync="isLoading" 
                        :can-cancel="false"/>
                </div>
                
                 <footer class="field is-grouped form-submit">
                    <div class="control">
                        <button 
                                class="button is-outlined" 
                                type="button" 
                                @click="$parent.close()">Close</button>
                    </div>
                </footer>
            </section>
        </div>
    </form>     
</template>

<script>
import { mapActions } from 'vuex';

export default {
    name: 'CollectionsModal',
    data(){
        return {
            collections: [],
            isLoading: false
        }
    },
    methods: {
        ...mapActions('collection', [
            'fetchCollections'
        ]),
        onSelectCollection(collection) {
            this.$router.push(this.$routerHelper.getNewItemPath(collection.id));
            this.$parent.close();
        }
    },
    mounted() {
        this.isLoading = true;
        this.fetchCollections({ 
                page: 1, 
                collectionsPerPage: 96, 
                contextEdit: true
            })
            .then((res) => {
                this.collections = res.collections;
                this.isLoading = false;
            }).catch((error) => {
                this.$console.log(error);
                this.isLoading = false;
            });
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .collection-types-container {
        position: relative;

        .collection-type {
            border-bottom: 1px solid $gray2;
            padding: 15px 8.3333333%;
            cursor: pointer;
        
            &:first-child {
                margin-top: 15px;
            }
            &:last-child {
                border-bottom: none;
            }
            &:hover {
                background-color: $gray2;
            }
        }
    }

</style>


 
