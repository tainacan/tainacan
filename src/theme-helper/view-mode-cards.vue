<template>
    <div class="table-container">
        <div class="table-wrapper">
            <!-- Empty result placeholder -->
            <section
                    v-if="!isLoading && items.length <= 0"
                    class="section">
                <div class="content has-text-gray4 has-text-centered">
                    <p>
                        <b-icon
                                icon="file-multiple"
                                size="is-large"/>
                    </p>
                    <p>{{ $i18n.get('info_no_item_found') }}</p>
                </div>
            </section>
            <!-- CARDS VIEW MODE -->
            <div class="tainacan-cards-container">
                <!-- <div> -->
                <a
                        :key="index"
                        v-for="(item, index) of items"
                        class="tainacan-card"
                        :href="item.url">                                
                    <!-- Title -->
                    <div class="metadata-title">
                        <p 
                                v-tooltip="{
                                    content: item.title != undefined ? item.title : '',
                                    html: true,
                                    autoHide: false,
                                    placement: 'auto-start'
                                }">
                            {{ item.title != undefined ? item.title : '' }}
                        </p>                            
                    </div>
                    <!-- Remaining metadata -->  
                    <div class="media">

                        <img 
                                v-if="item.thumbnail != undefined"
                                :src="item['thumbnail'].tainacan_medium ? item['thumbnail'].tainacan_medium : (item['thumbnail'].medium ? item['thumbnail'].medium : thumbPlaceholderPath)">  

                        <div class="list-metadata media-body">
                           <!-- Description -->
                            <p 
                                    v-tooltip="{
                                        content: item.description != undefined && item.description != '' ? item.description : `<span class='has-text-gray is-italic'>` + $i18n.get('label_description_not_informed') + `</span>`,
                                        html: true,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"   
                                    class="metadata-description"
                                    v-html="item.description != undefined && item.description != '' ? getLimitedDescription(item.description) : `<span class='has-text-gray is-italic'>` + $i18n.get('label_description_not_informed') + `</span>`" />                                                        
                            <br>
                            <!-- Author and Creation Date-->
<!--                            <p 
                                    v-tooltip="{
                                        content: column.metadatum == 'row_author' || column.metadatum == 'row_creation',
                                        html: false,
                                        autoHide: false,
                                        placement: 'auto-start'
                                    }"   
                                    v-for="(column, index) in displayedMetadata"
                                    :key="index"
                                    v-if="column.metadatum == 'row_author' || column.metadatum == 'row_creation'"
                                    class="metadata-author-creation">   
                                {{ column.metadatum == 'row_author' ? $i18n.get('info_created_by') + ' ' + item[column.slug] : $i18n.get('info_date') + ' ' + item[column.slug] }}
                            </p>   
-->                       
                        </div>
                    </div>
               
                </a>
                <!-- </div> -->
            </div>
        </div> 
    </div>
</template>

<script>

export default {
    name: 'ViewModeCards',
    props: {
        collectionId: Number,
        displayedMetadata: Array,
        items: Array,
        isLoading: false,
        shouldUseSmallCard: false
    },
    data () {
        return {
            thumbPlaceholderPath: tainacan_plugin.base_url + '/admin/images/placeholder_square.png'
        }
    },
    methods: {
        renderMetadata(itemMetadata, column) {

            let metadata = itemMetadata[column.slug] != undefined ? itemMetadata[column.slug] : false;

            if (!metadata) {
                return '';
            } else if (metadata.date_i18n) {
                return metadata.date_i18n;
            } else {
                return metadata.value_as_html;
            }
        },
        getLimitedDescription(description) {
            let maxCharacter = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) <= 480 ? 155 : 330;
            return description.length > maxCharacter ? description.substring(0, maxCharacter - 3) + '...' : description;
        }
    }
}
</script>

<style  lang="scss" scoped>
    $turquoise1: #e6f6f8;
    $turquoise2: #d1e6e6;
    $tainacan-input-color: #1d1d1d;
    $gray1: #f2f2f2;
    $gray2: #e5e5e5;
    $gray3: #dcdcdc;
    $gray4: #898d8f;
    $gray5: #454647; 

    @import "../../src/admin/scss/_view-mode-cards.scss";

    .tainacan-cards-container .tainacan-card .metadata-title {
        padding: 0.6rem 0.75rem;
        margin-bottom: 0px;
    }
</style>


