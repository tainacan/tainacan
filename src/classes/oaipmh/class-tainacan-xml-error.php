<?php
namespace Tainacan\OAIPMHExpose;

/**
 * Generate an XML response when a request cannot be finished
 *
 * It has only one derived member function
 */
class XML_Error extends XML_Create {

    public function __construct($par_array, $error_array) {
        parent::__construct($par_array);
        $oai_node = $this->doc->documentElement;

        if($error_array){
            foreach ($error_array as $e) {
                list($code, $value) = explode("|", $e);
                $node = $this->addChild($oai_node, "error", $value);
                $node->setAttribute("code", $code);
            }
        }
    }
}
