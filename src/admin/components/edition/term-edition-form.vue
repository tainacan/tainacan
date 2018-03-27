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
       
        <b-field :addons="false">
            <label class="label">
                {{ $i18n.get('label_parent_term') }} 
                <help-button 
                        :title="$i18n.getHelperTitle('terms', 'parent_term')" 
                        :message="$i18n.getHelperMessage('terms', 'parent_term')"/>
            </label>
            <b-select
                    id="parent_term_select"
                    v-model="editForm.parent"
                    :class="{'is-empty': editForm.parent == 0}"
                    :placeholder="$i18n.get('instruction_select_a_parent_term')">
                <option
                        id="tainacan-select-parent-term"
                        v-for="(parentTerm, index) in parentTermsList"
                        :key="index"
                        :value="parentTerm.id">
                    {{ parentTerm.name }}
                </option>
            </b-select>
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
    data(){
        return {
            editForm: {},
            oldForm: {},
            closedByForm: false
        }
    }, 
    props: {
        index: '',
        editedTerm: Object,
        originalTerm: Object, 
        categoryId: ''
    },
    computed: {
        parentTermsList() {
            let parentTerms = [];
            parentTerms.push({name: this.$i18n.get('label_no_parent_term'), id: 0});
            for (let term of this.getTerms()) {
                if (term.id != this.editForm.id)
                    parentTerms.push({id: term.id, name: term.name});
            }
            return parentTerms;
        }
    },
    created() {
        
        this.editForm = this.editedTerm;     
        this.oldForm = JSON.parse(JSON.stringify(this.originalTerm));

    },
    beforeDestroy() {
        if (this.closedByForm) {
            this.editedTerm.saved = true;
        } else {
            this.oldForm.saved = this.editForm.saved;
            if (JSON.stringify(this.editForm) != JSON.stringify(this.oldForm)) 
                this.editedTerm.saved = false;
            else    
                this.editedTerm.saved = true;
        }
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
                        index: this.index, 
                        name: this.editForm.name,
                        description: this.editForm.description,
                        parent: this.editForm.parent
                    })
                    .then(() => {
                        this.editForm = {};
                        this.closedByForm = true;
                        this.$emit('onEditionFinished');
                    })
                    .catch((error) => {
                        this.$emit('onErrorFound');
                        this.$console.log(error);
                    });

            } else {
  
                this.updateTerm({
                        categoryId: this.categoryId, 
                        termId: term.id, 
                        index: this.index, 
                        name: this.editForm.name,
                        description: this.editForm.description,
                        parent: this.editForm.parent
                    })
                    .then(() => {
                        this.editForm = {};
                        this.closedByForm = true;
                        this.$emit('onEditionFinished');
                    })
                    .catch((error) => {
                        this.$emit('onErrorFound');
                        this.$console.log(error);
                    });
            }
        },
        cancelEdition() {
            this.closedByForm = true;
            this.$emit('onEditionCanceled');
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


