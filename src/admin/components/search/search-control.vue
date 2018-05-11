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
                <b-dropdown-item disabled>{{ $i18n.get('add_items_bulk') + ' (Not ready)' }}
                </b-dropdown-item>
                <b-dropdown-item disabled>{{ $i18n.get('add_items_external_source') + ' (Not ready)' }}<br><small class="is-small">{{ $i18n.get() }}</small></b-dropdown-item>
            </b-dropdown>

        </div>
        <div class="header-item">
            <b-dropdown class="show">
                <button
                        class="button is-white"
                        slot="trigger">
                    <span>{{ $i18n.get('label_table_fields') }}</span>
                    <b-icon icon="menu-down"/>
                </button>
                <b-dropdown-item
                        v-for="(column, index) in tableFields"
                        :key="index"
                        class="control"
                        custom>
                    <b-checkbox
                            v-model="column.display"
                            :native-value="column.field">
                        {{ column.name }}
                    </b-checkbox>
                </b-dropdown-item>
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
                            field.id === 'date' || (
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
                prefTableFields: []
            }
        },
        props: {
            collectionId: Number,
            isRepositoryLevel: false,
            tableFields: Array,
            isOnTheme: false     
        },
        computed: {
            orderBy() {
                return this.getOrderBy();
            },
            order() {
                return this.getOrder();
            }
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
</style>


