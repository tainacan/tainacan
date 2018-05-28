<template>
    <div class="cards-container">
        <div 
                class="tainacan-card"
                v-for="(item, index) of items"
                :key="index"
                @click="goToItemPage(item)">

            <img 
                    class="card-image"
                    :src="item.thumbnail.medium_large">
            <div class="card-metadata">
                <span 
                        :key="index"
                        v-for="(field, index) of tableFields">
                    <p      
                            v-if="field.display && field.id"
                            :class="{ 'field-main-content': field.field_type_object != undefined ? (field.field_type_object.related_mapped_prop == 'title') : false }"
                            v-html="(field.field == 'row_author' || field.field == 'row_creation') ? item[field.slug] : renderMetadata(item.metadata[field.slug])"/>
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
</template>

<script>
export default {
    name: 'TainacanCardsList',
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

    .cards-container {
        padding: 20px calc(8.333333% - 12px);
        position: relative;
        margin-bottom: 40px;
        display: flex;
        justify-content: space-between;
        flex-flow: wrap;

        .tainacan-card {
            cursor: pointer;
            padding: 12px;
            width: 50%; //258px;
            
            @media screen and (min-width: 769px) and (max-width: 1216px) { width: 33.33333%; } 

            @media screen and (min-width: 1217px) { width: 25%; } 
            
            img.card-image {
                width: 100%;
            }
            .card-metadata {
                padding: 8px 0px;
                background: white;
                font-size: 11px;
                color: $gray-light;

                p.field-main-content {
                    font-size: 14px;
                    color: $tainacan-input-color;
                }
            }          
        }
    }

</style>


