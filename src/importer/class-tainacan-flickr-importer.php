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
                'content',
                'type',
                'license',
                'latitude',
                'longitude',
                'source'
            ];
        } else {
            return [];
        }
    }

    /**
     * Method implemented by the child importer class to proccess each item
     * @return int
     */
    public function process_item( $index, $collection_definition ){
        $processedItem = [];
        $items_json = $this->get_list_items();

        if ( $items_json && $items_json->items ) {
            $item = $items_json->items[0];

            $id = $this->get_transient('insertedId');

            if ( $id && $id === $item->contentDetails->videoId ) {
                return false;
            }

            $this->add_transient( 'video_url', 'https://www.youtube.com/watch?v=' . $item->contentDetails->videoId );
            $this->add_transient( 'image_url', ( isset( $item->snippet ) && isset( $item->snippet->thumbnails->high->url )  ) ? $item->snippet->thumbnails->high->url : '' );

            foreach ( $collection_definition['mapping'] as $metadatum_id => $raw_metadatum) {
                $value = '';

                switch ( $raw_metadatum ) {
                    case 'title':
                        $value = $item->snippet->title;
                        break;

                    case 'description':
                        $value = $item->snippet->description;
                        break;

                    case 'publishedAt':
                        $value = $item->snippet->publishedAt;
                        break;

                    case 'videoId':
                        $value = $item->contentDetails->videoId;
                        break;

                    case 'channelTitle':
                        $value = $item->snippet->channelTitle;
                        break;

                    case 'position':
                        $value = $item->snippet->position;
                        break;

                    case 'url':
                        $value = 'https://www.youtube.com/watch?v=' . $item->contentDetails->videoId;
                        break;
                }

                $metadatum = new \Tainacan\Entities\Metadatum($metadatum_id);
                $processedItem[ $raw_metadatum ] = ( $metadatum->is_multiple() ) ? [ $value ] : $value;
            }

            $this->add_log('nextToken ' . $items_json->nextPageToken);
            if ( isset( $items_json->nextPageToken ) ) {
                $this->add_transient( 'pageToken', $items_json->nextPageToken );
            }

            $this->add_transient( 'insertedId', $item->contentDetails->videoId );
        }

        return $processedItem;
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

                $json = json_decode(file_get_contents($api_url));
                if( $json && isset($json->photoset) ){
                    return $json;
                }
                break;

            case 'user':
                $api_url = $this->endPoint .
                    $this->method . 'photos.search' .
                    $this->apiKey . $this->apiKeyValue  . '&user_id=' . $id .
                    '&sort=date-posted-asc&content_type=1&extras=views,date_upload,license,date_taken,url_o' .
                    $this->perPage . '&page=' . $pageToken . $this->format;

                $json = json_decode(file_get_contents($api_url));
                if( $json && isset($json->photos) ){
                    return $json;

                }
                break;

            case 'singlephoto':
                $api_url = $this->endPoint .
                    $this->method . 'photos.getInfo' .
                    $this->apiKey . $this->apiKeyValue . '&extras=views,date_upload,license,date_taken,url_o&photo_id='
                    . $id . $this->format;

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
     * @inheritdoc
     */
    public function after_inserted_item( $inserted_item, $collection_index ) {
        $image_url = $this->get_transient('image_url');
        $video_url = $this->get_transient('video_url');

        if( isset( $image_url ) && $image_url ){
            $TainacanMedia = \Tainacan\Media::get_instance();
            $id = $TainacanMedia->insert_attachment_from_url( $image_url, $inserted_item->get_id());
            $inserted_item->set__thumbnail_id( $id );
        }

        if( isset( $video_url ) && $video_url ){

            $inserted_item->set_document( $video_url );
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
}