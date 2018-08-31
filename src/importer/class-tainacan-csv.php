<?php

namespace Tainacan\Importer;
use Tainacan;

class CSV extends Importer {

	public function __construct($attributes = array()) {
        parent::__construct($attributes);
        $this->items_repo = \Tainacan\Repositories\Items::get_instance();
		
		$this->set_default_options([
            'delimiter' => ',',
            'multivalued_delimiter' => '||',
            'encode' => 'utf8',
            'enclosure' => '"'
		]);
        
    }

    /**
     * alter the default options
     */
    public function set_option($key,$value){
        $this->default_options[$key] = $value;
    }

    /**
     * @inheritdoc
     */
    public function get_source_metadata(){
        if (($handle = fopen($this->tmp_file, "r")) !== false) {

            $rawColumns = fgetcsv($handle, 0, $this->get_option('delimiter'));
            $columns = [];

            if( $rawColumns ){
                foreach( $rawColumns as $index => $rawColumn ){
                  
                    if( strpos($rawColumn,'special_') === 0 ){
                        
                        if( $rawColumn === 'special_document' ){
                            $this->set_option('document_index', $index);
                        } else if( $rawColumn === 'special_attachments' ){
                            $this->set_option('attachment_index', $index);    
                        }
    
                    } else {
                        $columns[] = $rawColumn;
                    }
                }
    
                return $columns;
            }
        }

        return [];
    }

    /**
     * 
     * returns all header including special
     */
    public function raw_source_metadata(){

        if (($handle = fopen($this->tmp_file, "r")) !== false) {
            return fgetcsv($handle, 0, $this->get_option('delimiter'));
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function process_item( $index, $collection_definition ){
        $processedItem = [];
        $headers = $this->raw_source_metadata();

        $this->add_log('Proccessing item index ' . $index . ' in collection ' . $collection_definition['id'] );

        if (($handle = fopen($this->tmp_file, "r")) !== false) {
            $file = $handle;

        } else {
            $this->add_error_log(' Error reading the file ');
            return false;
            
        }

        if( $index === 0 ){

            // moves the pointer forward
            fgetcsv($file, 0, $this->get_option('delimiter'));

        } else {
            //get the pointer
            $csv_pointer= $this->get_transient('csv_pointer');

            if( $csv_pointer ){
                fseek($file, $csv_pointer);
            }
        }

        $this->add_transient('csv_last_pointer', ftell($file)); // add reference to post_process item in after_inserted_item()
		$values =  fgetcsv($file, 0, $this->get_option('delimiter'), $this->get_option('enclosure'));
        $this->add_transient('csv_pointer', ftell($file)); // add reference for insert

        if( count( $headers ) !== count( $values ) ){
            $string = (is_array($values)) ? implode('::', $values ) : $values;

            $this->add_error_log(' Mismatch count headers and row columns ');
            $this->add_error_log(' Headers count: ' . count( $headers ) );
            $this->add_error_log(' Values count: ' . count( $values ) );
            $this->add_error_log(' enclosure : ' .  $enclosure );
            $this->add_error_log(' Values string: ' . $string );
            return false;
        }
        
        foreach ( $collection_definition['mapping'] as $metadatum_id => $header) {
            $metadatum = new \Tainacan\Entities\Metadatum($metadatum_id);

            foreach ( $headers as $indexRaw => $headerRaw ) {
               if( $headerRaw === $header ){
                    $column = $indexRaw;
               }
            }
            
            if(!isset($column))
                continue;

            $valueToInsert = $this->handle_encoding( $values[ $column ] );

            $processedItem[ $header ] = ( $metadatum->is_multiple() ) ? 
                explode( $this->get_option('multivalued_delimiter'), $valueToInsert) : $valueToInsert;
        }
        
        $this->add_log('Success to proccess index: ' . $index  );
        return $processedItem;
    }

    /**
     * @inheritdoc
     */
    public function after_inserted_item( $inserted_item, $collection_index ) {
        $column_document = $this->get_option('document_index');
        $column_attachment = $this->get_option('attachment_index');

        if( !empty($column_document) || !empty( $column_attachment ) ){
            
			if (($handle = fopen($this->tmp_file, "r")) !== false) {
	            $file = $handle;
	        } else {
	            $this->add_error_log(' Error reading the file ');
	            return false;
	        }
			
			$csv_pointer= $this->get_transient('csv_last_pointer');
			fseek($file, $csv_pointer);
			
            $values = fgetcsv($file, 0, $this->get_option('delimiter'), $this->get_option('enclosure'));
            
            if( is_array($values) && !empty($column_document) ){
                $this->handle_document( $values[$column_document], $inserted_item);
            }

            if( is_array($values) && !empty($column_attachment) ){
                $this->handle_attachment( $values[$column_attachment], $inserted_item);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function get_source_number_of_items(){
        if ( isset($this->tmp_file) && file_exists($this->tmp_file) && ($handle = fopen($this->tmp_file, "r")) !== false) {
            $cont = 0;

            while ( ($data = fgetcsv($handle, 0, $this->get_option('delimiter')) ) !== false ) {
                $cont++; 
            }

            // does not count the header
            return $cont - 1;
        }
        return false;
    }

    
    public function options_form() {

		ob_start();
	   ?>
	   
		<div class="field">
			<label class="label"><?php _e('CSV Delimiter', 'tainacan'); ?></label>
			<span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							 <i class="mdi mdi-help-circle-outline" ></i>
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
		
		<div class="field">
			<label class="label"><?php _e('Multivalued metadata delimiter', 'tainacan'); ?></label>
			<span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							 <i class="mdi mdi-help-circle-outline" ></i>
						 </span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php _e('Multivalued metadata delimiter', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php _e('The character used to separate each value inside a cell with multiple values (e.g. ||). Note that the target metadatum must accept multiple values.', 'tainacan'); ?></p>
						</div>
					</div> 
			</span>
			<div class="control is-clearfix">
				<input class="input" type="text" name="multivalued_delimiter" value="<?php echo $this->get_option('multivalued_delimiter'); ?>">
			</div>
		</div>
		
		<div class="field">
			<label class="label"><?php _e('Enclosure', 'tainacan'); ?></label>
			<span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							 <i class="mdi mdi-help-circle-outline" ></i>
						 </span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php _e('Enclosure', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php _e('The character that wraps the content of each cell in your CSV. (e.g. ")', 'tainacan'); ?></p>
						</div>
					</div> 
			</span>
			<div class="control is-clearfix">
				<input class="input" type="text" name="enclosure" value="<?php echo $this->get_option('enclosure'); ?>">
			</div>
		</div>
		
		<div class="field">
			<label class="label"><?php _e('File Encoding', 'tainacan'); ?></label>
			<span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							 <i class="mdi mdi-help-circle-outline" ></i>
						 </span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php _e('File Encoding', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php _e('The encoding of the CSV file.', 'tainacan'); ?></p>
						</div>
					</div> 
			</span>
			<div class="control is-clearfix">
                <div class="select">
                    <select name="encode">
                        <option value="utf8" <?php selected($this->get_option('encode'), 'utf8'); ?> >UTF-8</option>
                        <option value="iso88591" <?php selected($this->get_option('encode'), 'iso88591'); ?> >ISO-88591</option>
                    </select>
                </div>
			</div>
		</div>
		
		<div class="field">
			<label class="label"><?php _e('Importing attachments', 'tainacan'); ?></label>
			<p>
			<?php echo nl2br(__("If you also have files you want to import, that are related to the items in your CSV, you can use some special columns in your csv to do so.\n
			There are two special columns you can use: <b>special_document</b>, which will set the Document of your item, and <b>special_attachments</b> to add one or many attachments.\n
			The values for the special_document must be prepended with 'url:'', 'file:'' or 'text:'. This will indicate the Document Type.\n
			The values for the special_attachments is just a list of files. If you want to add many attachments, use the separator you set in the Multivalued Delimiter option.\n
			In either case, you can point to a file using a full URL, or just a file name. In this last case, you should set the option below to tell Tainacan where to find the files in your server. You can then upload them directly (via FTP for example) and Taincan will add them to your items.\n
			", 'taincan')); ?>
			</p>
			<label class="label"><?php _e('Server path', 'tainacan'); ?></label>
			<span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							 <i class="mdi mdi-help-circle-outline" ></i>
						 </span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php _e('Server path', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php _e("When using CSV special field to add documents or attachments that you've uploaded to the server, inform the full path to the folder here (e.g. /home/user/files/)", 'tainacan'); ?></p>
						</div>
					</div> 
			</span>
			<div class="control is-clearfix">
				<input class="input" type="text" name="server_path" value="<?php echo $this->get_option('server_path'); ?>">
			</div>
		</div>
	   
	   <?php 
	   
	   
	   return ob_get_clean();

    }

    /**
     * get the encode option and return as expected
     */
    private function handle_encoding($string){

        switch( $this->get_option('encode') ){

            case 'utf8':
                return $string;

            case 'iso88591':
                return utf8_encode($string);

            default:
                return $string;
        }
    }

    /**
     * method responsible to insert the item document
     */
    private function handle_document($column_value, $item_inserted){
        $TainacanMedia = \Tainacan\Media::get_instance();
		$this->items_repo->disable_logs();

        if( strpos($column_value,'url:') === 0 ){
            $correct_value = trim(substr($column_value, 4));
            $item_inserted->set_document( $correct_value );
            $item_inserted->set_document_type( 'url' );

            if( $item_inserted->validate() ) {
                $item_inserted = $this->items_repo->update($item_inserted);
            }

        } else if( strpos($column_value,'text:') === 0 ){
            $correct_value = trim(substr($column_value, 5));
            $item_inserted->set_document( $correct_value );
            $item_inserted->set_document_type( 'text' );
            
            if( $item_inserted->validate() ) {
                $item_inserted = $this->items_repo->update($item_inserted);
            }

        } else if( strpos($column_value,'file:') === 0 ){
            $correct_value = trim(substr($column_value, 5));
            
            if( filter_var($correct_value, FILTER_VALIDATE_URL) ){
                $id = $TainacanMedia->insert_attachment_from_url($correct_value);

                if(!$id){
                    $this->add_error_log('Error in Document file imported from URL ' . $correct_value);
                    return false;
                }

                $item_inserted->set_document( $id );
                $item_inserted->set_document_type( 'attachment' );
                $this->add_log('Document file URL imported from ' . $correct_value);

                if( $item_inserted->validate() ) {
                    $item_inserted = $this->items_repo->update($item_inserted);
                }
            } else {
                $server_path_files = trailingslashit($this->get_option('server_path'));
                $id = $TainacanMedia->insert_attachment_from_file($server_path_files . $correct_value);

                if(!$id){
                    $this->add_error_log('Error in Document file imported from server ' . $correct_value);
                    return false;
                }

                $item_inserted->set_document( $id );
                $item_inserted->set_document_type( 'attachment' );
                $this->add_log('Document file in Server imported from ' . $correct_value);

                if( $item_inserted->validate() ) {
                    $item_inserted = $this->items_repo->update($item_inserted);
                }

            }

        }

        $thumb_id = $this->items_repo->get_thumbnail_id_from_document($item_inserted);
        
        if (!is_null($thumb_id)) {
            $this->add_log('Setting item thumbnail: ' . $thumb_id);
            set_post_thumbnail( $item_inserted->get_id(), (int) $thumb_id );
        }
		
		$this->items_repo->enable_logs();

        return true;

    }

    /**
     * method responsible to insert the item document
     */
    private function handle_attachment( $column_value, $item_inserted){
        $TainacanMedia = \Tainacan\Media::get_instance();
		
		$this->items_repo->disable_logs();

        $attachments = explode( $this->get_option('multivalued_delimiter'), $column_value);

        if( $attachments ){
            foreach( $attachments as $attachment ){

                if( filter_var($attachment, FILTER_VALIDATE_URL) ){
                    $id = $TainacanMedia->insert_attachment_from_url($attachment, $item_inserted->get_id());
                       
                    if(!$id){
                        $this->add_error_log('Error in Attachment file imported from URL ' . $attachment);
                        return false;
                    }

                    $this->add_log('Attachment file URL imported from ' . $attachment);

                    continue;
                } 

                $server_path_files = trailingslashit($this->get_option('server_path'));
                $id = $TainacanMedia->insert_attachment_from_file($server_path_files . $attachment, $item_inserted->get_id());

                if(!$id){
                    $this->add_log('Error in Attachment file imported from server ' . $attachment);
                    continue;
                }

                $this->add_log('Attachment file in Server imported from ' . $attachment);
            }
       }
	   
	   $this->items_repo->enable_logs();
	   
    }
}