<template>
    <div>
        <b-field 
                :addons="false"
                :type="metadataType"
                :message="metadataMessage">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-filter-dates-intersection', 'secondary_filter_metadatum_id') }}<span :class="metadataType">&nbsp;*&nbsp;</span>
                <span style="font-size: 1.35em;"> 
                    <help-button
                            :title="$i18n.getHelperTitle('tainacan-filter-dates-intersection', 'secondary_filter_metadatum_id')"
                            :message="$i18n.getHelperMessage('tainacan-filter-dates-intersection', 'secondary_filter_metadatum_id')" />
                </span>
            </label>
            <b-autocomplete
                    v-model="metadataSearch"
                    v-a11y-autocomplete="{ appendToBody: true }"
                    name="dates_intersect[secondary_filter_metadatum_id]"
                    :placeholder="$i18n.get('instruction_select_second_date_to_compare')"
                    :data="metadata"
                    field="name"
                    clearable
                    icon-right="menu-down"
                    :loading="loading"
                    :append-to-body="true"
                    open-on-focus
                    expanded
                    @select="onSelectSecondDateMetadatum"
                    @focus="onFocusMetadataSearch"
                    @active="onMetadataSuggestionsActive"
                    @typing="fetchMetadata">
                <template #empty>
                    {{ $i18n.get('info_no_options_found') }}
                </template>
            </b-autocomplete>
        </b-field>
        <fieldset 
                v-if="secondDateMetadatumId"
                class="intersection-explainer-section">
            <legend>
                <p>
                    <strong>{{ $i18n.get('info_intersection_explainer') }}</strong>
                    <span style="font-size: 1.35em;"> 
                        <help-button 
                                :title="$i18n.get('label_comparators')"
                                :message="$i18n.get('info_intersection_rules')" />
                    </span>
                </p>
            </legend>    
            <b-field :addons="false">
                <b-select
                        v-if="showEditFirstComparatorOptions"
                        v-model="firstComparator"
                        @update:model-value="emitValues()">
                    <option
                            v-for="(comparatorObject, comparatorKey) in comparatorsObject"
                            :key="comparatorKey" 
                            :value="comparatorKey"
                            v-html="comparatorObject.symbol + '&nbsp;' + comparatorObject.label" />
                </b-select>
                <strong 
                        v-else
                        v-html="comparatorsObject[firstComparator].symbol" />
                <p v-if="filter.metadatum">
                    &nbsp;
                    <em>{{ filter.metadatum.metadatum_name }}</em>
                </p>
                <button
                        v-if="!showEditFirstComparatorOptions"
                        class="button is-white is-pulled-right"
                        @click.prevent="showEditFirstComparatorOptions = true">
                    <span 
                            v-tooltip="{
                                content: $i18n.get('edit'),
                                autoHide: true,
                                placement: 'bottom',
                                popperClass: ['tainacan-tooltip', 'tooltip']
                            }"
                            class="icon">
                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-edit has-text-secondary" />
                    </span>
                </button>
                <button
                        v-else
                        class="button is-white is-pulled-right"
                        @click.prevent="showEditFirstComparatorOptions = false">
                    <span 
                            v-tooltip="{
                                content: $i18n.get('close'),
                                autoHide: true,
                                placement: 'bottom',
                                popperClass: ['tainacan-tooltip', 'tooltip']
                            }"
                            class="icon">
                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-approved has-text-secondary" />
                    </span>
                </button>
            </b-field>
            <div class="logic-divider">
                <span>{{ $i18n.get('label_and') }}</span>
            </div>
            <b-field :addons="false">
                <b-select
                        v-if="showEditSecondComparatorOptions"
                        v-model="secondComparator"
                        @update:model-value="emitValues()">
                    <option 
                            v-for="(comparatorObject, comparatorKey) in comparatorsObject"
                            :key="comparatorKey"
                            :value="comparatorKey"
                            v-html="comparatorObject.symbol + '&nbsp;' + comparatorObject.label" />
                </b-select>
                <strong 
                        v-else
                        v-html="comparatorsObject[secondComparator].symbol" />
                <p>&nbsp;<em>{{ secondDateMetadatumName }}</em></p>
                <button
                        v-if="!showEditSecondComparatorOptions"
                        class="button is-white is-pulled-right"
                        @click.prevent="showEditSecondComparatorOptions = true">
                    <span 
                            v-tooltip="{
                                content: $i18n.get('edit'),
                                autoHide: true,
                                placement: 'bottom',
                                popperClass: ['tainacan-tooltip', 'tooltip']
                            }"
                            class="icon">
                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-edit has-text-secondary" />
                    </span>
                </button>
                <button
                        v-else
                        class="button is-white is-pulled-right"
                        @click.prevent="showEditSecondComparatorOptions = false">
                    <span 
                            v-tooltip="{
                                content: $i18n.get('close'),
                                autoHide: true,
                                placement: 'bottom',
                                popperClass: ['tainacan-tooltip', 'tooltip']
                            }"
                            class="icon">
                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-approved has-text-secondary" />
                    </span>
                </button>
            </b-field>
        </fieldset>
        <!-- Much more complicated logic, will be possible if we implement #889 -->
        <!-- <b-field 
                :addons="false"
                :label="$i18n.getHelperTitle('tainacan-filter-dates-intersection', 'accept_date_interval')"
                style="margin-top: 1.125rem;"
                :type="errors && errors['accept_date_interval'] != undefined ? 'is-danger' : ''"
                :message="errors && errors['accept_date_interval'] != undefined ? errors['accept_date_interval'] : ''">
                &nbsp;
            <b-switch
                    v-model="acceptDateInterval"
                    size="is-small"
                    :true-value="'yes'"
                    :false-value="'no'"
                    :native-value="acceptDateInterval == 'yes' ? 'yes' : 'no'"
                    name="accept_date_interval"
                    @update:model-value="emitValues()">
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-filter-dates-intersection', 'accept_date_interval')"
                        :message="$i18n.getHelperMessage('tainacan-filter-dates-intersection', 'accept_date_interval')" />
            </b-switch>
        </b-field> -->
    </div>
</template>

<script>
    import { tainacanApi, CancelToken, isCancel } from '../../../js/axios';

    export default {
        props: {
            filter: Object,
            modelValue: Object,
            errors: Object
        },
        emits: [
            'update:model-value',
        ],
        data() {
            return {
                metadata: [],
                metadataSearch: '',
                metadataSource: [],
                metadataSourceLoaded: false,
                metadataSearchCancel: null,
                loading: true,
                metadataType: '',
                metadataMessage: '',
                secondDateMetadatumId: [Number, String],
                secondDateMetadatumName: String,
                firstComparator: String,
                secondComparator: String,
                comparatorsObject: {},
                acceptDateInterval: String,
                showEditFirstComparatorOptions: false,
                showEditSecondComparatorOptions: false
            }
        },
        watch: {
            metadataSearch(name) {
                if (!name)
                    this.onSelectSecondDateMetadatum(null);
            },
            errors(){
                if ( this.errors && this.errors.secondary_filter_metadatum_id !== '' )
                    this.setErrorsAttributes( 'is-danger', this.errors.secondary_filter_metadatum_id );
                else
                    this.setErrorsAttributes( '', '' );
            }
        },
        created() {
            this.secondDateMetadatumId = this.modelValue && this.modelValue.secondary_filter_metadatum_id ? this.modelValue.secondary_filter_metadatum_id : '';
            this.secondDateMetadatumName = this.modelValue && this.modelValue.secondary_filter_metadatum_name ? this.modelValue.secondary_filter_metadatum_name : '';
            this.metadataSearch = this.secondDateMetadatumName;
            this.firstComparator = this.modelValue && this.modelValue.first_comparator ? this.modelValue.first_comparator : '>=';
            this.secondComparator = this.modelValue && this.modelValue.second_comparator ? this.modelValue.second_comparator : '<=';
            this.acceptDateInterval = this.modelValue && this.modelValue.accept_date_interval ? this.modelValue.accept_date_interval : 'no';

            if (this.secondDateMetadatumId && !this.secondDateMetadatumName)
                this.fetchSelectedMetadatumName();
            else if (!this.secondDateMetadatumId)
                this.browseMetadata();
            else
                this.loading = false;

            this.comparatorsObject = {
                '=': {
                    symbol: '&#61;',
                    label: this.$i18n.get('is_equal_to')
                },
                '!=': {
                    symbol: '&#8800;',
                    label: this.$i18n.get('is_not_equal_to')
                },
                '>': {
                    symbol: '&#62;',
                    label: this.$i18n.get('after')
                },
                '>=': {
                    symbol: '&#8805;',
                    label: this.$i18n.get('after_or_on_day')
                },
                '<': {
                    symbol: '&#60;',
                    label: this.$i18n.get('before')
                },
                '<=': {
                    symbol: '&#8804;',
                    label: this.$i18n.get('before_or_on_day')
                }
            };
        },
        beforeUnmount() {
            this.cancelMetadataSearch();
        },
        methods: {
            cancelMetadataSearch() {
                if (this.metadataSearchCancel) {
                    this.metadataSearchCancel.cancel('Metadata search canceled.');
                    this.metadataSearchCancel = null;
                }
            },
            onFocusMetadataSearch() {
                this.clear();
                this.browseMetadata();
            },
            onMetadataSuggestionsActive(isOpen) {
                if (isOpen)
                    return;

                if (this.secondDateMetadatumId && this.secondDateMetadatumName && this.metadataSearch !== this.secondDateMetadatumName)
                    this.metadataSearch = this.secondDateMetadatumName;
            },
            browseMetadata() {
                this.loading = true;
                this.requestMetadata('');
            },
            isCollectionMetadata() {
                return this.filter && this.filter.collection_id && this.filter.collection_id !== 'default';
            },
            getMaxPerPage() {
                const configuredMax = Number(typeof tainacan_plugin !== 'undefined' ? tainacan_plugin.api_max_items_per_page : 0);
                if (!isNaN(configuredMax) && configuredMax > 0)
                    return configuredMax;
                return 96;
            },
            filterMetadata(list, query) {
                const needle = (query || '').toLowerCase();
                return (list || []).filter((metadatum) => {
                    if (this.filter && metadatum.id == this.filter.metadatum_id)
                        return false;
                    if (!needle)
                        return true;
                    return (metadatum.name || '').toLowerCase().indexOf(needle) >= 0;
                });
            },
            fetchSelectedMetadatumName() {
                const endpoint = this.isCollectionMetadata()
                    ? '/collection/' + this.filter.collection_id + '/metadata/' + this.secondDateMetadatumId
                    : '/metadata/' + this.secondDateMetadatumId;

                this.loading = true;
                return tainacanApi.get(endpoint)
                    .then(res => {
                        this.secondDateMetadatumName = res.data && res.data.name ? res.data.name : '';
                        this.metadataSearch = this.secondDateMetadatumName;
                        this.loading = false;
                    })
                    .catch(error => {
                        this.$console.log(error);
                        this.loading = false;
                    });
            },
            fetchMetadata: _.debounce(function(search) {
                const query = search || '';

                if (this.secondDateMetadatumName && query === this.secondDateMetadatumName)
                    return;

                this.requestMetadata(query);
            }, 500),
            requestMetadata(query) {
                if (!this.isCollectionMetadata() && this.metadataSourceLoaded) {
                    this.metadata = this.filterMetadata(this.metadataSource, query);
                    this.loading = false;
                    return;
                }

                this.cancelMetadataSearch();
                const source = CancelToken.source();
                this.metadataSearchCancel = source;
                this.loading = true;

                let endpoint = this.isCollectionMetadata()
                    ? ( '/collection/' + this.filter.collection_id + '/metadata' )
                    : '/metadata';
                endpoint += '?metaquery[0][key]=metadata_type&metaquery[0][value]=' + encodeURIComponent('Tainacan\\Metadata_Types\\Date');
                endpoint += '&perpage=' + this.getMaxPerPage() + '&paged=1&order=asc&orderby=title&exclude=' + this.filter.metadatum_id;
                if (query && this.isCollectionMetadata())
                    endpoint += '&search=' + encodeURIComponent(query);

                return tainacanApi.get(endpoint, { cancelToken: source.token })
                    .then(res => {
                        const metadata = res.data ? res.data : [];
                        if (!this.isCollectionMetadata()) {
                            this.metadataSource = metadata;
                            this.metadataSourceLoaded = true;
                            this.metadata = this.filterMetadata(metadata, query);
                        } else {
                            this.metadata = this.filterMetadata(metadata, '');
                        }
                        this.loading = false;
                    })
                    .catch(error => {
                        if (isCancel(error))
                            return;

                        this.loading = false;
                        this.$console.log(error);
                    });
            },
            onSelectSecondDateMetadatum(metadatum) {
                if (!metadatum || !metadatum.id) {
                    if (this.metadataSearch || (!this.secondDateMetadatumId && !this.secondDateMetadatumName))
                        return;

                    this.secondDateMetadatumId = '';
                    this.secondDateMetadatumName = '';
                    this.emitValues();
                    return;
                }

                this.secondDateMetadatumId = metadatum.id;
                this.secondDateMetadatumName = metadatum.name || '';
                this.metadataSearch = this.secondDateMetadatumName;
                this.emitValues();
            },
            emitValues() {
                this.$emit('update:model-value', {
                    first_comparator: this.firstComparator,
                    second_comparator: this.secondComparator,
                    secondary_filter_metadatum_id: this.secondDateMetadatumId,
                    secondary_filter_metadatum_name: this.secondDateMetadatumName,
                    accept_date_interval: this.acceptDateInterval
                });
            },
            setErrorsAttributes( type, message ) {
                this.metadataType = type;
                this.metadataMessage = message;
            },
            clear(){
                this.metadataType = '';
                this.metadataMessage = '';
            },
        }
    }
</script>

<style lang="scss" scoped>
.intersection-explainer-section {
    margin-top: 1.25rem;
    padding: 0.75em 0.75em 0.25em 0.75em;
    border: 1px solid var(--tainacan-gray1);

    legend {
        margin: -0.75em 0 0em 0;
        background-color: var(--tainacan-background-color);
        padding: 5px 5px 5px 0px;
    }

    .field {
        display: flex;
        gap: 0.5em;
        margin: 0 -0.5em 0.5em 0em;
        align-items: center;

        strong {
            margin-inline-start: 0.75em;
        }
    }

    button {
        border-radius: 100em !important;
        margin-inline-start: auto;
    }

    .logic-divider {
        display: none;
    }
}
</style>