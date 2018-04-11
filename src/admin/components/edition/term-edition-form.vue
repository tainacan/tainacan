<template>
    <form 
            id="termEditForm" 
            class="tainacan-form" 
            @submit.prevent="saveEdition(editForm)">    
        
        <b-field 
                :addons="false"
                :type="(formErrors.name != '' && formErrors.name != undefined) ? 'is-danger' : ''"
                :message="formErrors.name"> 
            <label class="label">
                {{ $i18n.get('label_name') }} 
                <span class="required-term-asterisk">*</span> 
                <help-button 
                        :title="$i18n.getHelperTitle('terms', 'name')" 
                        :message="$i18n.getHelperMessage('terms', 'name')"/>
            </label>
            <b-input 
                    v-model="editForm.name" 
                    name="name"/>
        </b-field>

        <b-field 
                :addons="false"
                :type="formErrors['description'] != '' && formErrors['description'] != undefined ? 'is-danger' : ''"
                :message="formErrors['description']">
            <label class="label">
                {{ $i18n.get('label_description') }}
                <help-button 
                        :title="$i18n.getHelperTitle('terms', 'description')" 
                        :message="$i18n.getHelperMessage('terms', 'description')"/>
            </label>
            <b-input 
                    type="textarea" 
                    name="description" 
                    v-model="editForm.description"/>
        </b-field>

        <div class="field is-grouped form-submit">  
            <div class="control">
                <button 
                        class="button is-outlined" 
                        @click.prevent="cancelEdition()" 
                        slot="trigger">
                    {{ $i18n.get('cancel') }}
                </button>
            </div>
            <div class="control">
                <button 
                        class="button is-success" 
                        type="submit">
                    {{ $i18n.get('save') }}
                </button>
            </div>
        </div>
    </form>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'TermEditionForm',
    data () {
        return { 
            formErrors: {}
        }
    },
    props: {
        editForm: Object,
        categoryId: ''
    },
    methods: {
        ...mapActions('category', [
            'sendTerm',
            'updateTerm'
        ]),
        ...mapGetters('category', [
            'getTerms'
        ]),
        saveEdition(term) {

            if (term.id == 'new') {
                this.sendTerm({
                        categoryId: this.categoryId, 
                        name: this.editForm.name,
                        description: this.editForm.description,
                        parent: this.editForm.parent
                    })
                    .then(() => {
                        this.editForm = {};
                        this.formErrors = {};
                        this.$emit('onEditionFinished');
                    })
                    .catch((errors) => {
                        for (let error of errors.errors) {
                            for (let field of Object.keys(error)){   
                                this.$set(this.formErrors, field, (this.formErrors[field] != undefined ? this.formErrors[field] : '') + error[field] + '\n');
                            }
                        }
                        this.$emit('onErrorFound');
                    });

            } else {
                this.updateTerm({
                        categoryId: this.categoryId, 
                        termId: this.editForm.id, 
                        name: this.editForm.name,
                        description: this.editForm.description,
                        parent: this.editForm.parent
                    })
                    .then(() => {
                        this.editForm.saved = true;
                        this.formErrors = {};
                        this.$emit('onEditionFinished');
                    })
                    .catch((errors) => {
                        for (let error of errors.errors) {
                            for (let field of Object.keys(error)){   
                                this.$set(this.formErrors, field, (this.formErrors[field] != undefined ? this.formErrors[field] : '') + error[field] + '\n');
                            }
                        }
                        this.$emit('onErrorFound');
                    });
            }
        },
        cancelEdition() {
            this.$emit('onEditionCanceled', this.editForm);
        },
    }
}
</script>

<style lang="scss" scoped>

    @import "../../scss/_variables.scss";
 
    form {
        padding: 1.0em 2.0em;
        border-top: 1px solid $draggable-border-color;
        border-bottom: 1px solid $draggable-border-color;
        margin-top: 1.0em;
    }

</style>


