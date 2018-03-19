<template>
    <div>
        <div class="block" v-for="option,index in getOptions( 0 )">
            <b-radio
                    :id="id"
                    :style="{ paddingLeft: (option.level * 20)  + 'px' }"
                    :key="index"
                    v-model="checked"
                    @input="onChecked(option)"
                    :label="option"
                    :native-value="option"
                    border>
                {{ option.name }}
            </b-radio>
        </div>
    </div>
</template>

<script>

    export default {
        data(){
            return {
                checked:''
            }
        },
        props: {
            options: {
                type: Array
            },
            value: [ Number, String ]
        },
        methods: {
            getOptions( parent, level = 0 ){
                let result = [];
                if ( this.options ){
                    for( let term of this.options ){
                        if( term.parent == parent ){
                            term['level'] = level;
                            result.push( term );
                            const levelTerm =  level + 1;
                            const children =  this.getOptions( term.term_id, levelTerm);
                            result = result.concat( children );
                        }
                    }
                }
                return result;
            },
            onChecked(option) {
                this.$emit('blur');
                console.log( this.checked );
                this.onInput(this.checked)
            },
            onInput($event) {
                this.inputValue = $event;
                this.$emit('input', this.inputValue);
            }
        }
    }
</script>