<template>
    <el-input v-model="inputValue"></el-input>
</template>

<script>
    import { eventBus } from '../../../js/event-bus-web-components'

    export default {
        props: [
            'value'
        ],
        computed: {
            inputValue() {
                return this.value;
            }
        },
        methods: {
            validateObject () {
                return
                [
                    { required: this.required, message: this.message, trigger: 'blur' }
                ]
            },
            getErrors(){
                try{
                    return JSON.parse( this.errorsMsg );
                }catch(e){
                    console.log('invalid json error');
                }
                return this.errorsMsg;
            }
        }
    }
</script>

<style scoped>
    .multiple-inputs {
        display: flex;
    }
</style>
