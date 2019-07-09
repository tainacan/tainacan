<?php

namespace Tainacan\Importer;
use Tainacan;
use Tainacan\Entities;

class CSV extends Importer {
    public function __construct($attributes = array()) {
        parent::__construct($attributes);
        $this->items_repo = \Tainacan\Repositories\Items::get_instance();
        $this->set_default_options([
            'delimiter' => ',',
            'multivalued_delimiter' => '||',
            'encode' => 'utf8',
            'enclosure' => ''
        ]);
    }

    /**
     * alter the default options
     */
    public function set_option($key,$value) {
        $this->default_options[$key] = $value;
    }

    /**
     * @inheritdoc
     */
    public function get_source_metadata() {
        if (($handle = fopen($this->tmp_file, "r")) !== false) {
            if( $this->get_option('enclosure') && strlen($this->get_option('enclosure')) > 0 ) {
                $rawColumns = $this->handle_enclosure( $handle );
            } else {
                $rawColumns = fgetcsv($handle, 0, $this->get_option('delimiter'));
            }

            $columns = [];

            if( $rawColumns ) {
                foreach( $rawColumns as $index => $rawColumn ) {
                    if( strpos($rawColumn,'special_') === 0 ) {
                        if( $rawColumn === 'special_document' ) {
                            $this->set_option('document_index', $index);
                        } else if( $rawColumn === 'special_attachments' ) {
                            $this->set_option('attachment_index', $index);
                        } else if( $rawColumn === 'special_item_status' ) {
                            $this->set_option('item_status_index', $index);
                        } else if( $rawColumn === 'special_item_id' ) {
                            $this->set_option('item_id_index', $index);
                        } else if( $rawColumn === 'special_comment_status' ) {
                            $this->set_option('item_comment_status_index', $index);
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
    public function raw_source_metadata() {
        if (($handle = fopen($this->tmp_file, "r")) !== false) {
            if( $this->get_option('enclosure') && strlen($this->get_option('enclosure')) > 0 ){
                return $this->handle_enclosure( $handle );
            } else {
                return fgetcsv($handle, 0, $this->get_option('delimiter'));
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function process_item( $index, $collection_definition ) {
        $processedItem = [];
        $headers = $this->raw_source_metadata();

        $this->add_log('Proccessing item index ' . $index . ' in collection ' . $collection_definition['id'] );

        if (($handle = fopen($this->tmp_file, "r")) !== false) {
            $file = $handle;
        } else {
            $this->add_error_log(' Error reading the file ');
            return false;
        }

        if( $index === 0 ) {
            // moves the pointer forward
            fgetcsv($file, 0, $this->get_option('delimiter'));
        } else {
            //get the pointer
            $csv_pointer= $this->get_transient('csv_pointer');
            if( $csv_pointer ) {
                fseek($file, $csv_pointer);
            }
        }

        $this->add_transient('csv_last_pointer', ftell($file)); // add reference to post_process item in after_inserted_item()

        if( $this->get_option('enclosure') && strlen($this->get_option('enclosure')) > 0 ) {
            $values = $this->handle_enclosure( $file );
        } else {
            $values = fgetcsv($file, 0, $this->get_option('delimiter'));
        }

        $this->add_transient('csv_pointer', ftell($file)); // add reference for insert

        if( count( $headers ) !== count( $values ) ) {
            $string = (is_array($values)) ? implode('::', $values ) : $values;

            $this->add_error_log(' Mismatch count headers and row columns ');
            $this->add_error_log(' Headers count: ' . count( $headers ) );
            $this->add_error_log(' Values count: ' . count( $values ) );
            $this->add_error_log(' enclosure : ' .  $this->get_option('enclosure') );
            $this->add_error_log(' Values string: ' . $string );
            return false;
        }

        $this->add_log('item index ' . $this->get_option('item_id_index')  );
        if( is_numeric($this->get_option('item_id_index')) ) {
            $this->handle_item_id( $values );
            $this->add_log('Updating item' );
        }
        foreach ( $collection_definition['mapping'] as $metadatum_id => $header) {
            $column = null;
            foreach ( $headers as $indexRaw => $headerRaw ) {
               if( $headerRaw === $header ) {
                    $column = $indexRaw;
               }
            }

            if(is_null($column))
                continue;

            $valueToInsert = $this->handle_encoding( $values[ $column ] );

            $metadatum = new \Tainacan\Entities\Metadatum($metadatum_id);
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
        $column_item_status = $this->get_option('item_status_index');
        $column_item_comment_status = $this->get_option('item_comment_status_index');

        if( !empty($column_document) || !empty( $column_attachment ) || !empty( $column_item_status ) ){
            
            if (($handle = fopen($this->tmp_file, "r")) !== false) {
                $file = $handle;
            } else {
                $this->add_error_log(' Error reading the file ');
                return false;
            }

            $csv_pointer= $this->get_transient('csv_last_pointer');
            fseek($file, $csv_pointer);

            if( $this->get_option('enclosure') && strlen($this->get_option('enclosure')) > 0 ){
                $values = $this->handle_enclosure( $file );
            } else {
                $values = fgetcsv($file, 0, $this->get_option('delimiter'));
            }

            if( is_array($values) && !empty($column_document) ) {
                $this->handle_document( $values[$column_document], $inserted_item);
            }

            if( is_array($values) && !empty($column_attachment) ) {
                $this->handle_attachment( $values[$column_attachment], $inserted_item);
            }

            if( is_array($values) && !empty($column_item_status) ) {
                $this->handle_item_status( $values[$column_item_status], $inserted_item);
            }

            if( is_array($values) && !empty($column_item_comment_status) ) {
                $this->handle_item_comment_status( $values[$column_item_comment_status], $inserted_item);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function get_source_number_of_items() {
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
				<input class="input" type="text" name="delimiter" value="<?php echo esc_attr($this->get_option('delimiter')); ?>">
			</div>
		</div>
		
		<div class="field">
			<label class="label"><?php _e('Multivalued metadata delimiter', 'tainacan'); ?></label>
			<span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							 <i class="tainacan-icon tainacan-icon-help" ></i>
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
							 <i class="tainacan-icon tainacan-icon-help" ></i>
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
							 <i class="tainacan-icon tainacan-icon-help" ></i>
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
			<label class="label"><?php _e('Repeated Item', 'tainacan'); ?></label>
			<span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							 <i class="tainacan-icon tainacan-icon-help" ></i>
						 </span>
					</a>
					<div class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php _e('Repeated Item', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php _e('Choose the action when a repeated item is found', 'tainacan'); ?></p>
						</div>
					</div> 
			</span>
			<div class="control is-clearfix">
				<div class="select">
					<select name="repeated_item">
						<option value="update" <?php selected($this->get_option('repeated_item'), 'update'); ?> >Update</option>
						<option value="ignore" <?php selected($this->get_option('repeated_item'), 'ignore'); ?> >Ignore</option>
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
			In either case, you can point to a file using a full URL, or just a file name. In this last case, you should set the option below to tell Tainacan where to find the files in your server. You can then upload them directly (via FTP for example) and tainacan will add them to your items.\n
			", 'tainacan')); ?>
			</p>
			<label class="label"><?php _e('Server path', 'tainacan'); ?></label>
			<span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							 <i class="tainacan-icon tainacan-icon-help" ></i>
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
    private function handle_encoding($string) {
        switch( $this->get_option('encode') ) {
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
    private function handle_document($column_value, $item_inserted) {
        $TainacanMedia = \Tainacan\Media::get_instance();
        $this->items_repo->disable_logs();

        if( strpos($column_value,'url:') === 0 ) {
            $correct_value = trim(substr($column_value, 4));
            $item_inserted->set_document( $correct_value );
            $item_inserted->set_document_type( 'url' );

            if( $item_inserted->validate() ) {
                $item_inserted = $this->items_repo->update($item_inserted);
            }
        } else if( strpos($column_value,'text:') === 0 ) {
            $correct_value = trim(substr($column_value, 5));
            $item_inserted->set_document( $correct_value );
            $item_inserted->set_document_type( 'text' );
            
            if( $item_inserted->validate() ) {
                $item_inserted = $this->items_repo->update($item_inserted);
            }
        } else if( strpos($column_value,'file:') === 0 ) {
            $correct_value = trim(substr($column_value, 5));
            if( filter_var($correct_value, FILTER_VALIDATE_URL) ) {
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

                if(!$id) {
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
    private function handle_attachment( $column_value, $item_inserted) {
        $TainacanMedia = \Tainacan\Media::get_instance();
        $this->items_repo->disable_logs();
        $attachments = explode( $this->get_option('multivalued_delimiter'), $column_value);
        if( $attachments ) {
            foreach( $attachments as $attachment ) {

                if( filter_var($attachment, FILTER_VALIDATE_URL) ) {
                    $id = $TainacanMedia->insert_attachment_from_url($attachment, $item_inserted->get_id());
                    if(!$id) {
                        $this->add_error_log('Error in Attachment file imported from URL ' . $attachment);
                        return false;
                    }
                    $this->add_log('Attachment file URL imported from ' . $attachment);
                    continue;
                }

                $server_path_files = trailingslashit($this->get_option('server_path'));
                $id = $TainacanMedia->insert_attachment_from_file($server_path_files . $attachment, $item_inserted->get_id());

                if(!$id) {
                    $this->add_log('Error in Attachment file imported from server ' . $attachment);
                    continue;
                }

                $this->add_log('Attachment file in Server imported from ' . $attachment);
            }
        }
        $this->items_repo->enable_logs();
    }

    /**
     * @param $file resource the csv file uploaded
     */
    private function handle_enclosure( &$file ) {

        $line = trim(fgets($file));
        $start = substr($line, 0, strlen($this->get_option('enclosure'))); 

        if( $this->get_option('enclosure') === $start ) {
            $cut_start = strlen($this->get_option('enclosure'));
            $line = substr($line, $cut_start);
        }

        $end = substr($line, ( strlen($line)  -  strlen($this->get_option('enclosure')) ) , strlen($this->get_option('enclosure')));

        if( $this->get_option('enclosure') === $end ) {
            $line = substr($line, 0,  ( strlen($line)  -  strlen($this->get_option('enclosure')) ) );
        }

        $delimiter = $this->get_option('enclosure').$this->get_option('delimiter').$this->get_option('enclosure');
        $values = explode($delimiter, $line);
        return $values;
    }

    /**
     * @param $status string the item status
     */
    private function handle_item_status( $status, $item_inserted ) {
        //if ( in_array( $status, array( 'auto-draft', 'draft', 'pending', 'future', 'publish', 'trash', 'inherit' ) ) ) {

            $status = ( $status == 'public' ) ? 'publish' : $status;
            $item_inserted->set_status($status);
            if( $item_inserted->validate() ) {
                $item_inserted = $this->items_repo->update($item_inserted);
            }
        //}
    }

    /**
     * @param $comment_status string the item comment status
     */
    private function handle_item_comment_status( $comment_status, $item_inserted ) {
        if ( ! in_array( $comment_status, array( 'open', 'closed' ) ) ) {
            $comment_status = 'closed';
        }

        $item_inserted->set_comment_status($comment_status);
        if( $item_inserted->validate() ) {
            $item_inserted = $this->items_repo->update($item_inserted);
        }
    }

    /**
     * @param $status string the item ID
     */
    private function handle_item_id( $values ) {
        $item_id_index = $this->get_option('item_id_index');
        if( is_numeric($item_id_index ) && isset($values[intval($item_id_index)]) ){
            $this->add_transient( 'item_id',$values[intval($item_id_index)] );
            $this->add_transient( 'item_action',$this->get_option('repeated_item') );
        }
    }

    /**
     * insert processed item from source to Tainacan
     *
     * @param array $processed_item Associative array with metadatum source's as index with
     *                              its value or values
     * @param integet $collection_index The index in the $this->collections array of the collection the item is beeing inserted into
     * 
     * @return Tainacan\Entities\Item Item inserted
     */
    public function insert( $processed_item, $collection_index ) {
        remove_action( 'post_updated', 'wp_save_post_revision' );
        $collections = $this->get_collections();
        $collection_definition = isset($collections[$collection_index]) ? $collections[$collection_index] : false;
        if ( !$collection_definition || !is_array($collection_definition) || !isset($collection_definition['id']) || !isset($collection_definition['mapping']) ) {
            $this->add_error_log('Collection misconfigured');
            return false;
        }

        $collection = \Tainacan\Repositories\Collections::get_instance()->fetch($collection_definition['id']);

        $Tainacan_Metadata = \Tainacan\Repositories\Metadata::get_instance();
        $Tainacan_Item_Metadata = \Tainacan\Repositories\Item_Metadata::get_instance();
        $Tainacan_Items = \Tainacan\Repositories\Items::get_instance();

        $Tainacan_Items->disable_logs();
        $Tainacan_Metadata->disable_logs();
        $Tainacan_Item_Metadata->disable_logs();

        $item = new Entities\Item( ( is_numeric($this->get_transient('item_id')) ) ? $this->get_transient('item_id') : 0 );
        $itemMetadataArray = [];

        if( is_numeric($this->get_transient('item_id')) ) {
            $this->add_log('item will be updated ID:' . $item->get_id() );
        }

        if( $this->get_transient('item_id') && $item && is_numeric($item->get_id()) && $item->get_id() > 0 && $this->get_transient('item_action') == 'ignore' ){
            $this->add_log('Repeated Item');
            return $item;
        }

        if( is_array( $processed_item ) ) {
            foreach ( $processed_item as $metadatum_source => $values ) {
                $tainacan_metadatum_id = array_search( $metadatum_source, $collection_definition['mapping'] );
                $metadatum = $Tainacan_Metadata->fetch( $tainacan_metadatum_id );

                if( $this->is_empty_value( $values ) ) continue;

                if( $metadatum instanceof Entities\Metadatum ) {
                    $singleItemMetadata = new Entities\Item_Metadata_Entity( $item, $metadatum); // *empty item will be replaced by inserted in the next foreach
                    if( $metadatum->get_metadata_type() == 'Tainacan\Metadata_Types\Taxonomy' ) {
                        if( !is_array( $values ) ) {
                            $tmp = $this->insert_hierarchy( $metadatum, $values);
                            if ($tmp !== false) {
                                $singleItemMetadata->set_value( $tmp );
                            }
                        } else {
                            $terms = [];
                            foreach($values as $k => $v) {
                                $tmp = $this->insert_hierarchy( $metadatum, $v);
                                if ($tmp !== false) {
                                    $terms[] = $tmp;
                                }
                            }
                            $singleItemMetadata->set_value( $terms );
                        }
                    } else {
                        $singleItemMetadata->set_value( $values );
                    }
                    $itemMetadataArray[] = $singleItemMetadata;
                } else {
                    $this->add_error_log('Metadata ' . $metadatum_source . ' not found');
                }
            }
        }

        if( !empty( $itemMetadataArray ) && $collection instanceof Entities\Collection ) {
            $item->set_collection( $collection );
            if( $item->validate() ) {
                $insertedItem = $Tainacan_Items->insert( $item );
            } else {
                $this->add_error_log( 'Error inserting Item Title: ' . $item->get_title() );
                $this->add_error_log( $item->get_errors() );
                return false;
            }

            foreach ( $itemMetadataArray as $itemMetadata ) {
                $itemMetadata->set_item( $insertedItem );  // *I told you
                if( $itemMetadata->validate() ) {
                    $result = $Tainacan_Item_Metadata->insert( $itemMetadata );
                } else {
                    $this->add_error_log('Error saving value for ' . $itemMetadata->get_metadatum()->get_name() . " in item " . $insertedItem->get_title());
                    $this->add_error_log($itemMetadata->get_errors());
                    continue;
                }

                //if( $result ){
                //	$values = ( is_array( $itemMetadata->get_value() ) ) ? implode( PHP_EOL, $itemMetadata->get_value() ) : $itemMetadata->get_value();
                //    $this->add_log( 'Item ' . $insertedItem->get_id() .
                //        ' has inserted the values: ' . $values . ' on metadata: ' . $itemMetadata->get_metadatum()->get_name() );
                //} else {
                //    $this->add_error_log( 'Item ' . $insertedItem->get_id() . ' has an error' );
                //}
            }

            $insertedItem->set_status('publish' );
            if($insertedItem->validate()) {
                $insertedItem = $Tainacan_Items->update( $insertedItem );
                $this->after_inserted_item(  $insertedItem, $collection_index );
            } else {
                $this->add_error_log( 'Error publishing, Item Title: ' . $insertedItem->get_title()  );
                $this->add_error_log( 'Error publishing, Item ID: ' . $insertedItem->get_id()  );
                $this->add_error_log( $insertedItem->get_errors() );
                return false;
            }
            return $insertedItem;
        } else {
            $this->add_error_log(  'Collection not set');
            return false;
        }
    }

    /**
     * @param $value
     * @return bool
     */
    public function is_empty_value( $value ){
        if( is_array( $value ) ){
            return ( empty( array_filter( $value ) ) );
        } else {
            return ( trim( $value ) === '' );
        }
    }

    /**
     * @param $metadatum the metadata
     * @param $values the categories names
     * 
     * @return array empty with no category or array with IDs
     */
    private function insert_hierarchy( $metadatum, $values ){

		if (empty($values)) {
			return false;
		}
		
		$Tainacan_Terms = \Tainacan\Repositories\Terms::get_instance();
        $taxonomy = new Entities\Taxonomy( $metadatum->get_metadata_type_options()['taxonomy_id']);
		
        if ( strpos($values, '>>') === false ) {
            return $values;
        }
        
        $exploded_values = explode(">>",$values);
		
		if (empty($exploded_values)) {
			return false;
		}

        if( is_array($exploded_values) ) {
            $parent = 0;
            foreach ( $exploded_values as $key => $value) {
				$value = trim($value);
                if ($value=='') {
					$this->add_error_log('Malformed term hierarchy for Item ' . $this->get_current_collection_item() . '. Term skipped. Value: ' . $values);
					return false;
                }
                $exists = $Tainacan_Terms->term_exists( $value ,$taxonomy->get_db_identifier(), $parent, true );
                if (false !== $exists && isset($exists->term_id)) {
					$parent = $exists->term_id;
                } else {
					$this->add_log('New term created: ' . $value . ' in tax_id: ' . $taxonomy->get_db_identifier() . '; parent: ' . $parent);
                    $term = new Entities\Term();
                    $term->set_name( $value );
                    $term->set_parent( $parent );
					$term->set_taxonomy( $taxonomy->get_db_identifier() );
                    if ( $term->validate() ) {
                        $term = $Tainacan_Terms->insert( $term );
                        $parent = $term->get_id();
                    } else {
                        $this->add_error_log('Invalid Term for Item ' . $this->get_current_collection_item() . ' on Metadatum ' . $metadatum->get_name() . '. Term skipped. Value: ' . $values);
                        $this->add_error_log( implode(',', $term->get_errors()) );
                        return false;
                    }
                    
                }
            }
            return $parent !== 0 ? (int)$parent : false;
        } else {
            return false;
        }
    }

    /**
     * @param $collection_id int the collection id
     * @param $mapping array the headers-metadata mapping
     */
    public function save_mapping( $collection_id, $mapping ){
        update_post_meta( $collection_id, 'metadata_mapping', $mapping );
    }

    /**
     * @param $collection_id
     *
     * @return array/bool false if has no mapping or associated array with metadata id and header
     */
    public function get_mapping( $collection_id ){
        $mapping = get_post_meta( $collection_id, 'metadata_mapping', true );
        return ( $mapping ) ? $mapping : false;
    }


    /**
     * @inheritdoc
     *
     * allow save mapping
     */
    public function add_collection(array $collection) {
        if (isset($collection['id'])) {

            if( isset($collection['mapping']) && is_array($collection['mapping']) ){

                foreach( $collection['mapping'] as $metadatum_id => $header ){

                    if( !is_numeric($metadatum_id) ) {
                        $metadatum = $this->create_new_metadata( $header, $collection['id']);

                        if( is_object($metadatum) ){
                            unset($collection['mapping'][$metadatum_id]);
                            $collection['mapping'][$metadatum->get_id()] = $header;
                        }

                    }
                }

                $this->save_mapping( $collection['id'], $collection['mapping'] );
            }

            $this->remove_collection($collection['id']);
            $this->collections[] = $collection;
        }
    }

}
