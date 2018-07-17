<template>
    <div>
        <div class="primary-page page-container">
            <tainacan-title />

            <div class="above-subheader">
                <b-loading 
                        :active.sync="isLoading" 
                        :can-cancel="false"/>
                <div>
                    <processes-list
                            :is-loading="isLoading"
                            :total="total"
                            :page="page"
                            :processes-per-page="processesPerPage"
                            :processes="processes"/>
                    
                    <!-- Empty state image -->
                    <div v-if="processes.length <= 0 && !isLoading">
                        <section class="section">
                            <div class="content has-text-grey has-text-centered">
                                <p>
                                    <b-icon
                                            icon="inbox"
                                            size="is-large"/>
                                </p>
                                <p v-if="status == undefined || status == ''">{{ $i18n.get('info_no_process') }}</p>
                            </div>
                        </section>
                    </div>
                    <!-- Footer -->
                    <div 
                            class="pagination-area" 
                            v-if="processes.length > 0">
                        <div class="shown-items">
                            {{
                                $i18n.get('info_showing_processes') +
                                (processesPerPage * (page - 1) + 1) +
                                $i18n.get('info_to') +
                                getLastProcessesNumber() +
                                $i18n.get('info_of') + total + '.'
                            }}
                        </div>
                        <div class="items-per-page">
                            <b-field 
                                    horizontal 
                                    :label="$i18n.get('label_processes_per_page')">
                                <b-select
                                        :value="processesPerPage"
                                        @input="onChangePerPage"
                                        :disabled="processes.length <= 0">
                                    <option value="12">12</option>
                                    <option value="24">24</option>
                                    <option value="48">48</option>
                                    <option value="96">96</option>
                                </b-select>
                            </b-field>
                        </div>
                        <div class="pagination">
                            <b-pagination
                                    @change="onPageChange"
                                    :total="total"
                                    :current.sync="page"
                                    order="is-centered"
                                    size="is-small"
                                    :per-page="processesPerPage"/>
                        </div>
                    </div>
                </div>
            </div>    
        </div>
    </div>
</template>

<script>
    import ProcessesList from "../../components/lists/processes-list.vue";
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: 'Processes',
        data(){
            return {
                isLoading: false,
                total: 0,
                page: 1,
                processesPerPage: 12,
            }
        },
        components: {
            ProcessesList
        },
        methods: {
            ...mapActions('bgprocess', [
                'fetchProcesses',
            ]),
            ...mapGetters('bgprocess', [
                'getProcesses'
            ]),
            onChangePerPage(value) {
               this.processesPerPage = value;
                this.$userPrefs.set('processes_per_page', value)
                .then((newValue) => {
                    this.processesPerPage = newValue;
                })
                .catch(() => {
                    this.$console.log("Error settings user prefs for processes per page")
                });
                this.load();
            },
            onPageChange(page) {
                this.page = page;
                this.load();
            },
            load() {
                this.isLoading = true;

                this.fetchProcesses({ 'page': this.page, 'processesPerPage': this.processesPerPage })
                    .then((res) => {
                        this.isLoading = false;
                        this.total = res.total;
                    })
                    .catch(() => {
                        this.isLoading = false;
                    });
            },
            getLastProcessesNumber() {
                let last = (Number(this.processesPerPage * (this.page - 1)) + Number(this.processesPerPage));
                return last > this.total ? this.total : last;
            }
        },
        computed: {
            processes(){
                return this.getProcesses();
            }
        },
        created() {
            this.processesPerPage = this.$userPrefs.get('processes_per_page');
        },
        mounted(){
            if (this.processesPerPage != this.$userPrefs.get('processes_per_page'))
                this.processesPerPage = this.$userPrefs.get('processes_per_page');

            if (!this.processesPerPage) {
                this.processesPerPage = 12;
                this.$userPrefs.set('processes_per_page', 12);
            }
            
            this.load();
        }
    }
</script>

<style lang="scss" scoped>
    @import '../../scss/_variables.scss';

    .sub-header {
        max-height: $subheader-height;
        height: $subheader-height;
        margin-left: -$page-side-padding;
        margin-right: -$page-side-padding;
        margin-top: -$page-top-padding;
        padding-top: $page-small-top-padding;
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
        border-bottom: 1px solid #ddd;

        .header-item {
            display: inline-block;
            padding-right: 8em;
        }

        @media screen and (max-width: 769px) {
            height: 60px;
            margin-top: -0.5em;
            padding-top: 0.9em;

            .header-item {
                padding-right: 0.5em;
            }
        }
    }
    .tabs {
        padding-top: 20px;
        margin-bottom: 20px;
        padding-left: $page-side-padding;
        padding-right: $page-side-padding;
    }
    .above-subheader {
        margin-bottom: 0;
        margin-top: 0;
        height: auto;
    }
</style>


