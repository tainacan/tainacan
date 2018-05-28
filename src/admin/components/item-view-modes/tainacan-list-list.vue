<template>
    <div class="list-container">
        <div 
                class="tainacan-list"
                v-for="(item, index) of items"
                :key="index"
                @click="goToItemPage(item)">

            <p class="field-main-content">{{ getTitle(item) }}</p>
            <div>
                <div class="list-image">
                    <img :src="item.thumbnail.medium">
                </div>
                <div class="list-metadata">
                    <span
                            v-for="(field, index) of tableFields"
                            :key="index">
                        <p      
                                v-if="field.display && field.id && (field.field_type_object != undefined && field.field_type_object.related_mapped_prop != 'title')"
                                v-html="renderMetadata(item.metadata[field.slug])"/>
                        <p 
                                v-if="field.field == 'row_author'">
                                {{ $i18n.get('label_created_by') + ": " + item[field.slug] }}
                        </p>
                        <p 
                                v-if="field.field == 'row_creation'">
                                {{ $i18n.get('label_creation_date') + ": " + item[field.slug] }}
                        </p>
                    </span>
                </div> 
            </div>

        </div>
    </div>
</template>

<script>
export default {
    name: 'TainacanListList',
    data(){
        return {
        }
    },
    props: {
        tableFields: Array,
        items: Array,
        isLoading: false,
    },
    methods: {
        getTitle(item) {
            for (let field of this.tableFields) {
                if (field.field_type_object != undefined && field.field_type_object.related_mapped_prop == 'title')
                    return this.renderMetadata(item.metadata[field.slug]);
            }
        },
        renderMetadata(metadata) {

            if (!metadata) {
                return '';
            } else if (metadata.date_i18n) {
                return metadata.date_i18n;
            } else {
                return metadata.value_as_html;
            }
        },
        goToItemPage(item) {
            window.location.href = item.url;   
        }
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";

    .list-container {
        padding: 20px calc(8.333333% - 12px);
        position: relative;
        margin-bottom: 40px;
        display: flex;
        justify-content: space-between;
        flex-flow: wrap;

        .tainacan-list {
            cursor: pointer;
            align-items: flex-start;
            display: flex;
            flex-flow: column;
            text-align: left;
            padding: 16px 12px;
            width: 100%;
            
            @media screen and (min-width: 769px) and (max-width: 1216px) { width: 50%; } 

            @media screen and (min-width: 1217px) { width: 33.333333%; } 
            
            p.field-main-content {
                font-size: 14px;
                color: $tainacan-input-color;
            }
            &>div {
                display: flex;

                .list-image {
                    width: 25%;

                    img { width: 100%; }
                }
                .list-metadata {
                    width: 75%;
                    padding: 0px 16px;
                    background: white;
                    font-size: 11px;
                    color: $gray-light;
                }      
            }    
        }
    }

</style>


