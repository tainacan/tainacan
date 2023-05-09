<template>
    <div class="metadata-mappers-area">
        <b-loading
                :can-cancel="false"
                :is-full-page="false"
                :active.sync="isLoadingMetadatumMappers"/>
        <b-loading
                :can-cancel="false"
                :is-full-page="false"
                :active.sync="isLoadingMetadata"/>

        <b-field>
            <p style="line-height: 2em;">{{ $i18n.get('info_metadata_mapper_helper') }}</p>
            <b-select
                    id="mappers-options-dropdown"
                    size="is-small"
                    :placeholder="$i18n.get('instruction_select_a_mapper')"
                    :value="mapper"
                    @input="onSelectMetadataMapper($event)">
                <option
                        v-for="metadatumMapper in metadatumMappers"
                        :key="metadatumMapper.slug"
                        :value="metadatumMapper">
                    {{ metadatumMapper.name }}
                </option>
            </b-select>
        </b-field>

        <!-- No metadata found warning -->
        <section 
                v-if="activeMetadatumList.length <= 0 && !isLoadingMetadata"
                class="field is-grouped-centered section">
            <div class="content has-text-gray has-text-centered">
                <p>
                    <span class="icon is-large">
                        <i class="tainacan-icon tainacan-icon-36px tainacan-icon-metadata"/>
                    </span>
                </p>
                <p>{{ $i18n.get('info_there_is_no_metadatum') }}</p>
            </div>
        </section>

        <!-- Mapping list -->
        <form 
                class="tainacan-form"
                v-else>

            <div class="mapping-control">
                <div
                        v-if="mapper != '' && mapper.allow_extra_metadata"
                        class="modal-new-link">
                    <a
                            v-if="collectionId != null && collectionId != undefined"
                            class="is-inline is-pulled-right add-link"
                            @click="onNewMetadataMapperMetadata()">
                        <span class="icon is-small">
                            <i class="tainacan-icon tainacan-icon-add"/>
                        </span>
                        {{ $i18n.get('label_add_more_mapper_metadata') }}
                    </a>
                </div>
            </div>
            <div 
                    class="mapping-header"
                    v-if="mapperMetadata.length > 0">
                <p>{{ $i18n.get('label_from_source_mapper') }}</p>
                <hr>
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-pointer tainacan-icon-1-25em" />
                </span>
                <hr>
                <p>{{ $i18n.get('label_to_target_mapper') }}</p>
            </div>

            <div 
                    v-for="(mapperMetadatum, index) of mapperMetadata"
                    :key="index"
                    class="source-metadatum">
                
                <b-select
                        :name="'mappers-metadatum-select-' + mapperMetadatum.slug"
                        v-model="mapperMetadatum.selected"
                        @input="onSelectMetadatumForMapperMetadata">
                    <option
                            value="">
                        {{ $i18n.get('instruction_select_a_metadatum') }}
                    </option>
                    <option
                        v-for="(metadatum, metadatumIndex) in activeMetadatumList"
                        :key="metadatumIndex"
                        :value="metadatum.id"
                        :disabled="isMetadatumSelected(metadatum.id)">
                        {{ metadatum.name }}
                    </option>
                </b-select>

                <p>
                    {{ mapperMetadatum.label }}
                    <a 
                            :style="{ visibility: 
                                    mapperMetadatum.isCustom
                                    ? 'visible' : 'hidden'
                                }" 
                            @click.prevent="editMetadatumCustomMapper(mapperMetadatum)">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('edit'),
                                    autoHide: true,
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                    placement: 'auto-start'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                        </span>
                    </a>
                    <a 
                            :style="{ visibility: 
                                    mapperMetadatum.isCustom
                                    ? 'visible' : 'hidden'
                                }" 
                            @click.prevent="removeMetadatumCustomMapper(mapperMetadatum)">
                        <span
                                v-tooltip="{
                                    content: $i18n.get('delete'),
                                    autoHide: true,
                                    popperClass: ['tainacan-tooltip', 'tooltip', isRepositoryLevel ? 'tainacan-repository-tooltip' : ''],
                                    placement: 'auto-start'
                                }"
                                class="icon">
                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-delete"/>
                        </span>
                    </a>
                </p>
            </div>

             <div 
                    v-if="mapper != '' && !isLoadingMetadatumMappers"
                    class="field is-grouped form-submit fixed-form-submit">
                <div class="control">
                    <button
                            class="button is-outlined"
                            type="button"
                            @click="onCancelUpdateMetadataMapperMetadata">{{ $i18n.get('cancel') }}</button>
                </div>
                <div class="control">
                    <button
                            @click.prevent="onUpdateMetadataMapperMetadataClick"
                            :class="{ 'is-loading': isMapperMetadataLoading }"
                            class="button is-success">{{ $i18n.get('save') }}</button>
                </div>
            </div>
            
        </form>

        <b-modal
                @close="onCancelNewMetadataMapperMetadata"
                :active.sync="isMapperMetadataCreating"
                trap-focus
                aria-modal
                aria-role="dialog"
                custom-class="tainacan-modal"
                :close-button-aria-label="$i18n.get('close')">
            <div 
                    autofocus
                    role="dialog"
                    tabindex="-1"
                    aria-modal
                    class="tainacan-modal-content">
                <div class="tainacan-modal-title">
                    <h2>{{ $i18n.get('instruction_insert_mapper_metadatum_info') }}</h2>
                    <hr>
                </div>
                <b-field>
                    <b-input
                            v-model="newMetadataLabel"
                            required
                            :placeholder="$i18n.get('label_name')"/>
                </b-field>
                <b-field>
                    <b-input
                            placeholder="URI"
                            type="url"
                            required
                            v-model="newMetadataUri"/>
                </b-field>
                <div class="field is-grouped form-submit">
                    <div class="control">
                        <button
                                class="button is-outlined"
                                type="button"
                                @click="onCancelNewMetadataMapperMetadata">{{ $i18n.get('cancel') }}</button>
                    </div>
                    <div class="control">
                        <button
                                :class="{ 'is-loading': isMapperMetadataLoading, 'is-success': !isMapperMetadataLoading }"
                                @click.prevent="onSaveNewMetadataMapperMetadata"
                                :disabled="isNewMetadataMapperMetadataDisabled || isMapperMetadataLoading"
                                class="button">{{ $i18n.get('save') }}
                        </button>
                    </div>
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'MetadataMappingList',
    props: {
        isRepositoryLevel: Boolean
    },
    data() {
        return {
            collectionId: '',
            isLoadingMetadatumMappers: true,
            isLoadingMetadata: false,
            mapper: '',
            mapperMetadata: [],
            isMapperMetadataLoading: false,
            isMapperMetadataCreating: false,
            mappedMetadata: [],
            newMapperMetadataList: [],
            newMetadataLabel: '',
            newMetadataUri: ''
        }
    },
    computed: {
        metadatumMappers() {
            return this.getMetadatumMappers();
        },
        isNewMetadataMapperMetadataDisabled() {
            return !this.newMetadataLabel || !this.newMetadataUri;
        },
        activeMetadatumList() { 
            return this.getMetadata();
        }
    },
    mounted() {
        
        /* If we're in a collection list, the metadata won't exist as they are read inside sections */        
        if ( !this.isRepositoryLevel ) {
            this.collectionId = this.$route.params.collectionId;

            this.isLoadingMetadata = true;
            
            this.cleanMetadata();
            this.fetchMetadata({
                collectionId: this.collectionId,
                isRepositoryLevel: false, 
                isContextEdit: true, 
                includeDisabled: true,
                includeOptionsAsHtml: false
            }).then((resp) => {
                resp.request
                    .then(() => {
                        this.loadMetadataMappers();
                        this.isLoadingMetadata = false;
                    })
                    .catch(() => {
                        this.isLoadingMetadata = false;
                    });
            })
            .catch(() => this.isLoadingMetadata = false); 
        } else {
            this.loadMetadataMappers();
        }
    },
    methods: {
        ...mapActions('metadata', [
            'fetchMetadata',
            'cleanMetadata',
            'fetchMetadatumMappers',
            'updateMetadataMapperMetadata',
        ]),
        ...mapGetters('metadata',[
            'getMetadatumMappers',
            'getMetadata'
        ]),
        loadMetadataMappers() {
            this.isLoadingMetadatumMappers = true;
            this.fetchMetadatumMappers()
                .then(() => {
                    this.isLoadingMetadatumMappers = false;
                    
                    if (this.metadatumMappers.length >= 1)
                        this.onSelectMetadataMapper(this.metadatumMappers[0])
                })
                .catch(() => {
                    this.isLoadingMetadatumMappers = false;
                });
        },
        onSelectMetadataMapper(metadatumMapper) {

            this.isMapperMetadataLoading = true;
            this.mapper = metadatumMapper; //TODO try to use v-model again
            this.mapperMetadata = [];
            
            if (metadatumMapper != '') {
                for (var k in metadatumMapper.metadata) {
                    var item = metadatumMapper.metadata[k];
                    item.slug = k;
                    item.selected = '';
                    item.isCustom = false;
                    this.activeMetadatumList.forEach((metadatum) => {
                        if (
                            Object.prototype.hasOwnProperty.call(metadatum.exposer_mapping, metadatumMapper.slug) &&
                            metadatum.exposer_mapping[metadatumMapper.slug] == item.slug
                        ) {
                            item.selected = metadatum.id;
                            this.mappedMetadata.push(metadatum.id);
                        }
                    });
                    this.mapperMetadata.push(item);
                }
                this.activeMetadatumList.forEach((metadatum) => {
                    if (
                        Object.prototype.hasOwnProperty.call(metadatum.exposer_mapping, metadatumMapper.slug) &&
                        typeof metadatum.exposer_mapping[metadatumMapper.slug] == 'object'
                    ) {
                        this.newMapperMetadataList.push(Object.assign({},metadatum.exposer_mapping[metadatumMapper.slug]));
                        this.mappedMetadata.push(metadatum.id);
                        var item = Object.assign({},metadatum.exposer_mapping[metadatumMapper.slug]);
                        item.selected = metadatum.id;
                        item.isCustom = true;
                        this.mapperMetadata.push(item);
                    }
                });
            }
            this.isMapperMetadataLoading = false;
        },
        isMetadatumSelected(id) {
            return this.mappedMetadata.indexOf(id) > -1;
        },
        onSelectMetadatumForMapperMetadata() {
            this.mappedMetadata = [];
            this.mapperMetadata.forEach((item) => {
                if(item.selected.length != 0) {
                    this.mappedMetadata.push(item.selected);
                }
            });
        },
        onUpdateMetadataMapperMetadataClick() {
            this.isMapperMetadataLoading = true;
            var metadataMapperMetadata = [];
            this.mapperMetadata.forEach((item) => {
                if (item.selected.length != 0) {
                    var map = {
                            metadatum_id: item.selected,
                            mapper_metadata: item.slug
                    };
                    metadataMapperMetadata.push(map);
                }
            });
            this.activeMetadatumList.forEach((item) => {
                if(this.mappedMetadata.indexOf(item.id) == -1) {
                    var map = {
                            metadatum_id: item.id,
                            mapper_metadata: ''
                    };
                    metadataMapperMetadata.push(map);
                }
            });
            this.newMapperMetadataList.forEach((item) => {
                var slug = item.slug;
                metadataMapperMetadata.forEach( (meta, index) => {
                    if(meta.mapper_metadata == slug) {
                        var item_clone = Object.assign({}, item); // TODO check if still need to clone
                        delete item_clone.selected;
                        delete item_clone.isCustom;
                        meta.mapper_metadata = item_clone;
                        metadataMapperMetadata[index] = meta;
                    }
                });
            });
            this.updateMetadataMapperMetadata({
                    metadataMapperMetadata: metadataMapperMetadata,
                    mapper: this.mapper.slug
            }).then(() => {
                this.isMapperMetadataLoading = false;
            })
            .catch(() => {
                this.isMapperMetadataLoading = false;
            });
        },
        onCancelUpdateMetadataMapperMetadata() {
            this.isMapperMetadataLoading = true;
            this.onSelectMetadataMapper(this.mapper);
            this.isMapperMetadataLoading = false;
        },
        onNewMetadataMapperMetadata() {
            this.isMapperMetadataCreating = true;
        },
        onCancelNewMetadataMapperMetadata() {
            this.isMapperMetadataCreating = false;
            this.newMetadataLabel = '';
            this.newMetadataUri = '';
            this.new_metadata_slug = '';
        },
        onSaveNewMetadataMapperMetadata() {
            this.isMapperMetadataLoading = true;
            var newMapperMetadata = {
                    label: this.newMetadataLabel,
                    uri: this.newMetadataUri,
                    slug: this.stringToSlug(this.newMetadataLabel),
                    isCustom: true
            };
            var selected = '';
            if(this.new_metadata_slug != '') { // Editing
                this.newMapperMetadataList.forEach((meta, index) => {
                    if(meta.slug == this.new_metadata_slug) {
                        this.newMapperMetadataList.splice(index);
                        this.mapperMetadata.forEach((item, index2) => {
                            if (item.slug == this.new_metadata_slug) {
                                selected = item.selected;
                                this.mapperMetadata.splice(index2);
                            }
                        });
                    }
                });
            }
            this.newMapperMetadataList.push(newMapperMetadata);
            newMapperMetadata.selected = selected;
            this.mapperMetadata.push(newMapperMetadata);
            this.newMetadataLabel = '';
            this.newMetadataUri = '';
            this.new_metadata_slug = '';
            this.isMapperMetadataCreating = false;
            this.isMapperMetadataLoading = false;
        },
        stringToSlug(str) { // adapted from https://gist.github.com/spyesx/561b1d65d4afb595f295
            str = str.replace(/^\s+|\s+$/g, ''); // trim
            str = str.toLowerCase();

            // remove accents, swap ñ for n, etc
            var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
            var to   = "aaaaeeeeiiiioooouuuunc------";

            for (var i=0, l=from.length ; i<l ; i++) {
                str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
            }

            str = str.replace('.', '-') // replace a dot by a dash 
                .replace(/[^a-z0-9 -]/g, '') // remove invalid chars
                .replace(/\s+/g, '-') // collapse whitespace and replace by a dash
                .replace(/-+/g, '-'); // collapse dashes

            return str;
        },
        editMetadatumCustomMapper(customMapperMeta) {
            this.newMetadataLabel = customMapperMeta.label;
            this.newMetadataUri = customMapperMeta.uri;
            this.new_metadata_slug = customMapperMeta.slug;
            this.isMapperMetadataCreating = true;
        },
        removeMetadatumCustomMapper(customMapperMeta) {
            var itemid = 0;
            this.newMapperMetadataList.forEach((meta, index) => {
                if(meta.slug == customMapperMeta.slug) {
                    this.newMapperMetadataList.splice(index);
                    var rem = this.mappedMetadata.indexOf(meta.selected);
                    this.mappedMetadata.splice(rem);
                    itemid = customMapperMeta.selected;
                }
            });
            if(itemid != '' && itemid > 0) {
                this.mapperMetadata.forEach((item, index) => {
                    if (item.selected == itemid) {
                        this.mapperMetadata.splice(index);
                    }
                });
            }
            return true;
        }
    }
}
</script>

<style lang="scss" scoped>

    .metadata-mappers-area {
        padding: 0 1em;
    }

    .tainacan-form {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 247px;
    }
    .form-submit {
        margin-top: 24px;
    }

    .add-link {
        font-size: 0.875em;
    }

    .field {
        position: relative;
    }

    .mapping-control {
        background-color: var(--tainacan-background-color, white);
        position: sticky;
        top: -34px;
        z-index: 9;
        height: 2.5rem;
        padding-top: 12px;
    }
    .mapping-header {
        background-color: var(--tainacan-background-color, white);
        position: sticky;
        top: 0;
        z-index: 9;
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: var(--tainacan-info-color);
        font-size: 0.875em;
        font-weight: bold;
        margin: 0 0 12px 0;
        border-bottom: 1px solid var(--tainacan-gray3);
        box-shadow: 0 5px 12px -14px var(--tainacan-gray5);

        p {
            white-space: nowrap;
        }
        hr {
            width: 100%;
            margin-left: 12px;
            margin-right: 12px;
            height: 1px;
            background: var(--tainacan-gray3);
        }

        @media screen and (max-width: 768px) {
            p {
                white-space: normal;
            }
            hr {
                display: none;
            }
        }
    }

    .source-metadatum {
        padding: 2px 0;
        min-height: 35px;
        width: 100%;
        margin: 3px 16px 6px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        
        &::before {
            display: block;
            content: '';
            position: absolute;
            height: 1px;
            width: 100%;
            background-color: var(--tainacan-gray2);
            z-index: -1;
        }
        &>p {
            font-weight: normal;
            transition: font-weight 0.1s ease;
            padding-left: 6px;
            overflow: hidden;
            word-wrap: break-word;
            background-color: var(--tainacan-background-color, white);
        }
        .control {
            max-width: 60%;
        }
        &:hover {
            --tainacan-input-border-color: var(--tainacan-gray4);
            &::before {
                background-color: var(--tainacan-gray4);
            }
            &>p {
                font-weight: bold;
            }
        }
    }

    .fixed-form-submit {
        margin-top: 24px;
        position: sticky !important;
        bottom: 0;
        background: var(--tainacan-background-color, white);
        z-index: 9;
        padding: 12px;
        border-top: 1px solid  var(--tainacan-gray3);
        box-shadow: 0 -5px 12px -14px var(--tainacan-gray5);
    }
</style>