<?php
namespace Tainacan\Filter_Types;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Class TainacanFilterType
 */
class Checkbox extends Filter_Type {

    function __construct(){
        $this->set_name( __('Checkbox List', 'tainacan') );
        $this->set_supported_types(['string','long_string','item', 'control']);
        $this->set_component('tainacan-filter-checkbox');
        $this->set_form_component('tainacan-filter-form-checkbox');
        $this->set_use_input_placeholder(false);
        $this->set_default_options([
            'max_view_more_pages' => 0
        ]);
        $this->set_preview_template('
            <div>
                <div>
                    <p class="has-text-dark">'. __('Selected values', 'tainacan') . ': </p> 
                    <div class="field selected-tags is-grouped-multiline is-grouped">
                        <div>
                            <div class="tags has-addons">
                                <span class="tag"><span>'. __('Value', 'tainacan') . ' 2</span></span> 
                                <a class="tag is-delete"></a>
                            </div>
                        </div>
                        <div>
                            <div class="tags has-addons">
                                <span class="tag"><span>'. __('Value', 'tainacan') . ' 3</span></span> 
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
                'title' => __( 'Max "View more" pages', 'tainacan' ),
                'description' => __( 'How many times "View more" may load another page inline before switching to "View all". Use 0 for "View all" only (default).', 'tainacan' ),
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

        $max_view_more_pages = $this->get_option('max_view_more_pages');
        if ( $max_view_more_pages === '' || $max_view_more_pages === null )
            return true;

        if ( !is_numeric($max_view_more_pages) || intval($max_view_more_pages) < 0 )
            return ['max_view_more_pages' => __('"Max View more pages" must be a non-negative integer', 'tainacan')];

        return true;
    }
}