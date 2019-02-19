<template>
    <form action="">
        <div 
                class="tainacan-modal-content" 
                style="width: auto">
            <header class="tainacan-modal-title">
                <h2>{{ this.$i18n.get('importers') }}</h2>
                <hr>
            </header>
            <section class="tainacan-form">
                 <p>{{ $i18n.get('instruction_select_an_importer_type') }}</p>
                <div 
                        role="list"
                        class="importer-types-container">
                    <div
                            role="listitem"
                            class="importer-type"
                            v-for="importerType in availableImporters"
                            :key="importerType.slug"
                            v-if="!(hideWhenManualCollection && !importerType.manual_collection)"
                            @click="onSelectImporter(importerType)">
                        <h4>{{ importerType.name }}</h4>
                        <p>{{ importerType.description }}</p>            
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
                    <!-- <div class="control">
                        <button class="button is-success">Confirm</button>
                    </div> -->
                </footer>
            </section>
        </div>
    </form>     
</template>

<script>
import { mapActions } from 'vuex';

export default {
    name: 'AvailableImportersModal',
    props: {
        targetCollection: Number,
        hideWhenManualCollection: false
    },
    data(){
        return {
            availableImporters: [],
            isLoading: false
        }
    },
    methods: {
        ...mapActions('importer', [
            'fetchAvailableImporters'
        ]),
        onSelectImporter(importerType) {
            this.$router.push({ path: this.$routerHelper.getImporterEditionPath(importerType.slug), query: { targetCollection: this.targetCollection } });
            this.$parent.close();
        }
    },
    mounted() {
        this.isLoading = true;
        this.fetchAvailableImporters()
            .then((res) => {
                this.availableImporters = res;
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

    .importer-types-container {
        position: relative;

        .importer-type {
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


 
