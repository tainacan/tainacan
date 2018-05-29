<template>
    <div class="table-container">
        <div class="table-wrapper">
            <table 
                    class="tainacan-table">
                <thead>
                    <tr>
                        <!-- Displayed Fields -->
                        <th 
                                v-for="(column, index) in tableFields"
                                :key="index"
                                v-if="column.display"
                                class="column-default-width"
                                :class="{
                                        'thumbnail-cell': column.field == 'row_thumbnail', 
                                        'column-small-width' : column.field_type_object != undefined ? (column.field_type_object.className == 'Tainacan\\Field_Types\\Date' || column.field_type_object.className == 'Tainacan\\Field_Types\\Numeric') : false,
                                        'column-medium-width' : column.field_type_object != undefined ? (column.field_type_object.className == 'Tainacan\\Field_Types\\Selectbox' || column.field_type_object.className == 'Tainacan\\Field_Types\\Category' || column.field_type_object.className == 'Tainacan\\Field_Types\\Compound') : false,
                                        'column-large-width' : column.field_type_object != undefined ? (column.field_type_object.className == 'Tainacan\\Field_Types\\Textarea') : false,
                                }"
                                :custom-key="column.slug">
                            <div class="th-wrap">{{ column.name }}</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr     
                            :key="index"
                            v-for="(item, index) of items">

                        <!-- Item Displayed Metadata -->
                        <td 
                                :key="index"    
                                v-for="(column, index) in tableFields"
                                v-if="column.display"
                                :label="column.name" 
                                :aria-label="(column.field != 'row_thumbnail' && column.field != 'row_creation' && column.field != 'row_author')
                                             ? column.name + '' + (item.metadata ? item.metadata[column.slug].value_as_string : '') : ''"
                                class="column-default-width"
                                :class="{
                                        'thumbnail-cell': column.field == 'row_thumbnail',
                                        'column-main-content' : column.field_type_object != undefined ? (column.field_type_object.related_mapped_prop == 'title') : false,
                                        'column-needed-width column-align-right' : column.field_type_object != undefined ? (column.field_type_object.className == 'Tainacan\\Field_Types\\Numeric') : false,
                                        'column-small-width' : column.field_type_object != undefined ? (column.field_type_object.className == 'Tainacan\\Field_Types\\Date' || column.field_type_object.className == 'Tainacan\\Field_Types\\Numeric') : false,
                                        'column-medium-width' : column.field_type_object != undefined ? (column.field_type_object.className == 'Tainacan\\Field_Types\\Selectbox' || column.field_type_object.className == 'Tainacan\\Field_Types\\Category' || column.field_type_object.className == 'Tainacan\\Field_Types\\Compound') : false,
                                        'column-large-width' : column.field_type_object != undefined ? (column.field_type_object.className == 'Tainacan\\Field_Types\\Textarea') : false,
                                }"
                                @click="goToItemPage(item)">

                            <!-- <data-and-tooltip
                                    v-if="column.field !== 'row_thumbnail' &&
                                            column.field !== 'row_actions' &&
                                            column.field !== 'row_creation'"
                                    :data="renderMetadata( item.metadata[column.slug] )"/> -->
                            <p
                                    v-tooltip="{
                                        content: renderMetadata( item.metadata[column.slug] ),
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    v-if="item.metadata != undefined &&
                                          column.field !== 'row_thumbnail' &&
                                          column.field !== 'row_actions' &&
                                          column.field !== 'row_creation' &&
                                          column.field !== 'row_author'"
                                    v-html="renderMetadata( item.metadata[column.slug] )"/>

                            <span v-if="column.field == 'row_thumbnail'">
                                <img 
                                        class="table-thumb" 
                                        :src="item[column.slug].thumb">
                            </span> 
                            <p 
                                    v-tooltip="{
                                        content: item[column.slug],
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    v-if="column.field == 'row_author' || column.field == 'row_creation'">
                                    {{ item[column.slug] }}
                            </p>

                        </td>

                    </tr>
                </tbody>
            </table>
        </div> 
    </div>
</template>

<script>
export default {
    name: 'ItemsList',
    props: {
        collectionId: Number,
        tableFields: Array,
        items: Array,
        isLoading: false,
        isOnTrash: false
    },
    methods: {
        goToItemPage(item) {
            window.location.href = item.url;   
        },
        renderMetadata(metadata) {

            if (!metadata) {
                return '';
            } else if (metadata.date_i18n) {
                return metadata.date_i18n;
            } else {
                return metadata.value_as_html;
            }
        }
    }
}
</script>

<style>

</style>


