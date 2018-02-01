<template>
    <div>
        <el-select
                v-model="selected"
                multiple
                filterable
                remote
                reserve-keyword
                :remote-method="search"
                :loading="loading"
                 @change="onChecked()">
            <el-option
                    v-for="option,index in options"
                    :key="option.value"
                    :label="option.label"
                    :value="option.value"
                    ></el-option>
        </el-select>
    </div>
</template>

<script>
    import axios from '../../../js/axios/axios'

    export default {
        data(){
            return {
                selected:[],
                options: [],
                loading: false,
                collectionId: 0,
                inputValue: null
            }
        },
        props: {
            field: {
                type: Object
            }
        },
        methods: {
            onChecked() {
                this.$emit('blur');
                this.onInput(this.selected)
            },
            onInput($event) {
                this.inputValue = $event;
                this.$emit('input', this.inputValue);
            },
            search(query) {
                if (query !== '') {
                    this.loading = true;
                    this.options = [];
                    let collectionId = this.field.field.field_type_options.collection_id;
                    axios.get('/collection/'+collectionId+'/items')
                    .then( res => {
                        let result = [];
                        this.loading = false;
                        result = res.data.filter(item => {
                            return item.title.toLowerCase()
                                .indexOf(query.toLowerCase()) > -1;
                        });

                        for (let item of result) {
                            this.options.push({ label: item.title, value: item.id })
                        }
                    })
                    .catch(error => {
                        console.log(error);
                    });
                } else {
                    this.options = [];
                }
            }
        }
    }
</script>