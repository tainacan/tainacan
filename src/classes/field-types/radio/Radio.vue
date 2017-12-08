<template>
    <div class="component">
        <label
                v-for="option in getOptions"
                :for="option.replace(' ','-') + '-checkbox'">
            <input
                    type="radio"
                    :id="option.replace(' ','-') + '-checkbox'"
                    :value="option"
                    v-model="checked"> {{ option }}<br>
        </label>
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
            name: {
                type: String
            },
            options: {
                type: String
            }
        },
        computed: {
            getOptions(){
                const values = ( this.options ) ? this.options.split("\n") : '';
                return values;
            }
        }
    }
</script>

<style scoped="">
    #postcustomstuff table input, #postcustomstuff table select, #postcustomstuff table textarea {
        width: auto;
        margin: 8px;
    }
</style>