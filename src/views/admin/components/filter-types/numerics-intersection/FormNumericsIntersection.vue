<template>
    <div>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-filter-numeric-interval', 'step') }}<span>&nbsp;*&nbsp;</span>
                <span style="font-size: 1.35em;">
                    <help-button
                            :title="$i18n.getHelperTitle('tainacan-filter-numeric-interval', 'step')"
                            :message="$i18n.getHelperMessage('tainacan-filter-numeric-interval', 'step')" />
                </span>
            </label>
            <div
                    v-if="!showEditStepOptions"
                    class="is-flex">
                <b-select
                        v-model="step"
                        name="step_options"
                        @update:model-value="emitValues()">
                    <option value="0.001">
                        0.001
                    </option>
                    <option value="0.01">
                        0.01
                    </option>
                    <option value="0.1">
                        0.1
                    </option>
                    <option value="1">
                        1
                    </option>
                    <option value="2">
                        2
                    </option>
                    <option value="5">
                        5
                    </option>
                    <option value="10">
                        10
                    </option>
                    <option value="100">
                        100
                    </option>
                    <option value="1000">
                        1000
                    </option>
                    <option
                            v-if="step && ![0.001,0.01,0.1,1,2,5,10,100,1000].find( (element) => element == step )"
                            :value="step">
                        {{ step }}</option>
                </b-select>
                <button
                        class="button is-white is-pulled-right"
                        :aria-label="$i18n.get('edit')"
                        @click.prevent="showEditStepOptions = true">
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
            </div>
            <div
                    v-if="showEditStepOptions"
                    class="is-flex">
                <b-input
                        v-model="step"
                        name="max_options"
                        type="number"
                        step="1"
                        @update:model-value="emitValues()" />
                <button
                        class="button is-white is-pulled-right"
                        @click.prevent="showEditStepOptions = false">
                    <span 
                            v-tooltip="{
                                content: $i18n.get('close'),
                                autoHide: true,
                                placement: 'bottom',
                                popperClass: ['tainacan-tooltip', 'tooltip']
                            }"
                            class="icon">
                        <i class="tainacan-icon tainacan-icon-18px tainacan-icon-close has-text-secondary" />
                    </span>
                </button>
            </div>
        </b-field>
        <b-field 
                :addons="false"
                :type="metadataType"
                :message="metadataMessage">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-filter-numerics-intersection', 'secondary_filter_metadatum_id') }}<span :class="metadataType">&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-filter-numerics-intersection', 'secondary_filter_metadatum_id')"
                        :message="$i18n.getHelperMessage('tainacan-filter-numerics-intersection', 'secondary_filter_metadatum_id')" />
            </label>
            <b-autocomplete
                    v-if="!secondNumericMetadatumId"
                    v-model="metadataSearch"
                    v-a11y-autocomplete="{ appendToBody: true }"
                    name="numerics_intersect[secondary_filter_metadatum_id]"
                    :placeholder="$i18n.get('instruction_select_second_numeric_to_compare')"
                    :data="metadata"
                    field="name"
                    :loading="loading"
                    :append-to-body="true"
                    open-on-focus
                    expanded
                    @select="onSelectSecondNumericMetadatum"
                    @update:model-value="fetchMetadata"
                    @focus="clear()">
                <template #empty>
                    {{ $i18n.get('info_no_options_found') }}
                </template>
            </b-autocomplete>
            <div
                    v-else
                    class="control selected-metadatum">
                <span>{{ secondNumericMetadatumName }}</span>
                <button
                        type="button"
                        class="button is-white"
                        :aria-label="$i18n.get('remove_value')"
                        @click.prevent="clearSelectedMetadatum">
                    <span class="icon is-small">
                        <i class="tainacan-icon tainacan-icon-close" />
                    </span>
                </button>
            </div>
        </b-field>
        <fieldset 
                v-if="secondNumericMetadatumId"
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
                <p>&nbsp;<em>{{ secondNumericMetadatumName }}</em></p>
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
                :label="$i18n.getHelperTitle('tainacan-filter-numerics-intersection', 'accept_numeric_interval')"
                style="margin-top: 1.125rem;"
                :type="errors && errors['accept_numeric_interval'] != undefined ? 'is-danger' : ''"
                :message="errors && errors['accept_numeric_interval'] != undefined ? errors['accept_numeric_interval'] : ''">
                &nbsp;
            <b-switch
                    v-model="acceptNumericInterval"
                    size="is-small"
                    :true-value="'yes'"
                    :false-value="'no'"
                    :native-value="acceptNumericInterval == 'yes' ? 'yes' : 'no'"
                    name="accept_numeric_interval"
                    @update:model-value="emitValues()">
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-filter-numerics-intersection', 'accept_numeric_interval')"
                        :message="$i18n.getHelperMessage('tainacan-filter-numerics-intersection', 'accept_numeric_interval')" />
            </b-switch>
        </b-field> -->
    </div>
</template>

<script>
    import { tainacanApi } from '../../../js/axios';

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
                step: [Number, String],
                showEditStepOptions: false,
                metadata: [],
                metadataSearch: '',
                metadataSource: [],
                metadataSourceLoaded: false,
                metadataRequestId: 0,
                loading: true,
                metadataType: '',
                metadataMessage: '',
                secondNumericMetadatumId: [Number, String],
                secondNumericMetadatumName: String,
                firstComparator: String,
                secondComparator: String,
                comparatorsObject: {},
                acceptNumericInterval: String,
                showEditFirstComparatorOptions: false,
                showEditSecondComparatorOptions: false
            }
        },
        watch: {
            errors() {
                if ( this.errors && this.errors.secondary_filter_metadatum_id !== '' )
                    this.setErrorsAttributes( 'is-danger', this.errors.secondary_filter_metadatum_id );
                else
                    this.setErrorsAttributes( '', '' );
            }
        },
        created() {
            this.step = this.modelValue && this.modelValue.step ? this.modelValue.step : 1;
            this.secondNumericMetadatumId = this.modelValue && this.modelValue.secondary_filter_metadatum_id ? this.modelValue.secondary_filter_metadatum_id : '';
            this.secondNumericMetadatumName = this.modelValue && this.modelValue.secondary_filter_metadatum_name ? this.modelValue.secondary_filter_metadatum_name : '';
            this.firstComparator = this.modelValue && this.modelValue.first_comparator ? this.modelValue.first_comparator : '>=';
            this.secondComparator = this.modelValue && this.modelValue.second_comparator ? this.modelValue.second_comparator : '<=';
            this.acceptNumericInterval = this.modelValue && this.modelValue.accept_numeric_interval ? this.modelValue.accept_numeric_interval : 'no';
            
            if (this.secondNumericMetadatumId && !this.secondNumericMetadatumName)
                this.fetchSelectedMetadatumName();
            else if (!this.secondNumericMetadatumId)
                this.fetchMetadata('');
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
                    label: this.$i18n.get('greater_than')
                },
                '>=': {
                    symbol: '&#8805;',
                    label: this.$i18n.get('greater_than_or_equal_to')
                },
                '<': {
                    symbol: '&#60;',
                    label: this.$i18n.get('less_than')
                },
                '<=': {
                    symbol: '&#8804;',
                    label: this.$i18n.get('less_than_or_equal_to')
                }
            };
        },
        methods: {
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
                    ? '/collection/' + this.filter.collection_id + '/metadata/' + this.secondNumericMetadatumId
                    : '/metadata/' + this.secondNumericMetadatumId;

                this.loading = true;
                return tainacanApi.get(endpoint)
                    .then(res => {
                        this.secondNumericMetadatumName = res.data && res.data.name ? res.data.name : '';
                        this.loading = false;
                    })
                    .catch(error => {
                        this.$console.log(error);
                        this.loading = false;
                    });
            },
            fetchMetadata: _.debounce(function(search) {
                const query = search || '';

                if (!this.isCollectionMetadata()) {
                    if (this.metadataSourceLoaded) {
                        this.metadata = this.filterMetadata(this.metadataSource, query);
                        this.loading = false;
                        return;
                    }
                }

                const requestId = ++this.metadataRequestId;
                this.loading = true;

                let endpoint = this.isCollectionMetadata()
                    ? ( '/collection/' + this.filter.collection_id + '/metadata' )
                    : '/metadata';
                endpoint += '?metaquery[0][key]=metadata_type&metaquery[0][value]=' + encodeURIComponent('Tainacan\\Metadata_Types\\Numeric');
                endpoint += '&perpage=' + this.getMaxPerPage() + '&paged=1&order=asc&orderby=title&exclude=' + this.filter.metadatum_id;
                if (query && this.isCollectionMetadata())
                    endpoint += '&search=' + encodeURIComponent(query);

                return tainacanApi.get(endpoint)
                    .then(res => {
                        if (requestId !== this.metadataRequestId)
                            return;

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
                        if (requestId !== this.metadataRequestId)
                            return;

                        this.loading = false;
                        this.$console.log(error);
                    });
            }, 500),
            onSelectSecondNumericMetadatum(metadatum) {
                if (!metadatum || !metadatum.id)
                    return;

                this.secondNumericMetadatumId = metadatum.id;
                this.secondNumericMetadatumName = metadatum.name || '';
                this.metadataSearch = '';
                this.emitValues();
            },
            clearSelectedMetadatum() {
                this.metadataRequestId++;
                this.secondNumericMetadatumId = '';
                this.secondNumericMetadatumName = '';
                this.metadataSearch = '';
                this.emitValues();
                this.fetchMetadata('');
            },
            emitValues() {
                this.$emit('update:model-value', {
                    step: this.step,
                    first_comparator: this.firstComparator,
                    second_comparator: this.secondComparator,
                    secondary_filter_metadatum_id: this.secondNumericMetadatumId,
                    secondary_filter_metadatum_name: this.secondNumericMetadatumName,
                    accept_numeric_interval: this.acceptNumericInterval
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
        margin-inline-start: 0em;
        margin-inline-end: -0.5em;
        margin-block-start: 0em;
        margin-block-end: 0.5em;
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

.selected-metadatum {
    border: 1px solid var(--tainacan-gray2);
    padding: calc(0.57em - 1px) 8px;
    font-size: 0.875em;
    min-height: 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;

    button {
        border-radius: 100em !important;
    }
}
</style>