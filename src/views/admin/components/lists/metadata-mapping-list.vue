<template>
    <div class="metadata-mappers-area">

        <b-loading
                :can-cancel="false"
                :is-full-page="false"
                :active.sync="isLoadingMetadata"/>

        <p>{{ mapper.description }}</p>

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
                        v-if="mapper && mapper.allow_extra_metadata"
                        class="modal-new-link">
                    <a
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
                    class="mapping-header">
                <p>{{ $i18n.get('label_from_source_mapper') }}</p>
                <hr>
                <span class="icon">
                    <i class="tainacan-icon tainacan-icon-pointer tainacan-icon-1-25em" />
                </span>
                <hr>
                <p>{{ $i18n.get('label_to_target_mapper') }}</p>
            </div>

            <section 
                    v-if="mapperMetadata.length <= 0"
                    class="section">
                <div class="content has-text-grey has-text-centered">
                    <p>
                        <span class="icon">
                            <i class="tainacan-icon tainacan-icon-30px tainacan-icon-processes tainacan-icon-rotate-90"/>
                        </span>
                    </p>
                    <p>{{ $i18n.get('info_no_metadata_from_mapper') }} <span v-if="mapper.allow_extra_metadata">{{ $i18n.get('info_mapper_extra_metadata') }}</span></p>
                </div>
            </section>

            <div 
                    v-else
                    v-for="(mapperMetadatum, index) of mapperMetadata"
                    :key="index"
                    class="source-metadatum">
                
                <b-select
                        :name="'mappers-metadatum-select-' + mapperMetadatum.slug"
                        v-model="mapperMetadatum.selected"
                        @input="onSelectMetadatumForMapperMetadata"
                        :disabled="!isRepositoryLevel && mapperMetadatum.isRepositoryLevel">
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
                    v-if="mapper"
                    class="field is-grouped form-submit fixed-form-submit">
                <div class="control">
                    <button
                            class="button is-outlined"
                            type="button"
                            @click="onCancelUpdateMapper">{{ $i18n.get('cancel') }}</button>
                </div>
                <div class="control">
                    <button
                            @click.prevent="onUpdateMapperClick"
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
                
                <!-- Hook for extra Form options -->
                <template 
                        v-if="mapper.add_meta_form">  
                    <form 
                        id="form-mapper-metadata"
                        class="form-hook-region"
                        v-html="mapper.add_meta_form"/>
                </template>

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
        isRepositoryLevel: Boolean,
        mapper: Object
    },
    data() {
        return {
            collectionId: '',
            isLoadingMetadata: false,
            mapperMetadata: [],
            isMapperMetadataLoading: false,
            isMapperMetadataCreating: false,
            mappedMetadata: [],
            newMapperMetadataList: [],
            newMetadataLabel: '',
            newMetadataUri: '',
            newMetadataSlug: '',
            customForm: {
                'teste': 'aaa'
            }
        }
    },
    computed: {
        isNewMetadataMapperMetadataDisabled() {
            return !this.newMetadataLabel || !this.newMetadataUri;
        },
        activeMetadatumList() { 
            return this.getMetadata();
        }
    },
    mounted() {
               
        this.collectionId = this.$route.params.collectionId;

        this.isLoadingMetadata = true;
        
        this.cleanMetadata();

        this.fetchMetadata({
            collectionId: this.collectionId,
            isRepositoryLevel: this.isRepositoryLevel, 
            isContextEdit: true, 
            includeDisabled: true,
            includeOptionsAsHtml: false
        }).then((resp) => {
            resp.request
                .then(() => {
                    this.loadMapperMetadata();
                    this.isLoadingMetadata = false;
                })
                .catch(() => {
                    this.isLoadingMetadata = false;
                });
        })
        .catch(() => this.isLoadingMetadata = false); 

    },
    methods: {
        ...mapActions('metadata', [
            'fetchMetadata',
            'cleanMetadata',
            'fetchMappers',
            'updateMapper',
        ]),
        ...mapGetters('metadata',[
            'getMetadata'
        ]),
        loadMapperMetadata() {

            this.isMapperMetadataLoading = true;
            this.mapperMetadata = [];
            
            if ( this.mapper && this.mapper.metadata ) {

                for (let metadatum in this.mapper.metadata) {
                    let item = this.mapper.metadata[metadatum];
                    item.slug = metadatum;
                    item.selected = '';
                    item.isCustom = false;

                    this.activeMetadatumList.forEach((activeMetadatum) => {
                        if (
                            Object.prototype.hasOwnProperty.call(activeMetadatum.exposer_mapping, this.mapper.slug) &&
                            activeMetadatum.exposer_mapping[this.mapper.slug] == item.slug
                        ) {
                            item.selected = activeMetadatum.id;
                            item.isRepositoryLevel = activeMetadatum.collection_id === 'default';
                            this.mappedMetadata.push(activeMetadatum.id);
                        }
                    });
                    this.mapperMetadata.push(item);
                }

                this.activeMetadatumList.forEach((activeMetadatum) => {
                    if (
                        Object.prototype.hasOwnProperty.call(activeMetadatum.exposer_mapping, this.mapper.slug) &&
                        typeof activeMetadatum.exposer_mapping[this.mapper.slug] == 'object'
                    ) {
                        this.newMapperMetadataList.push(Object.assign({},activeMetadatum.exposer_mapping[this.mapper.slug]));
                        this.mappedMetadata.push(activeMetadatum.id);

                        let item = Object.assign( {}, activeMetadatum.exposer_mapping[this.mapper.slug] );
                        item.selected = activeMetadatum.id;
                        item.isCustom = true;
                        item.isRepositoryLevel = activeMetadatum.collection_id === 'default';

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
            this.mapperMetadata.forEach((metadatum) => {
                if ( metadatum.selected.length != 0 ) 
                    this.mappedMetadata.push(metadatum.selected);
            });
        },
        onUpdateMapperClick() {
            this.isMapperMetadataLoading = true;
            let metadataMapperMetadata = [];
            this.mapperMetadata.forEach((metadatum) => {
                if (metadatum.selected.length != 0) {
                    let map = {
                            metadatum_id: metadatum.selected,
                            mapper_metadata: metadatum.slug
                    };
                    metadataMapperMetadata.push(map);
                }
            });
            this.activeMetadatumList.forEach( metadatum => {
                if(this.mappedMetadata.indexOf(metadatum.id) == -1) {
                    let map = {
                            metadatum_id: metadatum.id,
                            mapper_metadata: ''
                    };
                    metadataMapperMetadata.push(map);
                }
            });
            this.newMapperMetadataList.forEach( metadatum => {
                metadataMapperMetadata.forEach( (meta, index) => {
                    if ( meta.mapper_metadata == metadatum.slug ) {
                        let itemClone = Object.assign({}, metadatum); // TODO check if still need to clone
                        delete itemClone.selected;
                        delete itemClone.isCustom;
                        meta.mapper_metadata = itemClone;
                        metadataMapperMetadata[index] = meta;
                    }
                });
            });
            this.updateMapper({
                isRepositoryLevel: this.isRepositoryLevel,
                collectionId: this.collectionId,
                metadataMapperMetadata: metadataMapperMetadata,
                mapper: this.mapper.slug
            }).then(() => {
                this.isMapperMetadataLoading = false;
            })
            .catch(() => {
                this.isMapperMetadataLoading = false;
            });
        },
        onCancelUpdateMapper() {
            this.isMapperMetadataLoading = true;
            this.onSelectMetadataMapper(this.mapper);
            this.isMapperMetadataLoading = false;
        },
        onNewMetadataMapperMetadata() {
            this.isMapperMetadataCreating = true;

            // Fills hook forms with it's real values 
            this.$nextTick()
                .then(() => {
                    this.updateExtraFormData(this.customForm);
                });
        },
        onCancelNewMetadataMapperMetadata() {
            this.isMapperMetadataCreating = false;
            this.newMetadataLabel = '';
            this.newMetadataUri = '';
            this.newMetadataSlug = '';
        },
        onSaveNewMetadataMapperMetadata() {
            this.isMapperMetadataLoading = true;

            this.fillExtraFormData(this.customform);
            
            let newMapperMetadata = {
                label: this.newMetadataLabel,
                uri: this.newMetadataUri,
                slug: this.stringToSlug(this.newMetadataLabel),
                isCustom: true
            };

            let selected = '';
            if ( this.newMetadataSlug != '' ) { // Editing
                
                this.newMapperMetadataList.forEach((meta, index) => {
                    if ( meta.slug == this.newMetadataSlug ) {

                        this.newMapperMetadataList.splice(index);
                        this.mapperMetadata.forEach((metadatum, index2) => {
                            if (metadatum.slug == this.newMetadataSlug) {
                                selected = metadatum.selected;
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
            this.newMetadataSlug = '';
            this.isMapperMetadataCreating = false;
            this.isMapperMetadataLoading = false;
        },
        stringToSlug(str) { // adapted from https://gist.github.com/spyesx/561b1d65d4afb595f295
            str = str.replace(/^\s+|\s+$/g, '').toLowerCase();

            // remove accents, swap ñ for n, etc
            const from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
            const to   = "aaaaeeeeiiiioooouuuunc------";

            for (let i = 0, l = from.length ; i < l ; i++) {
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
            this.newMetadataSlug = customMapperMeta.slug;
            this.isMapperMetadataCreating = true;

            // Fills hook forms with it's real values 
            this.$nextTick()
                .then(() => {
                    this.updateExtraFormData(this.customForm);
                });
        },
        removeMetadatumCustomMapper(customMapperMeta) {
            let metadatumId = 0;

            this.newMapperMetadataList.forEach((meta, index) => {
                if ( meta.slug == customMapperMeta.slug ) {
                    this.newMapperMetadataList.splice(index);
                    let rem = this.mappedMetadata.indexOf(meta.selected);
                    this.mappedMetadata.splice(rem);
                    metadatumId = customMapperMeta.selected;
                }
            });

            if ( metadatumId != '' && metadatumId > 0 ) {
                this.mapperMetadata.forEach((metadatum, index) => {
                    if ( metadatum.selected == metadatumId )
                        this.mapperMetadata.splice(index);
                });
            }
            return true;
        },
        fillExtraFormData(data) {

            let formElement = document.getElementById('form-mapper-metadata');
            if (formElement) {  
                for (let element of formElement.elements) {
                    if (element.type == "checkbox" || (element.type == "select" && element.multiple != undefined && element.multiple == true)) {
                        if (element.checked && element.name != undefined && element.name != '') {
                            if (!Array.isArray(data[element.name]))
                                data[element.name] = [];
                            data[element.name].push(element.value);
                        }
                    } else if (element.type == "radio") {
                        if (element.checked && element.name != undefined && element.name != '')
                            data[element.name] = element.value;
                    } else {
                        data[element.name] = element.value;
                    }
                }
            }
        },
        updateExtraFormData(entityObject) {
            console.log(entityObject)
            let formElement = document.getElementById('form-mapper-metadata');
            console.log(formElement)
            if (formElement) { 
                for (let element of formElement.elements) {
                    for (let key of Object.keys(entityObject)) {
                        if (element['name'] == key)  {
                            if (Array.isArray(entityObject[key])) {
                                let obj = entityObject[key].find((value) => { return value == element['value'] });
                                element['checked'] = obj != undefined ? true : false;
                            } else {
                                if (entityObject[key] != null && entityObject[key] != undefined && entityObject[key] != ''){
                                    if (element.type == "radio")
                                        element['checked'] = entityObject[key] == element['value'] ? true : false;
                                    else 
                                        element['value'] = entityObject[key];
                                }
                                    
                            }
                        }
                    }
                }
            }
        },
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
        padding: 14px var(--tainacan-one-column);
        position: fixed;
        bottom: 0;
        right: 0;
        z-index: 9999;
        background-color: var(--tainacan-gray1);
        width: calc(100% - var(--tainacan-sidebar-width, 3.25em));
        height: 60px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        transition: bottom 0.5s ease, width 0.2s linear;
    }
</style>