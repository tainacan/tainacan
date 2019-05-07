<?php
namespace Tainacan\Importer;
use Tainacan;
use Tainacan\Entities;

class Flickr_Importer extends Importer {

    protected $endPoint = 'https://api.flickr.com/services/rest/?';
    protected $method = 'method=flickr.';
    protected $apiKey = '&api_key=';
    protected $perPage = '&per_page=1';
    protected $format = '&format=json&nojsoncallback=1';
    protected $apiKeyValue = '59dcf7e8e317103416c529b476f44fab';

    protected $steps = [
        [
            'name' => 'Identify URL',
            'progress_label' => 'Identify URL',
            'callback' => 'identify_url'
        ],
        [
            'name' => 'Import Items',
            'progress_label' => 'Import Items',
            'callback' => 'process_collections',
            'total' => 2
        ],
    ];

    /**
     * constructor
     */
    public function __construct($attributes = array()) {
        parent::__construct($attributes);

        $this->col_repo = \Tainacan\Repositories\Collections::get_instance();
        $this->items_repo = \Tainacan\Repositories\Items::get_instance();
        $this->metadata_repo = \Tainacan\Repositories\Metadata::get_instance();
        $this->item_metadata_repo = \Tainacan\Repositories\Item_Metadata::get_instance();
        $this->tax_repo = \Tainacan\Repositories\Taxonomies::get_instance();
        $this->term_repo = \Tainacan\Repositories\Terms::get_instance();

        $this->remove_import_method('file');
        $this->add_import_method('url');

    }

    /**
     * @inheritdoc
     */
    public function get_source_metadata() {
        $details = $this->identify_url(true);
        $api_key = $this->get_option('api_id');

        //$api_key = $this->apiKeyValue;
        //var_dump($this->get_list_items());
        //die;


        if( $details && $api_key ){

            return [
                'title',
                'ownername',
                'tags',
                'description',
                'owner',
                'date_upload',
                'url',
                'id',
                'type'
            ];
        } else {
            return [];
        }
    }

    /**
     * Method implemented by the child importer class to proccess each item
     * @return array
     */
    public function process_item( $index, $collection_definition ){
        $items_json = $this->get_list_items();

        $type = $this->get_transient('url_type');

        switch ($type) {

            case 'album':
                return $this->process_item_album_request( $items_json, $index, $collection_definition );

            case 'user':
                return $this->process_item_user_request( $items_json, $index, $collection_definition );

            case 'singlephoto':
                return $this->process_item_single_request( $items_json, $index, $collection_definition );

            default:
                return [];
        }
    }

    /**
     * method responsible for identify the type of url
     *
     * @param bool $showDetails
     * @return array|bool
     */
    public function identify_url( $showDetails = false ){
        $url = $this->get_url();
        $details = [];
        $matches = [];

        $preg_entities        = [
            'album'  => 'albums\/(([^\/])+)',
            'singlephoto'  => '(([^\/])+?)\/in\/',
            'user'        => 'photos\/(([^\/])+)', //match YouTube user from url
        ];


        foreach ( $preg_entities as $key => $preg_entity ) {
           //var_dump(preg_match( $preg_entity, $url, $matches ));
            if ( preg_match( '/' . $preg_entity . '/', $url, $matches ) ) {
                if ( isset( $matches[1] ) ) {
                    $details = [
                        'type' => $key,
                        'id' => $matches[1],
                    ];
                    break;
                }
            }
        }

        if( !$details ) {
            $this->add_error_log('None url from flickr has found');
            $this->abort();
            return false;
        } else {
            $this->add_transient( 'url_type', $details['type'] );
            $this->add_transient( 'flickr_id', $details['id'] );
            return ( !$showDetails ) ? false : $details;
        }

    }

    /**
     * @inheritdoc
     */
    public function get_source_number_of_items() {
        $type = $this->get_transient('url_type');

        switch ($type) {
            case 'singlephoto':
                return 1;
                break;

            default:
                $json = $this->get_list_items();

                if( isset( $json->photos ) && isset( $json->photos->total ) ){
                    return $json->photos->total;
                } else if( isset( $json->photoset ) && isset( $json->photoset->total ) ){
                    return $json->photoset->total;
                }
                break;
        }

        return 0;
    }

    /**
     * return the list of items for the different types of url
     *
     * @return array|mixed
     */
    private function get_list_items() {
        $type = $this->get_transient('url_type');
        $id = $this->get_transient('flickr_id');
        $pageToken = $this->get_transient('pageToken');
        $api_key = $this->get_option('api_id');

        switch ($type) {

            case 'album':
                $api_url = $this->endPoint .
                    $this->method . 'photosets.getPhotos' .
                    $this->apiKey . $this->apiKeyValue . '&photoset_id=' . $id .
                    '&extras=license,date_upload,date_taken,owner_name,icon_server,original_format,last_update,geo,tags,machine_tags,o_dims,views,media,path_alias,url_o' .
                    $this->perPage . '&page=' . $pageToken . $this->format;

                $this->add_log('url ' . $api_url);

                $json = json_decode(file_get_contents($api_url));
                if( $json && isset($json->photoset) ){
                    return $json;
                }
                break;

            case 'user':
                $api_url = $this->endPoint .
                    $this->method . 'photos.search' .
                    $this->apiKey . $this->apiKeyValue  . '&user_id=' . $id .
                    '&sort=date-posted-asc&content_type=1&extras=license,date_upload,date_taken,owner_name,icon_server,original_format,last_update,geo,tags,machine_tags,o_dims,views,media,path_alias,url_o' .
                    $this->perPage . '&page=' . $pageToken . $this->format;

                $this->add_log('url ' . $api_url);

                $json = json_decode(file_get_contents($api_url));
                if( $json && isset($json->photos) ){
                    return $json;

                }
                break;

            case 'singlephoto':
                $api_url = $this->endPoint .
                    $this->method . 'photos.getInfo' .
                    $this->apiKey . $this->apiKeyValue . '&extras=license,date_upload,date_taken,owner_name,icon_server,original_format,last_update,geo,tags,machine_tags,o_dims,views,media,path_alias,url_o&photo_id='
                    . $id . $this->format;

                $this->add_log('url ' . $api_url);

                $json = json_decode(file_get_contents($api_url));
                if( $json && isset($json->photo) ){
                    return $json;

                }
                break;

            default:
                return [];
                break;
        }

        return [];
    }

    /**
     * @param $items_json
     * @param $index
     * @param $collection_definition
     * @return array
     * @throws \Exception
     */
    private function process_item_user_request( $items_json, $index, $collection_definition ){
        $processedItem = [];

        if ( $items_json && $items_json->photos ) {
            $item = $items_json->photos->photo[0];
            $id_from_flickr = $this->get_transient('flickr_id');

            $id = $this->get_transient('insertedId');

            if ($id && $id === $item->id ) {
                return false;
            }

            $this->add_transient('image_url', (isset($item->url_o)) ? $item->url_o : '');

            foreach ($collection_definition['mapping'] as $metadatum_id => $raw_metadatum) {
                $value = '';

                switch ($raw_metadatum) {
                    case 'title':
                        $value = $item->title;
                        break;

                    case 'ownername':
                        $value = $id_from_flickr;
                        break;

                    case 'tags':
                        $value = '';
                        break;

                    case 'id':
                        $value = $item->id;
                        break;

                    case 'description':
                        $value = '';
                        break;

                    case 'owner':
                        $value = $item->owner;
                        break;

                    case 'url':
                        $value = $item->url_o;
                        break;

                    case 'date_upload':
                        $value = date('Y-m-d', $item->dateupload );
                        break;

                    case 'type':
                        $value = $this->get_image_mime_type( $item->url_o );
                        break;
                }

                $metadatum = new \Tainacan\Entities\Metadatum($metadatum_id);
                $processedItem[$raw_metadatum] = ($metadatum->is_multiple()) ? [$value] : $value;
            }

            $token = $index + 2;
            $this->add_log('nextToken ' . $token);
            if ( $token ) {
                $this->add_transient( 'pageToken', $token );
            }

            $this->add_transient( 'insertedId', $item->id );
        }

        return $processedItem;
    }

    /**
     *
     */
    private function process_item_album_request( $items_json, $index, $collection_definition ){
        $processedItem = [];

        if ( $items_json && $items_json->photoset ) {
            $item = $items_json->photoset->photo[0];

            $id = $this->get_transient('insertedId');

            if ($id && $id === $item->id ) {
                return false;
            }

            $this->add_transient('image_url', (isset($item->url_o)) ? $item->url_o : '');

            foreach ($collection_definition['mapping'] as $metadatum_id => $raw_metadatum) {
                $value = '';

                switch ($raw_metadatum) {
                    case 'title':
                        $value = $item->title;
                        break;

                    case 'ownername':
                        $value = $items_json->photoset->ownername;
                        break;

                    case 'tags':
                        $value = $item->tags;
                        break;

                    case 'id':
                        $value = $item->id;
                        break;

                    case 'description':
                        $value = '';
                        break;

                    case 'owner':
                        $value = $items_json->photoset->owner;
                        break;

                    case 'url':
                        $value = $item->url_o;
                        break;

                    case 'date_upload':
                        $value = date('Y-m-d', $item->dateupload );
                        break;

                    case 'type':
                        $value = $this->get_image_mime_type( $item->url_o );
                        break;
                }

                $metadatum = new \Tainacan\Entities\Metadatum($metadatum_id);
                $processedItem[$raw_metadatum] = ($metadatum->is_multiple()) ? [$value] : $value;
            }

            $token = $index + 2;
            $this->add_log('nextToken ' . $token);
            if ( $token ) {
                $this->add_transient( 'pageToken', $token );
            }

            $this->add_transient( 'insertedId', $item->id );
        }

        return $processedItem;
    }

    /**
     *
     */
    private function process_item_single_request( $items_json, $index, $collection_definition ){
        $processedItem = [];

        if ( $items_json && $items_json->photo ) {
            $item = $items_json->photo;

            $id = $this->get_transient('insertedId');

            if ($id && $id === $item->id ) {
                return false;
            }

            $url_image = 'https://farm' . $item->farm . '.staticflickr.com/' . $item->server . '/' . $item->id . '_' . $item->secret . '_b.jpg';
            $this->add_transient('image_url', $url_image);

            foreach ($collection_definition['mapping'] as $metadatum_id => $raw_metadatum) {
                $value = '';

                switch ($raw_metadatum) {
                    case 'title':
                        $value = $item->title->_content;
                        break;

                    case 'ownername':
                        $value = $item->owner->username;
                        break;

                    case 'tags':
                        $tags = [];

                        if ( isset( $item->tags ) && isset( $item->tags->tag )  && is_array( $item->tags->tag ) ) {
                            foreach ( $item->tags->tag as $tag ) {
                                $tags[] = $tag->raw;
                            }
                        }


                        $value = ( $tags ) ? implode(', ', $tags) : '';
                        break;

                    case 'id':
                        $value = $item->id;
                        break;

                    case 'description':
                        $value = $item->description->_content;
                        break;

                    case 'owner':
                        $value = $item->owner->nsid;
                        break;

                    case 'url':
                        $value = $url_image;
                        break;

                    case 'date_upload':
                        $value = date('Y-m-d', $item->dateuploaded );
                        break;

                    case 'type':
                        $value = $this->get_image_mime_type( $url_image );
                        break;
                }

                $metadatum = new \Tainacan\Entities\Metadatum($metadatum_id);
                $processedItem[$raw_metadatum] = ($metadatum->is_multiple()) ? [$value] : $value;
            }

            $this->add_transient( 'insertedId', $item->id );
        }

        return $processedItem;
    }

    /**
     * @inheritdoc
     */
    public function after_inserted_item( $inserted_item, $collection_index ) {
        $image_url = $this->get_transient('image_url');

        if( isset( $image_url ) && $image_url ){
            $TainacanMedia = \Tainacan\Media::get_instance();
            $id = $TainacanMedia->insert_attachment_from_url( $image_url, $inserted_item->get_id());
            $inserted_item->set__thumbnail_id( $id );

            $inserted_item->set_document( $image_url );
            $inserted_item->set_document_type( 'url' );
        }

        if( $inserted_item->validate() )
            $this->items_repo->update($inserted_item);
    }


    public function options_form(){
        ob_start();
        ?>
        <div class="field">
            <label class="label"><?php _e('API ID', 'tainacan'); ?></label>
            <span class="help-wrapper">
					<a class="help-button has-text-secondary">
						<span class="icon is-small">
							 <i class="tainacan-icon tainacan-icon-help" ></i>
						 </span>
					</a>
					<vdiv class="help-tooltip">
						<div class="help-tooltip-header">
							<h5><?php _e('Youtube API ID', 'tainacan'); ?></h5>
						</div>
						<div class="help-tooltip-body">
							<p><?php _e('Get youtube API ID if you request for channels or playlists', 'tainacan'); ?></p>
						</div>
					</vdiv>
			</span>
            <div class="control is-clearfix">
                <input class="input" type="text" name="api_id" value="<?php echo $this->get_option('api_id'); ?>">
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * @param $image_path
     * @return bool|mixed
     */
    private function get_image_mime_type($image_path) {
        $mimes  = array(
            IMAGETYPE_GIF => "image/gif",
            IMAGETYPE_JPEG => "image/jpg",
            IMAGETYPE_PNG => "image/png",
            IMAGETYPE_SWF => "image/swf",
            IMAGETYPE_PSD => "image/psd",
            IMAGETYPE_BMP => "image/bmp",
            IMAGETYPE_TIFF_II => "image/tiff",
            IMAGETYPE_TIFF_MM => "image/tiff",
            IMAGETYPE_JPC => "image/jpc",
            IMAGETYPE_JP2 => "image/jp2",
            IMAGETYPE_JPX => "image/jpx",
            IMAGETYPE_JB2 => "image/jb2",
            IMAGETYPE_SWC => "image/swc",
            IMAGETYPE_IFF => "image/iff",
            IMAGETYPE_WBMP => "image/wbmp",
            IMAGETYPE_XBM => "image/xbm",
            IMAGETYPE_ICO => "image/ico");

        if (($image_type = exif_imagetype($image_path))
            && (array_key_exists($image_type ,$mimes))){
            return $mimes[$image_type];
        } else {
            return FALSE;
        }
    }
}