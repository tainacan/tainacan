<?php

namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFilterType
 */
class Custom_Numeric extends Filter_Type {

    function __construct(){
        $this->set_name( __('Custom Numeric', 'tainacan') );
        $this->set_supported_types(['float']);
        $this->set_component('tainacan-filter-custom-numeric');
        $this->set_script('
            var TainacanExtraFilters = TainacanExtraFilters ? TainacanExtraFilters : {};

            const TainacanFilterCustomNumeric = {
                name: "TainacanFilterCustomNumeric",
                props: {
                    filter: Object,
                    query: Object,
                    isRepositoryLevel: Boolean,
                    isUsingElasticSearch: Boolean,
                    isLoadingItems: Boolean
                },
                data: function() {
                    return {
                        collectionId: "",
                        metadatumId: "",
                        metadatumType: "",
                        filterTypeOptions: [],
                        value: null,
                        filterTypeOptions: [],
                        comparator: "="
                    }
                },
                computed: {
                    comparatorSymbol: function() {
                        switch(this.comparator) {
                            case "=": return "&#61;";
                            case "!=": return "&#8800;";
                            case ">": return "&#62;";
                            case ">=": return "&#8805;";
                            case "<": return "&#60;";
                            case "<=": return "&#8804;";
                            default: return "";
                        }
                    }
                },
                watch: {
                    "query.metaquery": function() {
                        this.updateSelectedValues();
                    },
                    "query.taxquery": function() {
                        this.updateSelectedValues();
                    }
                },
                mounted: function() { console.log(this.filter, this.isRepositoryLevel, this.filterTypeOptions) },
                created: function() {
                    this.collectionId = this.filter.collection_id ? this.filter.collection_id : this.collectionId;
                    this.metadatumId = this.filter.metadatum.metadatum_id ? this.filter.metadatum.metadatum_id : this.metadatumId;
                    this.filterTypeOptions = this.filter.filter_type_options ? this.filter.filter_type_options : this.filterTypeOptions;
                    this.metadatumType = this.filter.metadatum.metadata_type_object && this.filter.metadatum.metadata_type_object.className ? this.filter.metadatum.metadata_type_object.className : this.metadatumType;
                },
                methods: {
                    updateSelectedValues: function(){
                        if ( !this.query || !this.query.metaquery || !Array.isArray( this.query.metaquery ) )
                            return false;
        
                        let index = this.query.metaquery.findIndex(function(newMetadatum) { return newMetadatum.key == this.metadatumId } );
                        
                        if ( index >= 0){
                            let metadata = this.query.metaquery[ index ];
                            
                            if ( metadata.value && metadata.value.length > 0)
                                this.value = Array.isArray(metadata.value) ? Number(metadata.value[0]) : Number(metadata.value);
        
                            if ( metadata.compare)
                                this.comparator = metadata.compare;
        
                            if (this.value != undefined)
                                this.$emit("sendValuesToTags", { label: this.comparator + " " + this.value, value: this.value });
        
                        } else {
                            this.value = null;
                        }
        
                    },
                    emit: function() {

                        if ( this.value === null || this.value === "")
                            return;
                            
                        this.$emit("input", {
                            filter: "numeric",
                            compare: this.comparator,
                            metadatum_id: this.metadatumId,
                            collection_id: this.collectionId,
                            value: this.value,
                            type: "NUMERIC"
                        });
        
                        this.$emit("sendValuesToTags", { label: this.comparator + " " + this.value, value: this.value });
                        
                    },
                    onChangeComparator: function(newComparator) {
                        this.comparator = newComparator;
                        this.emit();
                    }
                },
                template: `
                <div class="numeric-filter-container">
                <b-dropdown
                       :mobile-modal="true"
                       @input="onChangeComparator($event)"
                       aria-role="list"
                       trap-focus>
                   <button
                           class="button is-white"
                           slot="trigger">
                       <span class="icon is-small">
                           <i v-html="comparatorSymbol" />
                       </span>
                       <span class="icon">
                           <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown" />
                       </span>
                   </button>
                   <b-dropdown-item
                           role="button"
                           value="="
                           aria-role="listitem">
                       &#61;&nbsp; {{ $i18n.get("is_equal_to") }}
                   </b-dropdown-item>
                   <b-dropdown-item
                           role="button"
                           value="!="
                           aria-role="listitem">
                       &#8800;&nbsp; {{ $i18n.get("is_not_equal_to") }}
                   </b-dropdown-item>
                   <b-dropdown-item
                           role="button"
                           value=">"
                           aria-role="listitem">
                       &#62;&nbsp; {{ $i18n.get("greater_than") }}
                   </b-dropdown-item>
                   <b-dropdown-item
                           role="button"
                           value=">="
                           aria-role="listitem">
                       &#8805;&nbsp; {{ $i18n.get("greater_than_or_equal_to") }}
                   </b-dropdown-item>
                   <b-dropdown-item
                           role="button"
                           value="<"
                           aria-role="listitem">
                       &#60;&nbsp; {{ $i18n.get("less_than") }}
                   </b-dropdown-item>
                   <b-dropdown-item
                           role="button"
                           value="<="
                           aria-role="listitem">
                       &#8804;&nbsp; {{ $i18n.get("less_than_or_equal_to") }}
                   </b-dropdown-item>
               </b-dropdown>
       
               <b-numberinput
                       size="is-small"
                       :step="Number(filterTypeOptions.step)"
                       @input="emit()"
                       v-model="value"/>
           </div>
                `
            }
            TainacanExtraFilters["tainacan-filter-custom-numeric"] = TainacanFilterCustomNumeric;
        ');
        $this->set_use_max_options(false);
        $this->set_preview_template('
            <div>
                <div>
                    <div class="numeric-filter-container">
                        <div class="dropdown is-active">
                            <div role="button" class="dropdown-trigger">
                                <button class="button is-white">
                                    <span class="icon is-small">
                                        <i>=</i>
                                    </span>
                                    <span class="icon">
                                        <i class="tainacan-icon tainacan-icon-20px tainacan-icon-arrowdown"></i>
                                    </span>
                                </button>
                            </div>
                            <div class="background" style="display: none;"></div>
                            <div class="dropdown-menu" style="display: none;">
                                <div role="list" class="dropdown-content">
                                    <a class="dropdown-item is-active">=&nbsp; ' . __('Equal', 'tainacan') .'</a>
                                    <a class="dropdown-item">≠&nbsp; '. __('Not equal', 'tainacan') .'</a>
                                    <a class="dropdown-item">&gt;&nbsp; '. __('Greater than', 'tainacan') .'</a>
                                    <a class="dropdown-item">≥&nbsp; '. __('Greater than or equal to', 'tainacan') .'</a>
                                    <a class="dropdown-item">&lt;&nbsp; '. __('Less than', 'tainacan') .'</a>
                                    <a class="dropdown-item">≤&nbsp;  '. __('Less than or equal to', 'tainacan') .'</a>
                                </div>
                            </div>
                        </div>
                    <div class="b-numberinput field is-grouped">
                        <p class="control">
                            <button type="button" class="button is-primary is-small">
                                <span class="icon is-small">
                                    <i class="mdi mdi-minus"></i>
                                </span>
                            </button>
                        </p>
                        <div class="control is-small is-clearfix">
                            <input type="number" step="0.01" class="input is-small" value="1.5">
                        </div>
                        <p class="control">
                            <button type="button" class="button is-primary is-small">
                                <span class="icon is-small">
                                    <i class="mdi mdi-plus"></i>
                                </span>
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        ');
    }

    /**
     * @param $filter
     * @return string
     * @internal param $metadatum
     */
    public function render( $filter ){
        return '';
    }


    /**
     * @param \Tainacan\Entities\Filter $filter
     * @return array|bool true if is validate or array if has error
     */
    public function validate_options(\Tainacan\Entities\Filter $filter) {
        if ( !in_array($filter->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;

        return true;
    }
}