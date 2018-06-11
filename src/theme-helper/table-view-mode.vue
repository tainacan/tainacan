<template>
    <div class="table-container">
        <div class="table-wrapper">
            <table class="tainacan-table">
                <thead>
                    <tr>
                        <!-- Displayed Metadata -->
                        <th 
                                v-for="(column, index) in tableMetadata"
                                :key="index"
                                v-if="column.display"
                                class="column-default-width"
                                :class="{
                                        'thumbnail-cell': column.metadatum == 'row_thumbnail', 
                                        'column-small-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.className == 'Tainacan\\Metadata_Types\\Date' || column.metadata_type_object.className == 'Tainacan\\Metadata_Types\\Numeric') : false,
                                        'column-medium-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.className == 'Tainacan\\Metadata_Types\\Selectbox' || column.metadata_type_object.className == 'Tainacan\\Metadata_Types\\Category' || column.metadata_type_object.className == 'Tainacan\\Metadata_Types\\Compound') : false,
                                        'column-large-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.className == 'Tainacan\\Metadata_Types\\Textarea') : false,
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
                                v-for="(column, index) in tableMetadata"
                                v-if="column.display"
                                :label="column.name" 
                                :aria-label="(column.metadatum != 'row_thumbnail' && column.metadatum != 'row_creation' && column.metadatum != 'row_author')
                                             ? column.name + '' + (item.metadata ? item.metadata[column.slug].value_as_string : '') : ''"
                                class="column-default-width"
                                :class="{
                                        'thumbnail-cell': column.metadatum == 'row_thumbnail',
                                        'column-main-content' : column.metadata_type_object != undefined ? (column.metadata_type_object.related_mapped_prop == 'title') : false,
                                        'column-needed-width column-align-right' : column.metadata_type_object != undefined ? (column.metadata_type_object.className == 'Tainacan\\Metadata_Types\\Numeric') : false,
                                        'column-small-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.className == 'Tainacan\\Metadata_Types\\Date' || column.metadata_type_object.className == 'Tainacan\\Metadata_Types\\Numeric') : false,
                                        'column-medium-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.className == 'Tainacan\\Metadata_Types\\Selectbox' || column.metadata_type_object.className == 'Tainacan\\Metadata_Types\\Category' || column.metadata_type_object.className == 'Tainacan\\Metadata_Types\\Compound') : false,
                                        'column-large-width' : column.metadata_type_object != undefined ? (column.metadata_type_object.className == 'Tainacan\\Metadata_Types\\Textarea') : false,
                                }"
                                @click="goToItemPage(item)">

                            <!-- <data-and-tooltip
                                    v-if="column.metadatum !== 'row_thumbnail' &&
                                            column.metadatum !== 'row_actions' &&
                                            column.metadatum !== 'row_creation'"
                                    :data="renderMetadata( item.metadata[column.slug] )"/> -->
                            <p
                                    v-tooltip="{
                                        content: renderMetadata( item.metadata[column.slug] ),
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"
                                    v-if="item.metadata != undefined &&
                                          column.metadatum !== 'row_thumbnail' &&
                                          column.metadatum !== 'row_actions' &&
                                          column.metadatum !== 'row_creation' &&
                                          column.metadatum !== 'row_author'"
                                    v-html="renderMetadata( item.metadata[column.slug] )"/>

                            <span v-if="column.metadatum == 'row_thumbnail'">
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
                                    v-if="column.metadatum == 'row_author' || column.metadatum == 'row_creation'">
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
    name: 'TableViewMode',
    props: {
        collectionId: Number,
        tableMetadata: Array,
        items: Array,
        isLoading: false
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


