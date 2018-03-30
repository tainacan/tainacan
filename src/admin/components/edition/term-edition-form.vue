<template>
    <form 
            id="termEditForm" 
            class="tainacan-form" 
            @submit.prevent="saveEdition(editForm)">    
        
        <b-field 
                :addons="false"
                :type="editForm.name == '' ? 'is-danger' : ''" 
                :message="editForm.name == '' ? $i18n.get('info_name_is_required') : ''"> 
            <label class="label">
                {{ $i18n.get('label_name') }} 
                <span 
                        class="required-term-asterisk" 
                        :class="editForm.name == '' ? 'is-danger' : ''">*</span> 
                <help-button 
                        :title="$i18n.getHelperTitle('terms', 'name')" 
                        :message="$i18n.getHelperMessage('terms', 'name')"/>
            </label>
            <b-input 
                    v-model="editForm.name" 
                    name="name"/>
        </b-field>

        <b-field :addons="false">
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
                        type="submit"
                        :disabled="editForm.name == '' || editForm.name == undefined">
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
                        this.$emit('onEditionFinished');
                    })
                    .catch((error) => {
                        this.$emit('onErrorFound');
                        this.$console.log(error);
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
                        this.$emit('onEditionFinished');
                    })
                    .catch((error) => {
                        this.$emit('onErrorFound');
                        this.$console.log(error);
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


