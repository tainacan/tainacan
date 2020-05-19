<template>
    <div>
        <b-loading :active.sync="isLoadingMetadatumMappers"/>
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
        <section v-else>
            <div class="field is-grouped form-submit">
                <b-select
                        id="mappers-options-dropdown"
                        :placeholder="$i18n.get('instruction_select_a_mapper')"
                        @input="onSelectMetadataMapper($event)">
                    <option
                            v-for="metadatum_mapper in metadatum_mappers"
                            :key="metadatum_mapper.slug"
                            :value="metadatum_mapper">
                        {{ $i18n.get(metadatum_mapper.name) }}
                    </option>
                </b-select>
                <div
                        class="control"
                        v-if="mapper != '' && !isLoadingMetadatumMappers">
                    <button
                            class="button is-outlined"
                            type="button"
                            @click="onCancelUpdateMetadataMapperMetadata">{{ $i18n.get('cancel') }}</button>
                </div>
                <div
                        class="control"
                        v-if="mapper != '' && !isLoadingMetadatumMappers">
                    <button
                            @click.prevent="onUpdateMetadataMapperMetadataClick"
                            class="button is-success">{{ $i18n.get('save') }}</button>
                </div>
            </div>
        </section>

        <br>

        <section>
            <b-table
                    size="is-small"
                    :data="mapperMetadata"
                    :loading="isMapperMetadataLoading">

                <template slot-scope="props">
                    <b-table-column
                            field="label"
                            :label="$i18n.get('label_mapper_metadata')">
                        {{ props.row.label }}
                    </b-table-column>

                    <b-table-column
                            field="slug"
                            :label="$i18n.get('metadatum')">
                        <b-select
                                :name="'mappers-metadatum-select-' + props.row.slug"
                                v-model="props.row.selected"
                                @input="onSelectMetadatumForMapperMetadata">
                            <option
                                    value="">
                                {{ $i18n.get('instruction_select_a_metadatum') }}
                            </option>
                            <option
                                v-for="(metadatum, index) in activeMetadatumList"
                                :key="index"
                                :value="metadatum.id"
                                :disabled="isMetadatumSelected(metadatum.id)">
                                {{ metadatum.name }}
                            </option>
                        </b-select>
                    </b-table-column>
                    <b-table-column
                            field="isCustom"
                            label="">
                        <a 
                                :style="{ visibility: 
                                        props.row.isCustom
                                        ? 'visible' : 'hidden'
                                    }" 
                                @click.prevent="editMetadatumCustomMapper(props.row)">
                            <span
                                    v-tooltip="{
                                        content: $i18n.get('edit'),
                                        autoHide: true,
                                        classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }"
                                    class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                            </span>
                        </a>
                        <a 
                                :style="{ visibility: 
                                        props.row.isCustom
                                        ? 'visible' : 'hidden'
                                    }" 
                                @click.prevent="removeMetadatumCustomMapper(props.row)">
                            <span
                                    v-tooltip="{
                                        content: $i18n.get('delete'),
                                        autoHide: true,
                                        classes: ['tooltip', isRepositoryLevel ? 'repository-tooltip' : ''],
                                        placement: 'auto-start'
                                    }"
                                    class="icon">
                                <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-delete"/>
                            </span>
                        </a>
                    </b-table-column>
                </template>
            </b-table>
        </section>
        <section
                v-if="mapper != '' && mapper.allow_extra_metadata">
            <div
                    class="modal-new-link">
                <a
                        v-if="collectionId != null && collectionId != undefined"
                        class="is-inline is-pulled-left add-link"
                        @click="onNewMetadataMapperMetadata()">
                    <span class="icon is-small">
                        <i class="tainacan-icon tainacan-icon-add"/>
                    </span>
                    {{ $i18n.get('label_add_more_mapper_metadata') }}
                </a>
            </div>
        </section>
        <b-modal
                @close="onCancelNewMetadataMapperMetadata"
                :active.sync="isMapperMetadataCreating"
                trap-focus
                aria-modal
                aria-role="dialog">
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
                            v-model="new_metadata_label"
                            required
                            :placeholder="$i18n.get('label_name')"/>
                </b-field>
                <b-field>
                    <b-input
                            placeholder="URI"
                            type="url"
                            required
                            v-model="new_metadata_uri"/>
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
                                @click.prevent="onSaveNewMetadataMapperMetadata"
                                :disabled="isNewMetadataMapperMetadataDisabled"
                                class="button is-success">{{ $i18n.get('save') }}</button>
                    </div>
                </div>
            </div>
        </b-modal>

        <section
                v-if="mapper != '' && !isLoadingMetadatumMappers">
            <div class="field is-grouped form-submit w-100">
                <div class="control">
                    <button
                            class="button is-outlined"
                            type="button"
                            @click="onCancelUpdateMetadataMapperMetadata">{{ $i18n.get('cancel') }}</button>
                </div>
                <div class="control">
                    <button
                            @click.prevent="onUpdateMetadataMapperMetadataClick"
                            class="button is-success">{{ $i18n.get('save') }}</button>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'MetadataMappingList',
    props: {
        isRepositoryLevel: Boolean
    },
    data(){           
        return {
            collectionId: '',
            isLoadingMetadatumMappers: true,
            mapper: '',
            mapperMetadata: [],
            isMapperMetadataLoading: false,
            isMapperMetadataCreating: false,
            mappedMetadata: [],
            newMapperMetadataList: [],
        }
    },
    computed: {
        metadatum_mappers() {
            return this.getMetadatumMappers();
        },
        isNewMetadataMapperMetadataDisabled() {
            return !this.new_metadata_label || !this.new_metadata_uri;
        },
        activeMetadatumList() { 
            return this.getMetadata();
        }
    },
    mounted() {
        this.fetchMetadatumMappers()
            .then(() => {
                this.isLoadingMetadatumMappers = false;
            })
            .catch(() => {
                this.isLoadingMetadatumMappers = false;
            });
    },
    methods: {
        ...mapActions('metadata', [
            'fetchMetadatumMappers',
            'updateMetadataMapperMetadata',
        ]),
        ...mapGetters('metadata',[
            'getMetadatumMappers',
            'getMetadata'
        ]),
        onSelectMetadataMapper(metadatum_mapper) {

            this.isMapperMetadataLoading = true;
            this.mapper = metadatum_mapper; //TODO try to use v-model again
            this.mapperMetadata = [];
            this.mappedMetadata = [];
            
            if(metadatum_mapper != '') {
                for (var k in metadatum_mapper.metadata) {
                    var item = metadatum_mapper.metadata[k];
                    item.slug = k;
                    item.selected = '';
                    item.isCustom = false;
                    this.activeMetadatumList.forEach((metadatum) => {
                        if(
                                metadatum.exposer_mapping.hasOwnProperty(metadatum_mapper.slug) &&
                                metadatum.exposer_mapping[metadatum_mapper.slug] == item.slug
                        ) {
                            item.selected = metadatum.id;
                            this.mappedMetadata.push(metadatum.id);
                        }
                    });
                    this.mapperMetadata.push(item);
                }
                this.activeMetadatumList.forEach((metadatum) => {
                    if(
                            metadatum.exposer_mapping.hasOwnProperty(metadatum_mapper.slug) &&
                            typeof metadatum.exposer_mapping[metadatum_mapper.slug] == 'object'
                    ) {
                        this.newMapperMetadataList.push(Object.assign({},metadatum.exposer_mapping[metadatum_mapper.slug]));
                        this.mappedMetadata.push(metadatum.id);
                        var item = Object.assign({},metadatum.exposer_mapping[metadatum_mapper.slug]);
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
            this.updateMetadataMapperMetadata({metadataMapperMetadata: metadataMapperMetadata, mapper: this.mapper.slug}).then(() => {
                this.isLoadingMetadata = true;
                this.refreshMetadata();
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
            this.new_metadata_label = '';
            this.new_metadata_uri = '';
            this.new_metadata_slug = '';
        },
        onSaveNewMetadataMapperMetadata() {
            this.isMapperMetadataLoading = true;
            var newMapperMetadata = {
                    label: this.new_metadata_label,
                    uri: this.new_metadata_uri,
                    slug: this.stringToSlug(this.new_metadata_label),
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
            this.new_metadata_label = '';
            this.new_metadata_uri = '';
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
            this.new_metadata_label = customMapperMeta.label;
            this.new_metadata_uri = customMapperMeta.uri;
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

<style scoped>
    .b-table {
        font-size: 0.875em;
    }
    .b-table td {
        padding: 0.5em 0.75em 0.4em 0.75em;
    }
    .add-link {
        font-size: 0.75em;
    }
</style>