<template>
    <section>
        <button
                type="button"
                @click="clearSearch"
                class="button field is-info">
            <b-icon icon="magnify"/>
            <span>{{ $i18n.get('label_relationship_new_search') }}</span>
        </button>

        <b-tabs>
            <b-tab-item :label="$i18n.get('label_relationship_items_found')">
                <b-table
                        :data="data"
                        :columns="columns"
                        :checked-rows.sync="checkedRows"
                        :is-row-checkable="(row) => row.id !== 3"
                        checkable>

                    <template slot="bottom-left">
                        <b>Total</b>: {{ checkedRows.length }}
                    </template>
                </b-table>
            </b-tab-item>

            <b-tab-item :label="$i18n.get('label_selected')">
                <button
                        class="button field is-danger"
                        @click="checkedRows = []"
                        :disabled="!checkedRows.length">
                    <b-icon icon="close"/>
                    <span>{{ $i18n.get('label_clean') }}</span>
                </button>
                <pre>{{ checkedRows }}</pre>
            </b-tab-item>
        </b-tabs>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                data: this.items,
                checkedRows: [],
                columns: [
                    {
                        field: 'title',
                        label: this.$i18n.get('label_title'),
                    },
                    {
                        field: 'description',
                        label: this.$i18n.get('label_description'),
                    }
                ]
            }
        },
        watch: {
            checkedRows( val ){
                this.checkedRows = val;
                this.$emit('input', val );
            }
        },
        props: {
            collectionId: Number,
            totalItems: 0,
            page: 1,
            itemsPerPage: 12,
            items: Array,
        },
        methods: {
            clearSearch(){
                this.$emit('clearSearch', true );
            }
        }
    }
</script>