<template>
    <div class="block">
        <el-date-picker
                v-model="valueDate"
                @blur="changeValue()"
                type="date"
                format="dd/MM/yyyy"
                value-format="dd/MM/yyyy"
                placeholder="Selecione a data...">
        </el-date-picker>
        <div class="demonstration">{{ valueDate }}</div>
    </div>
</template>

<script>
    import lang from 'element-ui/lib/locale/lang/pt-br'
    import locale from 'element-ui/lib/locale'

    locale.use(lang)

    export default {
        props: {
            name: { type: String },
            item_id: { type: Number },
            metadata_id: { type: Number },
            value: { type: [ String,Number ]  },
            errorsMsg: { type: [ String,Number ] },
        },
        data(){
          return {
              valueDate:''
          }
        },
        created(){
            this.getValue();
        },
        methods: {
            changeValue(){
                this.$emit('input', { item_id: this.item_id, metadata_id: this.metadata_id, values: event.target.value } );
            },
            getValue(){
                try{
                    let val = JSON.parse( this.value );
                    this.valueDate = val;
                }catch(e){
                    console.log('invalid json value');
                }
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