<template>
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
                        @input="onChangeTableFields(column)"
                        v-model="column.visible" 
                        :native-value="column.field">
                    {{ column.label }}
                </b-checkbox>
            </b-dropdown-item>
        </b-dropdown>
    </div>
</template>

<script>
import { mapActions } from 'vuex';

export default {
    name: 'SearchControl',
    props: {
        collectionId: Number,
        isRepositoryLevel: false,
        tableFields: Array,
        prefTableFields: Array,
    },
    methods: {
        ...mapActions('fields', [
            'fetchFields'
        ]),
        onChangeTableFields(field) {
            // let prevValue = this.prefTableFields;
            // let index = this.prefTableFields.findIndex(alteredField => alteredField.slug === field.slug);
            // if (index >= 0) {
            //     prevValue[index].visible = this.prefTableFields[index].visible ? false : true;
            // }

            // for (let currentField of this.prefTableFields)
            //     this.$console.log(currentField.slug, currentField.visible);

            // for (let oldField of prevValue)
            //     this.$console.log(oldField.slug, oldField.visible);

            // this.$userPrefs.set('table_columns_' + this.collectionId, this.prefTableFields, prevValue);
        }
    },
    mounted() {
        this.fetchFields({ collectionId: this.collectionId, isRepositoryLevel: this.isRepositoryLevel, isContextEdit: false }).then((res) => {
            let rawFields = res;
            this.tableFields.push({ label: this.$i18n.get('label_thumbnail'), field: 'featured_image', slug: 'featured_image', visible: true });
            for (let field of rawFields) {
                this.tableFields.push(
                    { label: field.name, field: field.description, slug: field.slug,  visible: true }
                );
            }
            this.tableFields.push({ label: this.$i18n.get('label_actions'), field: 'row_actions', slug: 'actions', visible: true });
            this.prefTableFields = this.tableFields;
            // this.$userPrefs.get('table_columns_' + this.collectionId)
            //     .then((value) => {
            //         this.prefTableFields = value;
            //     })
            //     .catch((error) => {
            //         this.$userPrefs.set('table_columns_' + this.collectionId, this.prefTableFields, null);
            //     });

        }).catch();
    }
}
</script>

