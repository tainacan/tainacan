<?php
namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFilterType
 */
class TaxonomyCheckbox extends Filter_Type {

    function __construct(){
        $this->set_name( __('Taxonomy Checkbox List', 'tainacan') );
        $this->set_supported_types(['term']);
        $this->set_component('tainacan-filter-taxonomy-checkbox');
        $this->set_form_component('tainacan-filter-form-taxonomy-checkbox');
        $this->set_use_input_placeholder(false);
        $this->set_default_options([
            'max_view_more_pages' => 0,
            'show_hierarchy_path' => 'no'
        ]);
        $this->set_preview_template('
            <div>
                <div>
                    <p class="has-text-dark">'. __('Selected values', 'tainacan') . ': </p> 
                    <div class="field selected-tags is-grouped-multiline is-grouped">
                        <div>
                            <div class="tags has-addons">
                                <span class="tag"><span>'. __('Value', 'tainacan') . ' 21</span></span> 
                                <a class="tag is-delete"></a>
                            </div>
                        </div>
                        <div>
                            <div class="tags has-addons">
                                <span class="tag"><span>'. __('Value', 'tainacan') . ' 7</span></span> 
                                <a class="tag is-delete"></a>
                            </div>
                        </div>
                    </div> 
                    <div>
                        <label class="b-checkbox checkbox" border="" style="padding-left: 8px;">
                            <input type="checkbox" value="option1">
                            <span class="check"></span>
                            <span class="control-label">'. __('Value', 'tainacan') . ' 1</span>
                        </label> 
                        <br>
                    </div>
                    <div>
                        <label class="b-checkbox checkbox" border="" style="padding-left: 8px;">
                            <input type="checkbox" checked value="option2">
                            <span class="check"></span> 
                            <span class="control-label">'. __('Value', 'tainacan') . ' 2</span>
                        </label> 
                    </div>
                    <div>
                        <label class="b-checkbox checkbox" border="" style="padding-left: 8px;">
                            <input type="checkbox" checked value="option3">
                            <span class="check"></span> 
                            <span class="control-label">'. __('Value', 'tainacan') . ' 3</span>
                        </label> 
                    </div>
                </div> 
                <a class="add-new-term">'. __('View all', 'tainacan') . '</a>
            </div>
        ');
    }

    /**
     * @inheritdoc
     */
    public function get_form_labels(){
        return [
            'max_view_more_pages' => [
                'title' => __( 'Max "View more" attempts', 'tainacan' ),
                'description' => __( 'How many times "View more" may load another set of filter values inline before switching to "View all". Use 0 for "View all" only (default).', 'tainacan' ) . ' ' . __( 'Not used for hierarchical taxonomies.', 'tainacan' ),
            ],
            'show_hierarchy_path' => [
                'title' => __( 'Show hierarchy path', 'tainacan' ),
                'description' => __( 'Displays the parent terms before a child term in the list of options, as in "Parent > Child > Term". Child terms are listed here once they are selected, for example through "View all".', 'tainacan' ),
            ]
        ];
    }

    /**
     * @param \Tainacan\Entities\Filter $filter
     * @return array|bool true if is valid or array if has error
     */
    public function validate_options(\Tainacan\Entities\Filter $filter) {
        $parent_validation = parent::validate_options($filter);
        if ( is_array($parent_validation) )
            return $parent_validation;

        if ( !in_array($filter->get_status(), apply_filters('tainacan-status-require-validation', ['publish','future','private'])) )
            return true;

        $show_hierarchy_path = $this->get_option('show_hierarchy_path');
        if ( !empty($show_hierarchy_path) && !in_array($show_hierarchy_path, ['yes', 'no'], true) )
            return ['show_hierarchy_path' => __('"Show hierarchy path" must be "yes" or "no"', 'tainacan')];

        $max_view_more_pages = $this->get_option('max_view_more_pages');
        if ( $max_view_more_pages === '' || $max_view_more_pages === null )
            return true;

        if ( !is_numeric($max_view_more_pages) || intval($max_view_more_pages) < 0 )
            return ['max_view_more_pages' => __('"Max View more pages" must be a non-negative integer', 'tainacan')];

        return true;
    }
}