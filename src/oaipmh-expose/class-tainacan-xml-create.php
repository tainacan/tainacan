<?php
namespace Tainacan\OAIPMHExpose;

/**
 * A wraper of DOMDocument for data provider
 */
class Xml_Create {
    public $doc;

    /**
     * Constructs an Xml_Create object.
     *
     * @param $par_array Type: array.
     *   Array of request parameters for creating an ANDS_XML object.
     * \see create_request.
     */
    public function __construct($par_array) {
        $this->doc = new \DOMDocument("1.0", "UTF-8");
        //to have indented output, not just a line
        $this->doc->preserveWhiteSpace = false;
        $this->doc->formatOutput = true;

        // ------------- Interresting part here ------------
        //creating an xslt adding processing line
        //$xslt = $this->doc->createProcessingInstruction('xml-stylesheet', ' type="text/xsl" href="'. get_template_directory_uri().'/controllers/export/oai2.xsl"');

        //var_dump($xslt);
        //exit();
        //adding it to the xml
        //this->doc->appendChild($xslt);


        // oai_node equals to $this->doc->documentElement;
        $oai_node = $this->doc->createElement("OAI-PMH");
        $oai_node->setAttribute("xmlns", "http://www.openarchives.org/OAI/2.0/");
        $oai_node->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
        $oai_node->setAttribute("xsi:schemaLocation", "http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd");
        $this->addChild($oai_node, "responseDate", gmdate("Y-m-d\TH:i:s\Z"));
        $this->doc->appendChild($oai_node);
        $this->create_request($par_array);
    }

    /**
     * Add a child node to a parent node on a XML Doc: a worker function.
     *
     * @param $mom_node
     *   Type: DOMNode. The target node.
     *
     * @param $name
     *   Type: string. The name of child nade is being added
     *
     * @param $value
     *   Type: string. Text for the adding node if it is a text node.
     *
     * @return DOMElement $added_node
     *   The newly created node, can be used for further expansion.
     *   If no further expansion is expected, return value can be igored.
     */
    public function addChild($mom_node, $name, $value = '') {
        $added_node = $this->doc->createElement($name, $value);
        $added_node = $mom_node->appendChild($added_node);

        return $added_node;
    }

    /**
     * Add a child node to a parent node on a XML Doc: a worker function.
     *
     * @param $mom_node
     *   Type: DOMNode. The target node.
     *
     * @param $name
     *   Type: string. The name of child nade is being added
     *
     * @param $value
     *   Type: string. Text for the adding node if it is a text node.
     *
     * @return DOMElement $added_node
     *   The newly created node, can be used for further expansion.
     *   If no further expansion is expected, return value can be igored.
     */
    function addChildDC($mom_node, $name, $value = '') {
        $added_node = $this->doc->createElementNS('http://purl.org/dc/elements/1.1/',$name, $value);
        $added_node = $mom_node->appendChild($added_node);

        return $added_node;
    }

    /**
     * Create an OAI request node.
     *
     * @param $par_array Type: array
     *   The attributes of a request node. They describe the verb of the request and other associated parameters used in the request.
     * Keys of the array define attributes, and values are their content.
     */
    function create_request($par_array) {
        $request = $this->addChild($this->doc->documentElement, "request", TAINACAN_OAI_REPOSITORY_URL);
        foreach ($par_array as $key => $value) {
            $request->setAttribute($key, $value);
        }
    }

    /**
     * Display a doc in a readable, well-formatted way for display or saving
     */
    function display() {
        $pr = new \DOMDocument();
        $pr->preserveWhiteSpace = false;
        $pr->formatOutput = true;
        $pr->loadXML($this->doc->saveXML());
        echo $pr->saveXML();
    }
}
