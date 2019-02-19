<template>
    <div>
        <p 
                v-if="value instanceof Array ? value.length > 0 : (value != undefined && value != '')"
                class="has-text-gray">
            {{ $i18n.get('label_selected_terms') + ' :' }}
        </p>
        <b-field
                v-if="value instanceof Array ? value.length > 0 : (value != undefined && value != '')"
                grouped
                group-multiline
                class="selected-tags">
            <div
                    v-for="(term, index) in (value instanceof Array ? value : [value])"
                    :key="index">
                <b-tag
                        attached
                        closable
                        @close="value.splice(index, 1)">
                    {{ selectedTagsName[term] }}
                </b-tag>
            </div>
            <div 
                    v-if="isSelectedTermsLoading" 
                    class="control has-icons-right is-loading is-clearfix" />
        </b-field>
       
        <div   
                v-for="(option, index) in options"
                :key="index">
            <b-checkbox
                    :disabled="disabled"
                    :id="id"
                    :style="{ paddingLeft: (option.level * 30) + 'px' }"
                    :key="index"
                    v-model="checked"
                    @input="onChecked(option)"
                    :native-value="option.id"
                    border>
                {{ option.name }}
            </b-checkbox>
            <br>
        </div>
    </div>
</template>

<script>
    import { tainacan as axios } from '../../../js/axios/axios';

    export default {
        created(){
            if( this.value && this.value.length > 0)
                this.checked = this.value;
        },
        data(){
            return {
                checked: [],
                selectedTagsName: {},
                isSelectedTermsLoading: false,
            }
        },
        watch: {
            value( val ){
                this.checked = val;
                this.fetchSelectedLabels();
            }
        },
        props: {
            options: {
                type: Array
            },
            value: [ Number, String, Array ],
            disabled: false,
            taxonomyId: Number
        },
        methods: {
            onChecked() {
                this.$emit('blur');
                this.onInput(this.checked);
            },
            onInput($event) {
                this.inputValue = $event;
                this.$emit('input', this.inputValue);
            },
            fetchSelectedLabels() {

                if (this.value != null && this.value != undefined) {

                    this.isSelectedTermsLoading = true;
                    let selected = this.value instanceof Array ? this.value : [this.value];

                    if (this.taxonomyId && selected.length > 0) {
                        for (const term of selected) {

                            if(!this.isSelectedTermsLoading){
                                this.isSelectedTermsLoading = true;
                            }

                            axios.get(`/taxonomy/${this.taxonomyId}/terms/${term}`)
                                .then((res) => {
                                    this.saveSelectedTagName(res.data.id, res.data.name);
                                    this.isSelectedTermsLoading = false;
                                })
                                .catch((error) => {
                                    this.$console.log(error);
                                    this.isSelectedTermsLoading = false;
                                });
                        }
                    } else {
                        this.isSelectedTermsLoading = false;
                    }
                }
            },
            saveSelectedTagName(value, label){
                if(!this.selectedTagsName[value]) {
                    this.$set(this.selectedTagsName, `${value}`, label);
                }
            }
        },
        mounted() {
            this.fetchSelectedLabels();
        }
    }
</script>

<style>
    .selected-tags {
        margin-top: 0.75rem;
        font-size: 0.75rem;
        position: relative;
    }
    .selected-tags .is-loading {
        margin-left: 2rem;
        margin-top: -0.4rem;
    }
    .selected-tags .is-loading::after {
        border: 2px solid #555758 !important;
        border-right-color: #dbdbdb !important;
        border-top-color: #dbdbdb !important;
    } 
</style>
