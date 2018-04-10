<template>
    <span>
        <div class="header-item">
            <b-dropdown>
                <button 
                        class="button" 
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
                            v-model="column.visible" 
                            :native-value="column.slug">
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
                        v-for="(field, index) in tableFields"
                        v-if="field.id != undefined && field.field_type_object.related_mapped_prop != 'description'"
                        :value="field"
                        :key="index">
                        {{ field.name }}
                    </option>
                </b-select>
                <button 
                        class="button is-small"
                        @click="onChangeOrder()">
                    <b-icon :icon="order == 'ASC' ? 'sort-ascending' : 'sort-descending'"/>
                </button>
            </b-field>
        </div>
    </span>
</template>

<script>
import { mapGetters } from 'vuex';
import { eventSearchBus } from '../../../js/event-search-bus';

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
        tableFields: Array   
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
            eventSearchBus.setOrderBy(field);
        },
        onChangeOrder() {
            this.order == 'DESC' ? eventSearchBus.setOrder('ASC') : eventSearchBus.setOrder('DESC');
        }
    }
}
</script>

<style>
    .header-item {
        display: inline-block;
        padding-right: 8em;
    }
</style>


