<template>
    <div>
        <div class="block">
            <b-select
                    :disabled="disabled"
                    :id="id"
                    v-model="selected"
                    @input="emitChange()"
                    :placeholder="$i18n.get('label_select_taxonomy')" 
                    expanded>
                <option
                        v-for="(option, index) in options"
                        :key="index"
                        :value="option.id"
                        v-html="setSpaces( option.level ) + option.name"/>
            </b-select>
        </div>
    </div>
</template>

<script>

    export default {
        created(){
            if( this.value )
                this.selected = this.value;
        },
        data(){
            return {
                selected: ''
            }
        },
        watch: {
            value( val ){
                this.selected = val;
            }
        },
        props: {
            id: String,
            options: {
                type: Array
            },
            value: [ Number, String, Array ],
            disabled: false,
        },
        methods: {
            emitChange() {
                this.$emit('input', this.selected);
                this.$emit('blur');
            },
            setSpaces( level ){
                let result = '';
                let space =  '&nbsp;&nbsp;'

                for(let i = 0;i < level; i++)
                    result += space;

                return result;
            }
        }
    }
</script>