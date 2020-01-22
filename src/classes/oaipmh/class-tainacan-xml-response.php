<?php
namespace Tainacan\OAIPMHExpose;

/**
 * Generate an XML response to a request if no error has occured
 *
 * This is the class to further develop to suits a publication need
 */
class Xml_Response extends Xml_Create {

    public $verbNode;
    protected $verb;

    public function __construct($par_array) {
        parent::__construct($par_array);
        $this->verb = $par_array["verb"];
        $this->verbNode = $this->addChild($this->doc->documentElement, $this->verb);
    }

    /** Add direct child nodes to verb node (OAI-PMH), e.g. response to ListMetadataFormats.
     * Different verbs can have different required child nodes.
     *  \see create_record, create_header
     * \see http://www.openarchives.org/OAI/2.0/openarchivesprotocol.htm.
     *
     * \param $nodeName Type: string. The name of appending node.
     * \param $value Type: string. The content of appending node.
     */
    public function add2_verbNode($nodeName, $value = null) {
        return $this->addChild($this->verbNode, $nodeName, $value);
    }

    /**
     * Create an empty \<record\> node. Other nodes will be appended to it later.
     */
    public function create_record() {
        return $this->add2_verbNode("record");
    }

    /** Headers are enclosed inside of \<record\> to the query of ListRecords, ListIdentifiers and etc.
     *
     * \param $identifier Type: string. The identifier string for node \<identifier\>.
     * \param $timestamp Type: timestamp. Timestapme in UTC format for node \<datastamp\>.
     * \param $ands_class Type: mix. Can be an array or just a string. Content of \<setSpec\>.
     * \param $add_to_node Type: DOMElement. Default value is null.
     * In normal cases, $add_to_node is the \<record\> node created previously. When it is null, the newly created header node is attatched to $this->verbNode.
     * Otherwise it will be attatched to the desired node defined in $add_to_node.
     */
    public function create_header($identifier, $timestamp, $ands_class, $add_to_node = null, $is_deleted = false) {
        if (is_null($add_to_node)) {
            $header_node = $this->add2_verbNode("header");
        } else {
            $header_node = $this->addChild($add_to_node, "header");
        }

        if( $is_deleted ){
            $header_node->setAttribute('status', "deleted");
        }

        $this->addChild($header_node, "identifier", $identifier);
        $this->addChild($header_node, "datestamp", $timestamp);
        if (is_array($ands_class)) {
            foreach ($ands_class as $setspec) {
                $this->addChild($header_node, "setSpec", $setspec);
            }
        } else {
            $this->addChild($header_node, "setSpec", $ands_class);
        }
        return $header_node;
    }

    /** Create metadata node for holding metadata. This is always added to \<record\> node.
     *
     * \param $mom_record_node DOMElement. A node acts as the parent node.
     *
     * @return $meta_node Type: DOMElement.
     *   The newly created registryObject node which will be used for further expansion.
     *   metadata node itself is maintained by internally by the Class.
     */
    public function create_metadata($mom_record_node) {
        $meta_node = $this->addChild($mom_record_node, "metadata");
        return $meta_node;
    }


    /** If there are too many records request could not finished a resumpToken is generated to let harvester know
     *
     * \param $token Type: string. A random number created somewhere?
     * \param $expirationdatetime Type: string. A string representing time.
     * \param $num_rows Type: integer. Number of records retrieved.
     * \param $cursor Type: string. Cursor can be used for database to retrieve next time.
     */
    public  function create_resumpToken($token, $expirationdatetime, $num_rows, $cursor = null) {
        $resump_node = $this->addChild($this->verbNode, "resumptionToken", $token);
        if (isset($expirationdatetime)) {
            $resump_node->setAttribute("expirationDate", $expirationdatetime);
        }
        $resump_node->setAttribute("completeListSize", $num_rows);
        $resump_node->setAttribute("cursor", $cursor);
    }
}