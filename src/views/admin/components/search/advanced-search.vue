<template>
    <form 
            id="advanced-search-form"
            ref="advancedSearchForm"
            tabindex="-1"
            class="tnc-advanced-search-container"
            role="search"
            aria-labelledby="advanced-search-heading"
            @submit.prevent.stop="performAdvancedSearch">
        <h3 id="advanced-search-heading">
            {{ $i18n.get('advanced_search') }}
        </h3>

        <!-- AND/OR relation between criteria -->
        <b-field
                v-if="canChooseRelation"
                :addons="false"
                class="advanced-search-relation-field">
            <template #label>
                <span id="advanced-search-relation-label">{{ $i18n.get('label_match_criteria') }}</span>
                <help-button
                        :title="$i18n.get('label_match_criteria')"
                        :message="$i18n.get('info_match_any_criteria')"
                        :extra-classes="isRepositoryLevel ? 'tainacan-repository-tooltip' : ''" />
            </template>
            <div
                    role="radiogroup"
                    aria-labelledby="advanced-search-relation-label"
                    class="advanced-search-relation-options">
                <b-radio-button
                        id="advanced-search-relation-and"
                        name="advanced-search-relation"
                        native-value="AND"
                        size="is-small"
                        :model-value="effectiveRelation"
                        @update:model-value="setRelation">
                    {{ $i18n.get('label_match_all_criteria') }}
                </b-radio-button>
                <b-radio-button
                        id="advanced-search-relation-or"
                        name="advanced-search-relation"
                        native-value="OR"
                        size="is-small"
                        :disabled="hasMixedCriteriaKinds"
                        :model-value="effectiveRelation"
                        @update:model-value="setRelation">
                    {{ $i18n.get('label_match_any_criteria') }}
                </b-radio-button>
            </div>
            <p
                    v-if="hasMixedCriteriaKinds"
                    class="help">
                {{ $i18n.get('info_match_any_criteria_mixed') }}
            </p>
        </b-field>

        <transition-group name="filter-item">
            <b-field
                    v-for="(searchCriterion, index) in searchCriteria"
                    :key="index + '-' + searchCriterion.index + '-' + searchCriterion.type"
                    grouped
                    group-multiline
                    class="tainacan-form">

                <!-- Metadata (Search criteria) -->
                <b-field class="column">
                    <b-select
                            :loading="isLoadingMetadata"
                            :placeholder="$i18n.get('instruction_select_a_metadatum')"
                            :aria-label="$i18n.get('instruction_select_a_metadatum')"
                            :disabled="advancedSearchQuery[searchCriterion.type] && advancedSearchQuery[searchCriterion.type][searchCriterion.index]"
                            :model-value="(advancedSearchQuery[searchCriterion.type] && advancedSearchQuery[searchCriterion.type][searchCriterion.index] ) ? advancedSearchQuery[searchCriterion.type][searchCriterion.index].key : null"
                            @update:model-value="addMetadatumToAdvancedSearchQuery(
                                { 
                                    metadatumId: $event,
                                    type: (metadataAsObject[$event] && metadataAsObject[$event].metadata_type_object) ? metadataAsObject[$event].metadata_type_object.primitive_type : '',
                                    taxonomy: (metadataAsObject[$event] && metadataAsObject[$event].metadata_type_options) ? metadataAsObject[$event].metadata_type_options.taxonomy : ''
                                }, 
                                searchCriterion,
                                index
                            )">
                        <template
                                v-for="(metadatum, metadatumIndex) in metadataAsArray"
                                :key="metadatumIndex">
                            <option
                                    v-if="metadatum.metadata_type_object.component !== 'tainacan-user' &&
                                        metadatum.metadata_type_object.component !== 'tainacan-geocoordinate' &&
                                        metadatum.metadata_type_object.component !== 'tainacan-relationship' &&
                                        metadatum.metadata_type_object.component !== 'tainacan-compound' &&
                                        metadatum.parent <= 0"
                                    :value="metadatum.id">
                                {{ metadatum.name }}
                            </option>
                            <optgroup
                                    v-if="metadatum.metadata_type_object.component === 'tainacan-compound'"
                                    :label="metadatum.name">
                                <template 
                                        v-for="(childMetadatum, childIndex) of metadatum.metadata_type_options.children_objects"
                                        :key="childIndex">
                                    <option
                                            v-if="childMetadatum.metadata_type_object.component !== 'tainacan-user' &&
                                                childMetadatum.metadata_type_object.component !== 'tainacan-geocoordinate' &&
                                                childMetadatum.metadata_type_object.component !== 'tainacan-relationship'"
                                            :value="childMetadatum.id">
                                        {{ childMetadatum.name }}
                                    </option>
                                </template>
                            </optgroup>
                        </template>
                        <option value="document_content_index">
                            {{ $i18n.get('label_document_content') }}
                        </option>
                    </b-select>
                </b-field>

                <!-- Comparators -->
                <b-field class="column">
                    <b-select
                            v-if="searchCriterion.type == 'metaquery' && advancedSearchQuery.metaquery[searchCriterion.index]"
                            :loading="isLoadingMetadata"
                            :model-value="advancedSearchQuery.metaquery[searchCriterion.index].compare"
                            :placeholder="$i18n.get('label_criterion_to_compare')"
                            :aria-label="$i18n.get('label_criterion_to_compare')"
                            @update:model-value="addComparatorToAdvancedSearchQuery($event, searchCriterion)">
                        <option 
                                v-for="(comparator, key) in getComparators(searchCriterion)"
                                :key="key"
                                :value="key"
                            >{{ comparator }}</option>
                    </b-select>
                    <b-select
                            v-else-if="searchCriterion.type == 'taxquery' && advancedSearchQuery.taxquery[searchCriterion.index]"
                            :loading="isLoadingMetadata"
                            :model-value="advancedSearchQuery.taxquery[searchCriterion.index].operator"
                            :placeholder="$i18n.get('label_criterion_to_compare')"
                            :aria-label="$i18n.get('label_criterion_to_compare')"
                            @update:model-value="addComparatorToAdvancedSearchQuery($event, searchCriterion)">
                        <option 
                                v-for="(comparator, key) in getComparators(searchCriterion)"
                                :key="key"
                                :value="key"
                            >{{ comparator }}</option>
                    </b-select>
                    <b-input
                            v-else
                            type="text"
                            disabled
                            :aria-label="$i18n.get('label_disabled')" />
                </b-field>

                <!-- Inputs -->
                <b-field class="column is-half is-10-mobile">
                    <template v-if="searchCriterion.type == 'metaquery' && advancedSearchQuery.metaquery[searchCriterion.index] && advancedSearchQuery.metaquery[searchCriterion.index].compare != 'NOT EXISTS' && advancedSearchQuery.metaquery[searchCriterion.index].compare != 'EXISTS'">
                        <b-input
                                v-if="getAdvancedSearchQueryCriterionMetadataType(searchCriterion.index) == 'int' || getAdvancedSearchQueryCriterionMetadataType(searchCriterion.index) == 'float'"
                                type="number"
                                step="any"
                                :model-value="advancedSearchQuery.metaquery[searchCriterion.index].value"
                                :placeholder="$i18n.get('label_number_to_search_for')"
                                :aria-label="$i18n.get('label_number_to_search_for')"
                                @update:model-value="addValueToAdvancedSearchQuery($event, searchCriterion)"
                            />
                        <input
                                v-else-if="getAdvancedSearchQueryCriterionMetadataType(searchCriterion.index) == 'date'"
                                v-imask="{ mask: dateMask, skipInvalid: false }"
                                class="input"
                                :value="parseValidDateToNavigatorLanguage(advancedSearchQuery.metaquery[searchCriterion.index].value)"
                                :placeholder="dateFormat"
                                type="text" 
                                :aria-label="$i18n.get('label_date_to_search_for')"
                                @input="addValueToAdvancedSearchQuery($event.target.value, searchCriterion)">
                        <b-input
                                v-else
                                type="text"
                                :model-value="advancedSearchQuery.metaquery[searchCriterion.index].value"
                                :placeholder="$i18n.get('label_string_to_search_for')"
                                :aria-label="$i18n.get('label_string_to_search_for')"
                                @update:model-value="addValueToAdvancedSearchQuery($event, searchCriterion)"
                            />
                    </template>
                    <b-input
                            v-else-if="searchCriterion.type == 'taxquery' && advancedSearchQuery.taxquery[searchCriterion.index] && advancedSearchQuery.taxquery[searchCriterion.index].operator != 'NOT EXISTS' && advancedSearchQuery.taxquery[searchCriterion.index].operator != 'EXISTS'"
                            :model-value="advancedSearchQuery.taxquery[searchCriterion.index].terms"
                            type="text"
                            :placeholder="$i18n.get('label_string_to_search_for')"
                            :aria-label="$i18n.get('label_string_to_search_for')"
                            @update:model-value="addValueToAdvancedSearchQuery($event, searchCriterion)" />
                    <b-input
                            v-else
                            type="text"
                            disabled
                            :aria-label="$i18n.get('label_disabled')" />
                </b-field>

                <div class="field">
                    <button
                            class="button button-remove-criterion is-pulled-right has-text-secondary"
                            type="button"
                            tabindex="0"
                            :aria-label="$i18n.get('remove_search_criterion')"
                            @click.prevent="removeCriterion(searchCriterion)"
                            @keydown.enter.prevent="removeCriterion(searchCriterion)"
                            @keydown.space.prevent="removeCriterion(searchCriterion)">
                        <span 
                                v-tooltip="{
                                    content: $i18n.get('remove_search_criterion'),
                                    autoHide: true,
                                    placement: 'auto-end'
                                }"
                                aria-hidden="true"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-cancel" />
                        </span>
                    </button>
                </div>

            </b-field>
        </transition-group>

        <!-- Add button -->
        <div class="add-link-advanced-search">
            <a 
                    ref="addCriterionButton"
                    role="button"
                    tabindex="0"
                    @click="addSearchCriteria"
                    @keydown.enter.prevent="addSearchCriteria"
                    @keydown.space.prevent="addSearchCriteria">
                <span 
                        aria-hidden="true"
                        class="icon">
                    <i class="has-text-secondary tainacan-icon tainacan-icon-add" />
                </span>
                {{ searchCriteria.length <= 0 ?
                    $i18n.get('add_one_search_criterion') :
                    $i18n.get('add_another_search_criterion')
                }}
            </a>
            <a
                    v-if="Object.keys(advancedSearchQuery.taxquery).length > 0 || Object.keys(advancedSearchQuery.metaquery).length > 0"
                    role="button"
                    tabindex="0"
                    @click="clearSearch();"
                    @keydown.enter.prevent="clearSearch();"
                    @keydown.space.prevent="clearSearch();">
                <span 
                        aria-hidden="true"
                        class="icon">
                    <i class="has-text-secondary tainacan-icon tainacan-icon-remove" />
                </span>
                {{ $i18n.get('label_remove_all_criteria') }}
            </a>
        </div>

        <!-- Clear and search button -->
        <div 
                style="margin-bottom: 0;"
                class="field is-grouped is-justify-content-flex-end">
            <p class="control">
                <button
                        type="reset"
                        class="button is-outlined"
                        @click="clearSearch(); $emit('close')">
                    {{ $i18n.get('label_close_search') }}
                </button>
            </p>
            <p class="control">
                <button
                        aria-controls="items-list-results"
                        type="submit"
                        :disabled="!hasUpdatedSearch"
                        class="button is-secondary">
                    {{ $i18n.get('apply') }}
                </button>
            </p>
        </div>

        <b-loading 
                v-model="isLoadingMetadata" 
                :is-full-page="false" />
        
        <section
                v-if="!isLoadingMetadata && metadataAsArray && metadataAsArray.length <= 0"
                class="field is-grouped-centered section">
            <div class="content has-text-dark has-text-centered">
                <p>
                    <span 
                            aria-hidden="true"
                            class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-36px tainacan-icon-metadata" />
                    </span>
                </p>
                <p>
                    {{ isRepositoryLevel ? $i18n.get('info_there_are_no_metadata_in_repository_level' ) : $i18n.get('info_there_are_no_metadata_to_search' ) }}
                </p>
            </div>
        </section>

    </form>
</template>

<script>
    import { mapActions } from 'vuex';
    import { dateInter } from '../../js/mixins.js';
    import moment from 'moment';
    import { IMaskDirective } from 'vue-imask';

    export default {
        name: "AdvancedSearch",
        directives: {
            imask: IMaskDirective
        },
        mixins: [ dateInter ],
        props: {
            isRepositoryLevel: false,
            collectionId: ''
        },
        emits: [
            'close',
            'performAdvancedSearch'
        ],
        data() {
            return {
                metaqueryOperatorsForInterval: {
                    '=': this.$i18n.get('is_equal_to'),
                    '!=': this.$i18n.get('is_not_equal_to'),
                    '>': this.$i18n.get('greater_than'),
                    '<': this.$i18n.get('less_than'),
                    '>=': this.$i18n.get('greater_than_or_equal_to'),
                    '<=': this.$i18n.get('less_than_or_equal_to'),
                    'NOT EXISTS': this.$i18n.get('has_no_value'),
                    'EXISTS': this.$i18n.get('has_value'),
                },
                metaqueryOperatorsRegular: {
                    '=': this.$i18n.get('is_equal_to'),
                    '!=': this.$i18n.get('is_not_equal_to'),
                    'LIKE': this.$i18n.get('contains'),
                    'NOT LIKE': this.$i18n.get('not_contains'),
                    'NOT EXISTS': this.$i18n.get('has_no_value'),
                    'EXISTS': this.$i18n.get('has_value'),
                },
                taxqueryOperators: {
                    'LIKE': this.$i18n.get('contains'),
                    'NOT LIKE': this.$i18n.get('not_contains'),
                    'NOT EXISTS': this.$i18n.get('has_no_value'),
                    'EXISTS': this.$i18n.get('has_value'),
                },
                searchCriteria: [],
                advancedSearchQuery: {
                    advancedSearch: true,
                    metaquery: [],
                    taxquery: [],
                    relation: 'AND'
                },
                isLoadingMetadata: false,
                metadataAsObject: {},
                metadataAsArray: [],
                metadataSearchCancel: undefined,
                hasUpdatedSearch: false
            }
        },
        computed: {
            definedCriteriaCount() {
                return this.searchCriteria.filter((searchCriterion) => searchCriterion.type !== undefined).length;
            },
            canChooseRelation() {
                return this.definedCriteriaCount >= 2;
            },
            /**
             * WP_Query only applies `relation` inside meta_query and inside tax_query; between the two
             * groups it is always AND. So OR is only offered when every criterion is of the same kind.
             * Computed from the same filtered (non-empty) criteria that performAdvancedSearch sends.
             */
            hasMixedCriteriaKinds() {
                const filteredQuery = this.getFilteredAdvancedSearchQuery();
                return filteredQuery.metaquery.length > 0 && filteredQuery.taxquery.length > 0;
            },
            effectiveRelation() {
                return this.hasMixedCriteriaKinds ? 'AND' : this.advancedSearchQuery.relation;
            }
        },
        mounted() {
            // Focus the first interactive element when component is mounted
            this.$nextTick(() => {
                if (this.$refs.advancedSearchForm) {
                    // Try to find and focus the first select element
                    const firstSelect = this.$refs.advancedSearchForm.querySelector('select:not([disabled])');
                    if (firstSelect) {
                        firstSelect.focus();
                    } else {
                        // Fallback: focus the form itself
                        this.$refs.advancedSearchForm.focus();
                    }
                }
            });

            this.isLoadingMetadata = true;

            this.fetchMetadata({
                collectionId: this.isRepositoryLevel ? false : this.collectionId,
                isRepositoryLevel: this.isRepositoryLevel,
                isContextEdit: false,
                includeDisabled: false,
                isAdvancedSearch: true,
                parent: 'any'
            }).then((resp) => {
                    resp.request
                        .then((metadata) => {

                            metadata = metadata.filter(metadatum => metadatum.allow_advanced_search !== 'no');

                            this.metadataAsArray = JSON.parse(JSON.stringify(metadata));

                            // Repository and term lists have no collection core metadatum. Offer synthetic ones from the plugin settings.
                            if (this.isRepositoryLevel) {
                                const coreMetadataAdvancedSearchDisabled = tainacan_plugin.tainacan_enable_core_metadata_on_advanced_search == false;
                                const descriptionAdvancedSearch = coreMetadataAdvancedSearchDisabled ? 'no' : (['default', 'yes', 'no'].indexOf(tainacan_plugin.repository_core_description_allow_advanced_search) >= 0 ? tainacan_plugin.repository_core_description_allow_advanced_search : 'yes');
                                const titleAdvancedSearch = coreMetadataAdvancedSearchDisabled ? 'no' : (['default', 'yes', 'no'].indexOf(tainacan_plugin.repository_core_title_allow_advanced_search) >= 0 ? tainacan_plugin.repository_core_title_allow_advanced_search : 'yes');

                                if (descriptionAdvancedSearch !== 'no') {
                                    this.metadataAsArray.unshift({
                                        collection_id: 'default',
                                        id: 'tainacan_core_description',
                                        metadata_section_id: 'default_section',
                                        metadata_type: 'Tainacan\\Metadata_Types\\Core_Description',
                                        metadata_type_object: {
                                            className: "Tainacan\\Metadata_Types\\Core_Description",
                                            component: "tainacan-textarea",
                                            core: true,
                                            errors: null,
                                            form_component: "tainacan-form-textarea",
                                            name: this.$i18n.get('label_core_description'),
                                        },
                                        metadata_type_options: [],
                                        name: this.$i18n.get('label_core_description'),
                                        parent: 0,
                                        repository_level: null,
                                        slug: 'tainacan-core-description',
                                        allow_advanced_search: descriptionAdvancedSearch
                                    });
                                }

                                if (titleAdvancedSearch !== 'no') {
                                    this.metadataAsArray.unshift({
                                        collection_id: 'default',
                                        id: 'tainacan_core_title',
                                        metadata_section_id: 'default_section',
                                        metadata_type: 'Tainacan\\Metadata_Types\\Core_Title',
                                        metadata_type_object: {
                                            className: "Tainacan\\Metadata_Types\\Core_Title",
                                            component: "tainacan-text",
                                            core: true,
                                            errors: null,
                                            form_component: "tainacan-form-text",
                                            name: this.$i18n.get('label_core_title'),
                                        },
                                        metadata_type_options: [],
                                        name: this.$i18n.get('label_core_title'),
                                        parent: 0,
                                        repository_level: null,
                                        slug: 'tainacan-core-title',
                                        allow_advanced_search: titleAdvancedSearch
                                    });
                                }
                            }

                            // We create and object keyed by IDs to easily match the query params,
                            // but keep an array version to use the order in the select
                            this.metadataAsArray.forEach(metadatum => {
                                this.metadataAsObject[metadatum.id] = metadatum;

                                if (
                                    metadatum.metadata_type_object &&
                                    metadatum.metadata_type_object.component === 'tainacan-compound' &&
                                    metadatum.metadata_type_options &&
                                    metadatum.metadata_type_options.children_objects
                                ) {
                                    metadatum.metadata_type_options.children_objects.forEach(childMetadatum => {
                                        this.metadataAsObject[childMetadatum.id] = childMetadatum;
                                    });
                                }
                            });

                            // Search Request Token for cancelling
                            this.metadataSearchCancel = resp.source;

                            // Pre-build empty criteria for metadata marked as "offer by default"
                            this.presetSearchCriteriaFromMetadata();

                            // Loads existing search query
                            this.buildAdvancedSearchQueryFromRoute();

                            this.isLoadingMetadata = false;
                        }).catch(() => {
                            this.isLoadingMetadata = false;
                        });
                })
                .catch(() => this.isLoadingMetadata = false);  
        },
        beforeUnmount() {
            // Cancels previous Request
            if (this.metadataSearchCancel != undefined)
                this.metadataSearchCancel.cancel('Metadata search Canceled.');

        },
        methods: {
            ...mapActions('metadata', [
                'fetchMetadata'
            ]),
            getComparators(searchCriterion) {
                if (searchCriterion.type == 'taxquery' && this.advancedSearchQuery.taxquery[searchCriterion.index]) {
                    return this.taxqueryOperators;
                } else if (searchCriterion.type == 'metaquery' && this.advancedSearchQuery.metaquery[searchCriterion.index]) {
                    const metadataType = this.getAdvancedSearchQueryCriterionMetadataType(searchCriterion.index);
                    if (metadataType == 'date' || metadataType == 'int' || metadataType == 'float')
                        return this.metaqueryOperatorsForInterval;
                    else
                        return this.metaqueryOperatorsRegular;
                }
            },
            buildAdvancedSearchQueryFromRoute() {

                const routeMetaquery = this.$route.query.metaquery;
                const routeTaxquery = this.$route.query.taxquery;
                const hasMetaQueries = routeMetaquery && Object.keys(routeMetaquery).length > 0;
                const hasTaxQueries = routeTaxquery && Object.keys(routeTaxquery).length > 0;

                // A nested group means a facet filter was applied on top of an OR advanced search,
                // e.g. (A OR B) AND C. Only the group's clauses belong to this form; the facet
                // clauses and the URL query are left untouched.
                const metaGroup = this.findNestedGroup(routeMetaquery);
                const taxGroup = this.findNestedGroup(routeTaxquery);
                const hasNestedGroup = metaGroup !== null || taxGroup !== null;

                // Route query wins over any pre-built criteria
                if (hasMetaQueries || hasTaxQueries) {
                    const relationSources = hasNestedGroup ? [ metaGroup, taxGroup ] : [ routeMetaquery, routeTaxquery ];

                    this.searchCriteria = [];
                    this.advancedSearchQuery = {
                        advancedSearch: true,
                        metaquery: [],
                        taxquery: [],
                        relation: relationSources.some((group) => this.getGroupRelation(group) === 'OR') ? 'OR' : 'AND'
                    };
                }

                if (hasMetaQueries) {

                    let metaquery = this.getGroupClauses(hasNestedGroup ? metaGroup : routeMetaquery);

                    for (let meta in metaquery) {
                        if (
                            Object.prototype.hasOwnProperty.call(this.metaqueryOperatorsRegular, metaquery[meta]['compare']) ||
                            Object.prototype.hasOwnProperty.call(this.metaqueryOperatorsForInterval, metaquery[meta]['compare'])
                        )
                            Object.assign( this.advancedSearchQuery.metaquery, { [`${meta}`]: metaquery[meta] });
                    }

                    let metakeys = Object.keys(this.advancedSearchQuery.metaquery);

                    let relationIndex = metakeys.findIndex((element) => element == 'relation');

                    if (relationIndex != -1)
                        metakeys.splice(relationIndex, 1);

                    for (let metakey of metakeys)
                        this.searchCriteria.push({ index: metakey, type: 'metaquery' });
                }

                if (hasTaxQueries) {
                    let taxquery = this.getGroupClauses(hasNestedGroup ? taxGroup : routeTaxquery);

                    for (let tax in taxquery) {
                        if ( Object.prototype.hasOwnProperty.call(this.taxqueryOperators, taxquery[tax]['operator']) )
                            Object.assign( this.advancedSearchQuery.taxquery, { [`${tax}`]: taxquery[tax] });
                    }

                    let taxkeys = Object.keys(this.advancedSearchQuery.taxquery);

                    let relationIndex = taxkeys.findIndex((element) => element == 'relation');

                    if (relationIndex != -1) 
                        taxkeys.splice(relationIndex, 1);

                    for (let taxkey of taxkeys)
                        this.searchCriteria.push({ index: taxkey, type: 'taxquery' });
                }

                // If we're coming from a URL advanced search, execute it.
                // If criteria were already pre-built, keep them without searching
                // (empty values must not hit the query). Otherwise, create an empty row.
                if (hasMetaQueries || hasTaxQueries) {
                    this.$eventBusSearch.updateStoreFromURL();

                    // A query with a nested group is already applied and would be rewritten as a flat
                    // one if executed from the form, so it only runs again when the user submits it.
                    if (!hasNestedGroup)
                        this.performAdvancedSearch();
                } else if (!this.searchCriteria.length) {
                    this.addSearchCriteria();
                }
            },
            /**
             * Returns the AND/OR relation of a query group (top-level metaquery/taxquery or a nested group).
             */
            getGroupRelation(group) {
                if (group && typeof group === 'object' && typeof group.relation === 'string' && group.relation.toUpperCase() === 'OR')
                    return 'OR';

                return 'AND';
            },
            /**
             * Returns the first nested group found inside a top-level metaquery/taxquery, or null.
             */
            findNestedGroup(group) {
                if (!group || typeof group !== 'object')
                    return null;

                for (const key in group) {
                    if (key !== 'relation' && this.isQueryGroup(group[key]))
                        return group[key];
                }
                return null;
            },
            /**
             * A nested group is an entry without its own key/taxonomy whose entries are clauses,
             * e.g. { relation: 'OR', 0: { key, compare, value }, 1: { ... } }.
             */
            isQueryGroup(clause) {
                return (
                    clause &&
                    typeof clause === 'object' &&
                    clause.key === undefined &&
                    clause.taxonomy === undefined &&
                    Object.keys(clause).some((key) => key !== 'relation' && clause[key] && typeof clause[key] === 'object')
                );
            },
            /**
             * Returns the plain clauses of a group (top-level or nested) as an object keyed by
             * position, skipping the `relation` entry and any nested group.
             */
            getGroupClauses(group) {
                const clauses = {};
                let position = 0;

                if (!group || typeof group !== 'object')
                    return clauses;

                for (const key in group) {
                    const clause = group[key];

                    if (key === 'relation' || !clause || typeof clause !== 'object' || this.isQueryGroup(clause))
                        continue;

                    clauses[position++] = clause;
                }
                return clauses;
            },
            setRelation(relation) {
                if (relation !== 'AND' && relation !== 'OR')
                    return;

                this.advancedSearchQuery.relation = relation;
                this.hasUpdatedSearch = true;
            },
            /**
             * Pre-builds one criterion row per metadatum marked as
             * allow_advanced_search === 'default', with the default comparator and no value.
             */
            presetSearchCriteriaFromMetadata() {
                const addCriterionForMetadatum = (metadatum) => {
                    if (metadatum.allow_advanced_search !== 'default')
                        return;

                    const component = metadatum.metadata_type_object
                        ? metadatum.metadata_type_object.component
                        : '';

                    if (
                        component === 'tainacan-user' ||
                        component === 'tainacan-geocoordinate' ||
                        component === 'tainacan-relationship' ||
                        component === 'tainacan-compound'
                    )
                        return;

                    const primitiveType = metadatum.metadata_type_object
                        ? metadatum.metadata_type_object.primitive_type
                        : '';
                    const taxonomy = metadatum.metadata_type_options
                        ? metadatum.metadata_type_options.taxonomy
                        : '';

                    if (primitiveType === 'term') {
                        const index = this.advancedSearchQuery.taxquery.length;
                        this.advancedSearchQuery.taxquery.push({
                            key: metadatum.id,
                            taxonomy: taxonomy,
                            operator: 'LIKE'
                        });
                        this.searchCriteria.push({ index: index, type: 'taxquery' });
                    } else {
                        const index = this.advancedSearchQuery.metaquery.length;
                        const compare = (
                            primitiveType != 'date' &&
                            primitiveType != 'int' &&
                            primitiveType != 'float'
                        ) ? 'LIKE' : '=';

                        this.advancedSearchQuery.metaquery.push({
                            key: metadatum.id,
                            compare: compare
                        });
                        this.searchCriteria.push({ index: index, type: 'metaquery' });
                    }
                };

                this.metadataAsArray.forEach(metadatum => {
                    if (
                        metadatum.metadata_type_object &&
                        metadatum.metadata_type_object.component === 'tainacan-compound' &&
                        metadatum.metadata_type_options &&
                        metadatum.metadata_type_options.children_objects
                    ) {
                        metadatum.metadata_type_options.children_objects.forEach(childMetadatum => {
                            addCriterionForMetadatum(childMetadatum);
                        });
                    } else if (metadatum.parent <= 0) {
                        addCriterionForMetadatum(metadatum);
                    }
                });
            },
            addSearchCriteria() {
                let aleatoryKey = Math.floor(Math.random() * (1000 - 2 + 1)) + 2;

                let existingKeyIndex = this.searchCriteria.findIndex((element) => element.index == aleatoryKey);

                if (existingKeyIndex < 0)
                    this.searchCriteria.push({ index: aleatoryKey, type: undefined });
                else
                    this.addSearchCriteria();
            },
            removeCriterion(searchCriterion) {
                
                // First, check if this criterion row is defined
                if (!searchCriterion.type) {
                    let searchCriterionIndex = this.searchCriteria.findIndex((element) => element.index == searchCriterion.index);
                    if (searchCriterionIndex >= 0)
                        this.searchCriteria.splice(searchCriterionIndex, 1);

                // If it was defined, then we need to update advancedSearchQuery properly when removing
                } else {
                    let searchCriterionIndex = this.searchCriteria.findIndex((element) => element.index == searchCriterion.index && element.type == searchCriterion.type);
                
                    if (searchCriterionIndex >= 0) {
                        
                        this.advancedSearchQuery[searchCriterion.type].splice(searchCriterion.index, 1);
                        this.searchCriteria.splice(searchCriterionIndex, 1);

                        for (let queryIndex = 0; queryIndex < this.advancedSearchQuery.metaquery.length; queryIndex++) { 
                            let isCriterionIndexUpdated = false;
                            let expectedIndex = queryIndex;
                            while(!isCriterionIndexUpdated && expectedIndex < this.searchCriteria.length) {
                                if (this.searchCriteria[expectedIndex] && this.searchCriteria[expectedIndex].type == 'metaquery') {
                                    Object.assign(this.searchCriteria[expectedIndex], { 'index': queryIndex });
                                    isCriterionIndexUpdated = true;
                                } else {
                                    expectedIndex++;
                                }
                            }
                        }
                        for (let queryIndex = 0; queryIndex < this.advancedSearchQuery.taxquery.length; queryIndex++) {
                            let isCriterionIndexUpdated = false;
                            let expectedIndex = queryIndex;
                            while(!isCriterionIndexUpdated && expectedIndex < this.searchCriteria.length) {
                                if (this.searchCriteria[expectedIndex] && this.searchCriteria[expectedIndex].type == 'taxquery') {
                                    Object.assign(this.searchCriteria[expectedIndex], { 'index': queryIndex });
                                    isCriterionIndexUpdated = true;
                                } else {
                                    expectedIndex++;
                                }
                            }
                        }
                    }
                }

                this.hasUpdatedSearch = true;
            },
            clearSearch() {
                this.searchCriteria = [];
                this.advancedSearchQuery = {
                    advancedSearch: true,
                    metaquery: [],
                    taxquery: [],
                    relation: 'AND'
                };
                this.hasUpdatedSearch = true;
            },
            convertDateToMatchInDB(dateValue) {
                return moment(dateValue,  this.dateFormat).toISOString().split('T')[0];
            },
            parseValidDateToNavigatorLanguage(date) {
                if (date && date.length === this.dateMask.length)
                    return (
                        moment(date, this.dateFormat).toISOString(true) &&
                        moment(date, this.dateFormat).toISOString(true).split('T') &&
                        moment(date, this.dateFormat).toISOString(true).split('T')[0]
                    ) ? this.parseDateToNavigatorLanguage(moment(date, this.dateFormat).toISOString(true).split('T')[0]) : this.parseDateToNavigatorLanguage(date);
                else
                    return date;
            },
            addMetadatumToAdvancedSearchQuery({ metadatumId, type, taxonomy }, searchCriterion, index) {
                if (!metadatumId)
                    return;
                    
                if (type === 'term') {
                    
                    // Convert fake placeholder criterion row to a tax row
                    let totalOfTaxCriteria = this.searchCriteria.reduce((counter, { type }) => type === 'taxquery' ? counter + 1 : counter, 0);
                    Object.assign(this.searchCriteria[index], { 'type': 'taxquery' });
                    Object.assign(this.searchCriteria[index], { 'index': totalOfTaxCriteria });

                    // Was selected a taxonomy criteria      
                    this.advancedSearchQuery.taxquery.push({
                        key: metadatumId,
                        taxonomy: taxonomy,
                        operator: 'LIKE'
                    });
                } else {

                    // Convert fake placeholder criterion row to a meta row
                    let totalOfMetaCriteria = this.searchCriteria.reduce((counter, { type }) => type === 'metaquery' ? counter + 1 : counter, 0);
                    Object.assign(this.searchCriteria[index], { 'type': 'metaquery' });
                    Object.assign(this.searchCriteria[index], { 'index': totalOfMetaCriteria });

                    // Was selected a metadatum criteria
                    if (type != 'date' && type != 'int' && type != 'float') {
                        this.advancedSearchQuery.metaquery.push({
                            key: metadatumId,
                            compare: 'LIKE'
                        });
                    } else {
                        this.advancedSearchQuery.metaquery.push({
                            key: metadatumId,
                            compare: '='
                        });
                    }
                }
            },
            addValueToAdvancedSearchQuery(value, searchCriterion) {
                if (!value)
                    return;

                if (searchCriterion.type == 'metaquery' && this.advancedSearchQuery.metaquery[searchCriterion.index])
                    Object.assign(this.advancedSearchQuery.metaquery[searchCriterion.index], { 'value': value });
                else if (searchCriterion.type == 'taxquery' && this.advancedSearchQuery.taxquery[searchCriterion.index])
                    Object.assign(this.advancedSearchQuery.taxquery[searchCriterion.index], { 'terms': value });

                this.hasUpdatedSearch = true;
            },
            addComparatorToAdvancedSearchQuery(comparator, searchCriterion) {
                if (!comparator)
                    return;

                if (searchCriterion.type == 'metaquery' && this.advancedSearchQuery.metaquery[searchCriterion.index]) {
                    Object.assign(this.advancedSearchQuery.metaquery[searchCriterion.index], { 'compare': comparator });

                    if (comparator == 'NOT EXISTS' || comparator == 'EXISTS')
                        delete this.advancedSearchQuery.metaquery[searchCriterion.index].value;
                    
                } else if (searchCriterion.type == 'taxquery' && this.advancedSearchQuery.taxquery[searchCriterion.index]) {
                    Object.assign(this.advancedSearchQuery.taxquery[searchCriterion.index], { 'operator': comparator });

                    if (comparator == 'NOT EXISTS' || comparator == 'EXISTS')
                        delete this.advancedSearchQuery.taxquery[searchCriterion.index].terms;
                }

                this.hasUpdatedSearch = true;
            },
            /**
             * Returns a copy of the advanced search query with empty criteria removed.
             * Criteria whose comparator does not expect a value (EXISTS / NOT EXISTS)
             * are kept. The form state is left untouched so preset empty rows remain.
             */
            getFilteredAdvancedSearchQuery() {
                const isEmptyValue = (value) => {
                    return value === undefined || value === null || value === '';
                };

                const metaquery = Array.isArray(this.advancedSearchQuery.metaquery)
                    ? this.advancedSearchQuery.metaquery.filter((criterion) => {
                        if (!criterion || criterion.key === undefined)
                            return false;

                        if (criterion.compare === 'EXISTS' || criterion.compare === 'NOT EXISTS')
                            return true;

                        return !isEmptyValue(criterion.value);
                    }).map((criterion) => Object.assign({}, criterion))
                    : [];

                const taxquery = Array.isArray(this.advancedSearchQuery.taxquery)
                    ? this.advancedSearchQuery.taxquery.filter((criterion) => {
                        if (!criterion || criterion.key === undefined)
                            return false;

                        if (criterion.operator === 'EXISTS' || criterion.operator === 'NOT EXISTS')
                            return true;

                        return !isEmptyValue(criterion.terms);
                    }).map((criterion) => Object.assign({}, criterion))
                    : [];

                return {
                    advancedSearch: true,
                    metaquery: metaquery,
                    taxquery: taxquery
                };
            },
            performAdvancedSearch() {

                // Work on a filtered copy so empty preset criteria stay in the form
                // but are not sent to the API (see tainacan/tainacan#1071).
                const advancedSearchQuery = this.getFilteredAdvancedSearchQuery();

                // WP_Query only honors `relation` inside meta_query and inside tax_query (AND is its
                // default and the two groups are always combined with AND), so the relation is only
                // written into each group, and only when it is OR. Keeping the groups as plain arrays
                // in the AND case preserves the URL shape the facet filters expect.
                const relation = this.effectiveRelation;

                if ( Object.keys(advancedSearchQuery.taxquery).length > 1 && relation === 'OR' )
                    Object.assign(advancedSearchQuery.taxquery, { 'relation': 'OR' });

                // Convert date values to a format (ISO_8601) that will match in database
                if ( Object.keys(advancedSearchQuery.metaquery).length > 0 ) {

                    for (let metaquery in advancedSearchQuery.metaquery) {
                        if (
                            advancedSearchQuery.metaquery[metaquery] &&
                            advancedSearchQuery.metaquery[metaquery].key &&
                            this.metadataAsObject[advancedSearchQuery.metaquery[metaquery].key] &&
                            this.metadataAsObject[advancedSearchQuery.metaquery[metaquery].key].metadata_type_object &&
                            this.metadataAsObject[advancedSearchQuery.metaquery[metaquery].key].metadata_type_object.primitive_type == 'date'
                        ) {
                            let value = advancedSearchQuery.metaquery[metaquery].value;
                            
                            if (value != null && value != undefined && value.includes('/'))
                                Object.assign(advancedSearchQuery.metaquery[metaquery], { 'value': this.convertDateToMatchInDB(value) });
                        }
                    }
                }

                if ( Object.keys(advancedSearchQuery.metaquery).length > 1 && relation === 'OR' )
                    Object.assign(advancedSearchQuery.metaquery, { 'relation': 'OR' });

                if ( Object.keys(advancedSearchQuery.metaquery).length > 0 ) {

                    for (let metaquery in advancedSearchQuery.metaquery) {
                        if (
                            advancedSearchQuery.metaquery[metaquery] &&
                            advancedSearchQuery.metaquery[metaquery].key &&
                            this.metadataAsObject[advancedSearchQuery.metaquery[metaquery].key] &&
                            this.metadataAsObject[advancedSearchQuery.metaquery[metaquery].key].metadata_type_object &&
                            this.metadataAsObject[advancedSearchQuery.metaquery[metaquery].key].metadata_type_object.primitive_type == 'date'
                        ) {
                            let value = advancedSearchQuery.metaquery[metaquery].value;
                            
                            if (value != null && value != undefined && value.includes('-'))
                                Object.assign(advancedSearchQuery.metaquery[metaquery], { 'value': this.parseValidDateToNavigatorLanguage(value) });
                        }
                    }
                }

                this.hasUpdatedSearch = false;
                this.$store.dispatch('search/setPage', 1);
                this.$eventBusSearch.performAdvancedSearch(advancedSearchQuery);
            },
            getAdvancedSearchQueryCriterionMetadataType(searchCriterion) {
                if (this.advancedSearchQuery.metaquery[searchCriterion] &&
                    this.advancedSearchQuery.metaquery[searchCriterion].key &&
                    this.metadataAsObject[this.advancedSearchQuery.metaquery[searchCriterion].key] &&
                    this.metadataAsObject[this.advancedSearchQuery.metaquery[searchCriterion].key].metadata_type_object
                ) {
                    return this.metadataAsObject[this.advancedSearchQuery.metaquery[searchCriterion].key].metadata_type_object.primitive_type
                }

                return '';
            }
        }
    }
</script>

<style lang="scss">

    #advanced-search-container {
        width: calc(100% - (2 * var(--tainacan-one-column)));
        margin: 0 var(--tainacan-one-column) 0.875em;
        background: var(--tainacan-background-color);
        border: 1px solid var(--tainacan-input-border-color);
        border-radius: var(--tainacan-dropdownmenu-border-radius, 0px);
        transition: height 0.2s ease;

        .tnc-advanced-search-container {
            position: relative;
            padding: 1.25em;
            border-radius: var(--tainacan-dropdownmenu-border-radius, 0px);

            h3 {
                font-size: 1em !important;
                padding-top: 0 !important;
                color: var(--tainacan-heading-color) !important;
                margin-bottom: 1em !important;
            }
            .tainacan-form {
                margin-bottom: 1.125em !important;
            }
            .advanced-search-relation-field {
                margin-bottom: 1.125em;

                .label {
                    display: inline-flex;
                    align-items: center;
                    font-size: 0.875em;
                    font-weight: normal;
                    margin-bottom: 0.375em;
                }
                .advanced-search-relation-options {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 0.25em;
                }
                .help {
                    margin-top: 0.375em;
                }
            }
            .column {
                padding: 0;
            }
            .control {
                font-size: 1em;
                margin-bottom: 0px !important;

                .select{
                    width: 100% !important;
                    select{
                        width: 100% !important;
                    }
                }
            }
            .add-link-advanced-search {
                margin-top: 0.75rem;
            }
            .add-link-advanced-search a {
                font-size: 0.8125em;
                display: inline-flex;
                align-items: center;
                margin-right: 1em;
            }

            .button-remove-criterion {
                color: var(--tainacan-input-color);
                background: transparent;
                border: none;
                border-radius: var(--tainacan-button-border-radius, 4px);
            }
        }
        @media screen and (max-width: 768px) {
            border-left: none;
            border-right: none;

            .tnc-advanced-search-container {
                padding-right: 0;
                padding-left: 0;
            }
            .is-12>.columns {
                flex-wrap: wrap;
            }
            .is-two-thirds {
                order: 3;
                flex-basis: 100%;
            }
        }
    }

</style>