<?php

/**
 * @author: MediaLab-UFG(Eduardo Humberto).
 * Term Exporter
 *
 * Class to export files CSV with terms
 *
 */

namespace Tainacan\Exporter;
use Tainacan;
use Tainacan\Entities;
use Tainacan\Repositories;

class Term_Exporter extends Exporter {

    protected $steps = [
        [
            'name' => 'Export Terms',
            'progress_label' => 'Exporting terms',
            'callback' => 'exporting_terms'
        ]
    ];

    public function __construct($attributes = array()){
        parent::__construct($attributes);
        $this->set_accepted_mapping_methods('any'); // set all method to mapping
        $this->accept_no_mapping = true;

        $this->set_default_options([
            'delimiter' => ',',
            'enclosure' => '"'
        ]);
    }

    /**
     * When exporter is finished, gets the final output
     */
    public function get_output() {
        $files = $this->get_output_files();

        if ( is_array($files) && isset($files['termcsvexporter.csv'])) {
            $file = $files['termcsvexporter.csv'];

            $message = __('Your CSV file is ready! Access it in the link below:', 'tainacan');
            $message .= '<br/><br/>';
            $message .= '<a href="' . $file['url'] . '">Download</a>';

            return $message;

        } else {
            $this->add_error_log('Output file not found! Maybe you need to correct the permissions of your upload folder');
        }
    }

    function str_putcsv($item, $delimiter = ',', $enclosure = '"') {
        // Open a memory "file" for read/write...
        $fp = fopen('php://temp', 'r+');

        fputcsv($fp, $item, $delimiter, $enclosure);
        rewind($fp);
        //Getting detailed stats to check filesize:
        $fstats = fstat($fp);
        $data = fread($fp, $fstats['size']);
        fclose($fp);
        return rtrim($data, "\n");
    }

    public function options_form() {
        ob_start();
        ?>
        <div class="field">
            <label class="label"><?php _e('CSV Delimiter', 'tainacan'); ?></label>
            <span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							<i class="tainacan-icon tainacan-icon-help" ></i>
						</span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php _e('CSV Delimiter', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php _e('The character used to separate each column in your CSV (e.g. , or ;)', 'tainacan'); ?></p>
						</div>
					</div>
				</span>
            <div class="control is-clearfix">
                <input class="input" type="text" name="delimiter" value="<?php echo $this->get_option('delimiter'); ?>">
            </div>
        </div>

        <div class="field export_term_csv_taxonomies">
            <label class="label"><?php _e('Target taxonomy:', 'tainacan'); ?></label>
            <span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							<i class="tainacan-icon tainacan-icon-help" ></i>
						</span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php _e('Existing Taxonomy', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php _e('Inform the taxonomy you want to export the terms', 'tainacan'); ?></p>
							<p><?php _e('Select a taxonomy.', 'tainacan'); ?></p>
						</div>
					</div>
				</span>
            <div class="control is-clearfix">
                <div class="select">
                    <select name="select_taxonomy" class="select_taxonomy">
                        <?php
                        $Tainacan_Taxonomies  = \Tainacan\Repositories\Taxonomies::get_instance();
                        $taxonomies  = $Tainacan_Taxonomies->fetch( ['nopaging' => true], 'OBJECT' );
                        foreach( $taxonomies as $taxonomie) {
                            ?>
                            <option value="<?php echo $taxonomie->get_db_identifier();?>"><?php echo $taxonomie->get_name() ?> </option>
                            <?php
                        }
                        ?>
                    </select>

                </div>

            </div>

        </div>

        <?php
        return ob_get_clean();
    }

    public function process_item($index, $collection_definition) {
        return true;
    }

    /**
     * 
     */
    public function exporting_terms(){

        if ( $this->get_option('select_taxonomy') == '' ) {
            $this->abort();
            $this->add_error_log('No taxonomy selected');
            return false;
        }

        $tax = new Entities\Taxonomy($this->get_option('select_taxonomy'));
        $term_repo = Repositories\Terms::get_instance();

        $this->get_terms_recursively( $term_repo, $tax );
    }

    /**
     * @param $taxonomy the taxonomy to fetch the terms
     * @param $parent int the id of term father
     *
     * @return string
     */
    public function get_terms_recursively( $term_repo, $taxonomy, $parent = 0, $level = 0 ){
        $terms = $term_repo->fetch([ 'parent' => $parent ], $taxonomy->get_id());

        if( $terms && sizeof($terms) > 0 ){
            $level++;

            foreach ( $terms as $term ) {
                $line = [ $term->get_name(), $term->get_description() ];

               for ($i =0; $i < $level; $i++){
                   array_unshift($line, "" );
               }

                $line_string = $this->str_putcsv($line, $this->get_option('delimiter'), $this->get_option('enclosure'));
                $this->append_to_file('csvvocabularyexporter.csv', $line_string."\n");

                $this->get_terms_recursively($term_repo, $taxonomy, $term->get_id(), $level);
            }
        }
    }
}