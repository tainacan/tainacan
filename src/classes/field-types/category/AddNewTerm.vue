<template>
    <div>
         <span>
             <a
                class="button"
                @click="showForm = !showForm"><b-icon 
                size="is-small" 
                icon="plus"/>&nbsp;{{ $i18n.get('label_new_term') }}</a>
         </span>
        <div class="columns">
            <transition name="fade">

                <section
                        v-if="showForm"
                        class="column is-one-third"
                        style="padding-left: 0px;">

                    <b-field :label="$i18n.get('label_name')">
                        <b-input
                                :class="{'has-content': name != undefined && name != ''}" 
                                v-model="name"/>
                    </b-field>

                    <b-field :label="$i18n.get('label_parent_term')">
                        <b-select
                                v-model="parent">
                            <option 
                                    :value="0" 
                                    selected> ---{{ $i18n.get('label_parent_term') }}--- </option>
                            <option
                                    v-for="(option,index) in options"
                                    :key="index"
                                    :value="option.id"
                                    v-html="setSpaces( option.level ) + option.name"/>
                        </b-select>
                    </b-field>

                    <a
                            class="button is-primary"
                            @click="save">{{ $i18n.get('save') }}</a>
                </section>

            </transition>
        </div>
    </div>

</template>
<script>
    import { tainacan as axios } from '../../../js/axios/axios'

    export default {
        data(){
            return {
                name: '',
                parent: 0,
                showForm: false,
                field_id: this.field.field.id
            }
        },
        props: {
            id: String,
            item_id: [Number,String],
            field: [Number,String],
            taxonomy_id: [Number,String],
            value:[ Array, Boolean, Number ],
            options: {
                type: Array
            }
        },
        methods: {
            setSpaces( level ){
                let result = '';
                let space =  '&nbsp;&nbsp;'

                for(let i = 0;i < level; i++)
                    result += space;

                return result;
            },
            save(){
                if( this.name.trim() === ''){
                    this.$toast.open({
                        duration: 2000,
                        message: this.$i18n.get('info_name_is_required'),
                        position: 'is-bottom',
                        type: 'is-danger'
                    })
                } else {
                    const instance = this;

                    axios.post(`/taxonomy/${this.taxonomy_id}/terms`, {
                        name: this.name,
                        parent: this.parent
                    })
                    .then( res => {
                        instance.name = '';
                        instance.parent = 0;

                        if( res.data && res.data.id || res.id ){
                            let id = ( res.id ) ? res.id : res.data.id;
                            let val = this.value;

                            if( !Array.isArray( val ) && this.field.field.multiple === 'no' ){
                                axios.patch(`/item/${this.item_id}/metadata/${this.field_id}`, {
                                    values: id,
                                }).then(() => {
                                    instance.$emit('newTerm', id);
                                })
                            } else {
                                val = ( val ) ? val : [];
                                val.push( id );
                                axios.patch(`/item/${this.item_id}/metadata/${this.field_id}`, {
                                    values: val,
                                }).then( () => {
                                    instance.$emit('newTerm', val);
                                })
                            }
                        }
                    });
                }

            }
        }
    }
</script>
<style scoped>
    button{
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
    }
</style>