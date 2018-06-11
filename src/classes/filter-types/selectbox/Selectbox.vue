<template>
    <div class="block">
        <b-select
                :id = "id"
                :loading = "isLoading"
                v-model = "selected"
                @input = "onSelect($event)"
                :placeholder="$i18n.get('label_selectbox_init')"
                expanded
                :class="{'is-empty': selected == undefined || selected == ''}">
            <option value="">{{ $i18n.get('label_selectbox_init') }}...</option>
            <option
                    v-for="(option, index) in options"
                    :key="index"
                    :label="option.label"
                    :value="option.value">{{ option.label }}</option>
        </b-select>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios';
    import { filter_type_mixin } from '../filter-types-mixin'

    export default {
        created(){
            this.collection = ( this.collection_id ) ? this.collection_id : this.filter.collection_id;
            this.metadatum = ( this.metadatum_id ) ? this.metadatum_id : this.filter.metadatum.metadatum_id;
            const vm = this;

            let in_route = '/collection/' + this.collection + '/metadata/' +  this.metadatum;

            if(this.isRepositoryLevel || this.collection == 'filter_in_repository'){
                in_route = '/metadata/'+ this.metadatum;
            }

            axios.get(in_route)
                .then( res => {
                    let result = res.data;
                    if( result && result.metadatum_type ){
                        vm.metadatum_object = result;
                        vm.type = result.metadatum_type;
                        vm.loadOptions();
                    }
                })
                .catch(error => {
                    this.$console.error(error);
                });
        },
        props: {
            isRepositoryLevel: Boolean,
        },
        data(){
            return {
                isLoading: false,
                options: [],
                type: '',
                collection: '',
                metadatum: ''
            }
        },
        mixins: [filter_type_mixin],
        computed: {
            selected() {
                if ( this.query && this.query.metaquery && Array.isArray( this.query.metaquery ) ) {

                    let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key === this.metadatum );
                    if ( index >= 0){
                        let metadata = this.query.metaquery[ index ];
                        return metadata.value;
                    }
                }
                return undefined;
            }
        },
        methods: {
            loadOptions(){
                this.isLoading = true;

                let promise = null;
                promise = this.getValuesPlainText( this.metadatum, null, this.isRepositoryLevel );

                promise.then(() => {
                    this.isLoading = false;
                })
                .catch( error => {
                    this.$console.error('error select', error );
                    this.isLoading = false;
                });
            },
            onSelect(value){
                this.$emit('input', {
                    filter: 'selectbox',
                    metadatum_id: this.metadatum,
                    collection_id: ( this.collection_id ) ? this.collection_id : this.filter.collection_id,
                    value: ( value ) ? value : ''
                });
            },
            selectedValues(){
                if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                    return false;

                let index = this.query.metaquery.findIndex(newMetadatum => newMetadatum.key === this.metadatum );
                if ( index >= 0){
                    let metadata = this.query.metaquery[ index ];
                    this.selected = metadata.value;
                } else {
                    return false;
                }
            }
        }
    }
</script>
