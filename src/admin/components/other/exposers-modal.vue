<template>
    <form action="">
        <div 
                class="tainacan-modal-content" 
                style="width: auto">
            <header class="tainacan-modal-title">
                <h2>{{ this.$i18n.get('label_urls') }}</h2>
                <hr>
            </header>
            <section class="tainacan-form">
                 <p>{{ $i18n.get('instruction_select_an_importer_type') }}</p>
                <div class="exposer-types-container">
                    <b-field 
                            :addons="false"
                            class="exposer-type"
                            v-for="(exposerType, index) in availableExposers"
                            :key="index">
                        <span 
                                @click="collapse(index)"
                                class="collapse-handle">
                            <span class="icon">
                                <i 
                                        :class="{ 'tainacan-icon-arrowdown' : !exposerType.collapsed, 'tainacan-icon-arrowright' : exposerType.collapsed }"
                                        class="has-text-secondary tainacan-icon tainacan-icon-20px"/>
                            </span>
                            <label 
                                    v-tooltip="{
                                        content: exposerType.name,
                                        autoHide: false,
                                        placement: 'auto-end'
                                    }" 
                                    class="label">
                                {{ exposerType.name }}
                            </label>
                            <span class="has-text-gray">{{ exposerType.description }}</span>
                        </span>
                        <transition name="filter-item">
                            <div v-show="!exposerType.collapsed">    
                                <div class="exposer-item">
                                    <span>{{ $i18n.get('label_exposer') + ": " + exposerType.name }}</span>
                                    <span class="exposer-item-actions">
                                        <a 
                                                target="_blank"
                                                :href="exposerBaseURL + '&exposer=' + exposerType.slug">
                                            <span class="gray-icon">
                                                <i class="tainacan-icon tainacan-icon-20px tainacan-icon-url"/>
                                            </span>
                                        </a>
                                    </span> 
                                </div> 
                                <div 
                                        v-for="(exposerMapper, index) of exposerType.mappers"
                                        :key="index"
                                        class="exposer-item">
                                    <span>{{ $i18n.get('label_exposer') + ": " + exposerType.name + ", " + $i18n.get('label_mapper') + ": " + exposerMapper }}</span>
                                    <span class="exposer-item-actions">
                                        <a 
                                                target="_blank"
                                                :href="exposerBaseURL + '&exposer=' + exposerType.slug + '&mapper=' + exposerMapper">
                                            <span class="gray-icon">
                                                <i class="tainacan-icon tainacan-icon-20px tainacan-icon-url"/>
                                            </span>
                                        </a>
                                    </span>  
                                </div>      
                            </div>
                        </transition>
                    </b-field>
                </div>
                
                <b-loading 
                        :active.sync="isLoading" 
                        :can-cancel="false"/>
               <!-- <footer class="field is-grouped form-submit">
                    <div class="control">
                        <button 
                                class="button is-outlined" 
                                type="button" 
                                @click="$parent.close()">Close</button>
                    </div>
                    <div class="control">
                        <button class="button is-success">Confirm</button>
                    </div>
                </footer> -->
            </section>
        </div>
    </form>     
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import qs from 'qs';

export default {
    name: 'ExposersModal',
    props: {
        collectionId: Number
    },
    data(){
        return {
            isLoading: false,
            availableExposers: []
        }
    },
    computed: {
        exposerBaseURL() {
            let baseURL = this.collectionId != undefined ? '/collection/' + this.collectionId + '/items/' : 'items';
            let currentParams = this.$route.query;

            // Removes Fetch Only
            if (currentParams.fetch_only != undefined)
                delete currentParams.fetch_only;
            
            // Handles pagination of this link
            if (currentParams.paged != 1)
                currentParams.paged = 1;
            currentParams.perpage = 100;

            return tainacan_plugin.tainacan_api_url + baseURL + '?' + qs.stringify(currentParams);
        }
    },
    methods: {
        ...mapActions('exposer', [
            'fetchAvailableExposers'
        ]),
        ...mapGetters('exposer', [
            'getAvailableExposers'
        ]),
        collapse(index) {
            let exposer = this.availableExposers[index];
            this.$set(exposer, 'collapsed', !exposer.collapsed);
            this.$set(this.availableExposers, index, exposer);
        }
    },
    mounted() {
        this.isLoading = true;
        this.fetchAvailableExposers()
            .then((res) => {
                let exposers = JSON.parse(JSON.stringify(res));
                exposers.forEach(item => this.$set(item, 'collapsed', true));
                this.availableExposers = exposers;

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

    .exposer-types-container {

        .exposer-type {
            border-bottom: 1px solid $gray2;
            padding: 0.75rem $page-side-padding;
            margin: 0;
        
            &:first-child {
                margin-top: 0.75rem;
            }
            &:last-child {
                border-bottom: none;
            }
            .collapse-handler:hover {
                cursor: pointer;
                background-color: $gray1;
            }
            .collapse-handle {
                .label {
                    margin: 3px 0.75rem 0 0;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    overflow: hidden;
                }
                .has-text-gray {
                    font-size: 0.75rem;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    overflow: hidden;
                }
            }

            p {
                padding: 0.5rem 0.75rem;
            }

            .exposer-item {
                margin: 0; 
                display: flex;
                justify-content: space-between;
                align-items: center;

                &:first-of-type {
                    margin-top: 0.5rem;
                }
                &>span {
                    padding: 0.5rem 0.75rem;
                    font-size: 0.75rem;
                }
                &:hover {
                    background-color: $gray1;
                    .exposer-item-actions {
                        background-color: $gray2;
                    }
                }
                .exposer-item-actions a {
                    cursor: pointer;
                    margin: 0 0.5rem;
                }
            }
        }
    }

</style>


 
