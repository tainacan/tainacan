<template>
    <item-submission-form 
            :collection-id="$root.collectionId"
            class="tainacan-item-submission-form" />
</template>

<script>
export default {
    name: "ItemSubmission"
}
</script>

<style lang="scss">

    // TAINACAN Variables
    @import "../admin/scss/_variables.scss";

    // Bulma imports
    @import "./scss/item-submission-basics.sass";

    // Buefy imports
    @import "../../../node_modules/buefy/src/scss/utils/_all.scss";
    @import "../../../node_modules/buefy/src/scss/components/_datepicker.scss";
    @import "../../../node_modules/buefy/src/scss/components/_checkbox.scss";
    @import "../../../node_modules/buefy/src/scss/components/_radio.scss";
    @import "../../../node_modules/buefy/src/scss/components/_tag.scss";
    @import "../../../node_modules/buefy/src/scss/components/_loading.scss";
    @import "../../../node_modules/buefy/src/scss/components/_dropdown.scss";
    @import "../../../node_modules/buefy/src/scss/components/_modal.scss";
    @import "../../../node_modules/buefy/src/scss/components/_dialog.scss";
    @import "../../../node_modules/buefy/src/scss/components/_notices.scss";
    @import "../../../node_modules/buefy/src/scss/components/_numberinput.scss";

    // Tainacan imports
    @import "../admin/scss/_modals.scss";
    @import "../admin/scss/_buttons.scss"; 
    @import "../admin/scss/_inputs.scss";
    @import "../admin/scss/_checkboxes.scss";
    @import "../admin/scss/_pagination.scss";
    @import "../admin/scss/_tags.scss";
    @import "../admin/scss/_notices.scss";
    @import "../admin/scss/_tabs.scss";
    @import "../admin/scss/_selects.scss";
    @import "../admin/scss/_dropdown-and-autocomplete.scss";
    @import "../admin/scss/_tooltips.scss";
    @import "../admin/scss/_control.scss";
    @import "../admin/scss/_tainacan-form.scss";
    @import "../admin/scss/_custom_variables.scss";

</style>