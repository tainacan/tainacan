<template>
    <form action="">
        <div 
                class="tainacan-modal-content" 
                style="width: auto">
            <header class="tainacan-modal-title">
                <h2>{{ this.$i18n.get('label_alternative_exposer_urls') }}</h2>
                <hr>
            </header>
            <section class="tainacan-form">
                 <p>{{ $i18n.get('instruction_select_an_importer_type') }}</p>
                <div class="importer-types-container">
                    <b-field 
                            :addons="false"
                            class="importer-type"
                            v-for="(exposerType, index) in availableExposers"
                            :key="index">
                        <span 
                                @click="collapse(index)"
                                class="collapse-handle">
                            <span class="icon">
                                <i 
                                        :class="{ 'tainacan-icon-arrowdown' : exposerType.collapsed, 'tainacan-icon-arrowright' : !exposerType.collapsed }"
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
                        </span>
                        <transition name="filter-item">
                            <div v-show="!exposerType.collapsed">    
                                <p>{{ exposerType.description }}</p>            
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

    .importer-types-container {

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


 
