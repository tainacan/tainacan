<template>
    <span>
        <div 
                class="header-item"
                v-if="!isOnTheme">
            <b-dropdown id="item-creation-options-dropdown">
                <button
                        class="button is-secondary"
                        slot="trigger">
                    <span>{{ $i18n.getFrom('items','add_new') }}</span>
                    <b-icon icon="menu-down"/>
                </button>

                <b-dropdown-item>
                    <router-link
                            id="a-create-item"
                            tag="div"
                            :to="{ path: $routerHelper.getNewItemPath(collectionId) }">
                        {{ $i18n.get('add_one_item') }}
                    </router-link>
                </b-dropdown-item>
                <b-dropdown-item disabled>
                    {{ $i18n.get('add_items_bulk') + ' (Not ready)' }}
                </b-dropdown-item>
                <b-dropdown-item disabled>
                    {{ $i18n.get('add_items_external_source') + ' (Not ready)' }}
                </b-dropdown-item>
            </b-dropdown>

        </div>
        <div class="header-item">
            <b-dropdown
                    ref="displayedFieldsDropdown"
                    class="show">
                <button
                        class="button is-white"
                        slot="trigger">
                    <span>{{ $i18n.get('label_table_fields') }}</span>
                    <b-icon icon="menu-down"/>
                </button>
                <div class="metadata-options-container">
                <b-dropdown-item
                        v-for="(column, index) in localTableFields"
                        :key="index"
                        class="control"
                        custom>
                    <b-checkbox
                            v-model="column.display"
                            :native-value="column.display">
                        {{ column.name }}
                    </b-checkbox>
                </b-dropdown-item>   
                </div>
                <div class="dropdown-item-apply">
                    <button 
                            @click="onChangeDisplayedFields()"
                            class="button is-success">
                        {{ $i18n.get('label_apply_changes') }}
                    </button>
                </div>  
            </b-dropdown>
        </div>
        <div class="header-item">
            <b-field>
                <b-select
                        @input="onChangeOrderBy($event)"
                        :placeholder="$i18n.get('label_sorting')">
                    <option
                            v-for="field in tableFields"
                            v-if="
                                field.id === 'creation_date' || 
                                field.id === 'author_name' || (
                                    field.id !== undefined &&
                                    field.field_type_object.related_mapped_prop !== 'description' &&
                                    field.field_type_object.primitive_type !== 'term' &&
                                    field.field_type_object.primitive_type !== 'item' &&
                                    field.field_type_object.primitive_type !== 'compound'
                            )"
                            :value="field"
                            :key="field.id">
                        {{ field.name }}
                    </option>
                </b-select>
                <button
                        class="button is-white is-small"
                        @click="onChangeOrder()">
                    <b-icon :icon="order === 'ASC' ? 'sort-ascending' : 'sort-descending'"/>
                </button>
            </b-field>
        </div>
    </span>
</template>

<script>
    import {mapGetters} from 'vuex';

    export default {
        name: 'SearchControl',
        data() {
            return {
                prefTableFields: [],
                localTableFields: []
            }
        },
        props: {
            collectionId: Number,
            isRepositoryLevel: false,
            tableFields: Array,
            isOnTheme: false     
        },
        watch: {
            tableFields() {
                this.localTableFields = JSON.parse(JSON.stringify(this.tableFields));
            }
        },
        computed: {
            orderBy() {
                return this.getOrderBy();
            },
            order() {
                return this.getOrder();
            }
        },
        mounted() {
            this.localTableFields = JSON.parse(JSON.stringify(this.tableFields));
        },
        methods: {
            ...mapGetters('search', [
                'getOrderBy',
                'getOrder'
            ]),
            onChangeOrderBy(field) {
                this.$eventBusSearch.setOrderBy(field);
            },
            onChangeOrder() {
                this.order == 'DESC' ? this.$eventBusSearch.setOrder('ASC') : this.$eventBusSearch.setOrder('DESC');
            },
            onChangeDisplayedFields() {
                let fetchOnlyFieldIds = [];

                for (let i = 0; i < this.localTableFields.length; i++) {

                    this.tableFields[i].display = this.localTableFields[i].display;
                    if (this.tableFields[i].id != undefined) {
                        if (this.tableFields[i].display) {
                            fetchOnlyFieldIds.push(this.tableFields[i].id);                      
                        }
                    }
                }
                this.$eventBusSearch.addFetchOnly({
                    '0': 'thumbnail',
                    'meta': fetchOnlyFieldIds,
                    '1': 'creation_date',
                    '2': 'author_name'
                });
                this.$refs.displayedFieldsDropdown.toggle();
            }
        }
    }
</script>

<style>
    .header-item {
        display: inline-block;
    }
    .header-item .field {
        align-items: center;
    }
    #item-creation-options-dropdown {
        margin-right: 80px;
    }
    .header-item .dropdown-menu {
        display: block;
    }
    .metadata-options-container {
        max-height: 240px;
        overflow: auto;
    }
    .dropdown-content {
        padding: 0;
    }
    .dropdown-item-apply {
        width: 100%;
        border-top: 1px solid #efefef;
        padding: 8px 12px;
        text-align: right;
    }
    .dropdown-item-apply .button {
        width: 100%;
    }
</style>


