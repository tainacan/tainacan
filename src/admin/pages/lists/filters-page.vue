<template>
    <div class="repository-level-page page-container">
        <tainacan-title 
                :bread-crumb-items="[{ path: '', label: this.$i18n.get('filters') }]"/>
        
        <template v-if="$userCaps.hasCapability('tnc_rep_edit_filters')">
            <filters-list/>
        </template>
        <template v-else>
            <section class="section">
                <div class="content has-text-grey has-text-centered">
                    <span class="icon">
                        <i class="tainacan-icon tainacan-icon-30px tainacan-icon-filters"/>
                    </span>
                    <p>{{ $i18n.get('info_can_not_edit_filters') }}</p>
                </div>
            </section>
        </template>
    </div>
</template>

<script>
import FiltersList from '../../components/lists/filters-list.vue';

export default {
    name: 'FiltersPage',
    data(){
        return {
        }
    },
    components: {
        FiltersList
    }
}
</script>

<style scoped>

</style>


