<template>
    <div class="table-container">
        <div class="table-wrapper">
            <table class="tainacan-table is-narrow">
                <thead>
                    <tr>
                        <!-- Name -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_name') }}</div>
                        </th>
                        <!-- Description -->
                        <th>
                            <div class="th-wrap">{{ $i18n.get('label_description') }}</div>
                        </th>
                        <!-- Actions -->
                        <th class="actions-header">
                            &nbsp;
                            <!-- nothing to show on header for actions cell-->
                        </th>
                    </tr>
                </thead>
                <tbody v-if="!isLoading">
                    <template v-for="(mapper, index) of mappers">
                        <tr :key="index">
                            <!-- Name -->
                            <td
                                    class="column-default-width column-main-content"
                                    :label="$i18n.get('label_name')"
                                    :aria-label="$i18n.get('label_name') + ': ' + mapper.name">
                                <p
                                        v-tooltip="{
                                            delay: {
                                                shown: 500,
                                                hide: 120,
                                            },
                                            content: mapper.name,
                                            autoHide: false,
                                            popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                            placement: 'auto-start'
                                        }">
                                    {{ mapper.name }}
                                </p>
                            </td>
                            <!-- Description -->
                            <td
                                    class="table-creation column-large-width"
                                    :label="$i18n.get('label_description')"
                                    :aria-label="$i18n.get('label_description') + ': ' + mapper.description">
                                <p
                                        v-tooltip="{
                                            delay: {
                                                shown: 500,
                                                hide: 120,
                                            },
                                            content: mapper.description,
                                            autoHide: false,
                                            popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                            placement: 'auto-start'
                                        }"
                                        v-html="mapper.description"/>
                            </td>
                            
                            <!-- Actions -->
                            <td  
                                    class="actions-cell column-default-width" 
                                    :label="$i18n.get('label_actions')">
                                <div class="actions-container">
                                    <b-switch 
                                            v-model="mapper.enabled"
                                            size="is-small" />
                                    <a 
                                            id="button-edit" 
                                            :aria-label="$i18n.get('edit')" 
                                            @click.prevent.stop="goToMapperEditionPage(mapper.slug)">                      
                                        <span 
                                                v-tooltip="{
                                                    content: $i18n.get('edit'),
                                                    autoHide: true,
                                                    popperClass: ['tainacan-tooltip', 'tooltip', 'tainacan-repository-tooltip'],
                                                    placement: 'auto'
                                                }"
                                                class="icon">
                                            <i class="tainacan-icon tainacan-icon-1-25em tainacan-icon-edit"/>
                                        </span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'MappersList',
        props: {
            isLoading: false,
            mappers: Array
        },
        methods: {
            goToMapperEditionPage(mapperSlug) {
                this.$router.push(this.$routerHelper.getMapperEditPath(mapperSlug));
            }
        },
    }
</script>

<style scoped lang="scss">

.table-container .table-wrapper table.tainacan-table tbody tr {
    cursor: default;
}

</style>
