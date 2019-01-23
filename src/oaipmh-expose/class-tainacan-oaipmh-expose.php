<?php

namespace Tainacan\OAIPMHExpose;

/**
 * Support Dublin Core Mapping
 * http://purl.org/dc/elements/1.1/
 *
 */
class OAIPMH_Expose {
    var $identifyResponse = array();
    var $deletedRecord = '';
    var $adminEmail = '';
    var $compression = '';
    var $expirationdatetime = '';
    var $delimiter = ':';
    var $show_identifier;
    var $SETS;
    var $METADATAFORMATS;
    var $supported_formats;
    var $MAXRECORDS;
    var $CONTENT_TYPE;
    var $charset;
    var $xmlescaped;
    var $text;
    var $code;
    /**
     * @signature - config
     * @return begin the class
     * @author: Eduardo
     */
    public function config() {
        $this->CONTENT_TYPE = 'Content-Type: text/xml';
        $this->identifyResponse["repositoryName"] = get_bloginfo( 'name');
        $this->identifyResponse['protocolVersion'] = '2.0';
        $this->identifyResponse['baseURL'] = get_bloginfo( 'url').'/oai/socialdb-oai/';
        $this->identifyResponse["earliestDatestamp"] = '2006-06-01';
        $this->identifyResponse["deletedRecord"] = 'no';
        $this->identifyResponse["granularity"] = 'YYYY-MM-DDThh:mm:ssZ';

        //$this->deletedRecord = $identifyResponse["deletedRecord"]; // a shorthand for checking the configuration of Deleted Records
        if (strcmp($this->identifyResponse["granularity"], 'YYYY-MM-DDThh:mm:ssZ') == 0) {
            $this->identifyResponse["earliestDatestamp"] = $this->identifyResponse["earliestDatestamp"] . 'T00:00:00Z';
        }

        $this->adminEmail = get_bloginfo( 'admin_email');

        /** Compression methods supported. Optional (multiple). Default: null.
         *
         * Currently only gzip is supported (you need output buffering turned on,
         * and php compiled with libgz).
         * The client MUST send "Accept-Encoding: gzip" to actually receive
         */
        $this->compression = null;
        $url = array_reverse(explode('/', str_replace('http://', '', get_bloginfo( 'url'))));

        if(is_array($url)&& count($url)>1){
            $this->repositoryIdentifier = implode('.', $url);
        }else{
            $this->repositoryIdentifier = $url[0];
        }

        define('REG_OBJ_GROUP', 'Something agreed on');
        $this->show_identifier = false;

        /** Maximum mumber of the records to deliver
         * (verb is ListRecords)
         * If there are more records to deliver
         * a ResumptionToken will be generated.
         */
        $this->MAXRECORDS = 100;

        /** Maximum mumber of identifiers to deliver
         * (verb is ListIdentifiers)
         * If there are more identifiers to deliver
         * a ResumptionToken will be generated.
         */
        define('MAXIDS', 40);
        /** After 24 hours resumptionTokens become invalid. Unit is second. */
        define('TOKEN_VALID', 24 * 3600);
        define('MY_URI',  get_bloginfo( 'url' ));
        $this->expirationdatetime = gmstrftime('%Y-%m-%dT%TZ', time() + TOKEN_VALID);

        /** Where token is saved and path is included */
        //if(!is_dir(dirname(__FILE__).'/../../data/tokens/')){
          //  mkdir(dirname(__FILE__).'/../../data/socialdb_tokens/');
        //}
        //define('TOKEN_PREFIX', dirname(__FILE__).'/../../data/tokens/');

        $this->SETS = array(
            array('setSpec' => 'class:activity', 'setName' => 'Activities'),
            array('setSpec' => 'class:collection', 'setName' => 'Collections'),
            array('setSpec' => 'class:party', 'setName' => 'Parties'));

        $this->METADATAFORMATS = array(
            'oai_dc' => array('metadataPrefix' => 'oai_dc',
                'schema' => 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
                'metadataNamespace' => 'http://www.openarchives.org/OAI/2.0/oai_dc/',
                'myhandler' => 'record_dc.php',
                'record_prefix' => 'dc',
                'record_namespace' => 'http://purl.org/dc/elements/1.1/'
            )
        );

        $this->supported_formats = array('oai_dc');

        if (!is_array($this->METADATAFORMATS)) {
            exit("Configuration of METADATAFORMAT has been wrongly set. Correct your " . __FILE__);
        }
        define('XMLSCHEMA', 'http://www.w3.org/2001/XMLSchema-instance');
        $this->charset = "iso8859-1";
        $this->xmlescaped = false;
    }

    /** Dump information of a varible for debugging,
     * only works when SHOW_QUERY_ERROR is true.
     * \param $var_name Type: string Name of variable is being debugded
     * \param $var Type: mix Any type of varibles used in PHP
     * \see SHOW_QUERY_ERROR in oaidp-config.php
     */
    function debug_var_dump($var_name, $var) {
        if (SHOW_QUERY_ERROR) {
            echo "Dumping \${$var_name}: \n";
            var_dump($var) . "\n";
        }
    }

    /** Prints human-readable information about a variable for debugging,
     * only works when SHOW_QUERY_ERROR is true.
     * \param $var_name Type: string Name of variable is being debugded
     * \param $var Type: mix Any type of varibles used in PHP
     * \see SHOW_QUERY_ERROR in oaidp-config.php
     */
    function debug_print_r($var_name, $var) {
        if (SHOW_QUERY_ERROR) {
            echo "Printing \${$var_name}: \n";
            print_r($var) . "\n";
        }
    }

    /** Prints a message for debugging,
     * only works when SHOW_QUERY_ERROR is true.
     * PHP function print_r can be used to construct message with <i>return</i> parameter sets to true.
     * \param $msg Type: string Message needs to be shown
     * \see SHOW_QUERY_ERROR in oaidp-config.php
     */
    function debug_message($msg) {
        if (!SHOW_QUERY_ERROR)
            return;
        echo $msg, "\n";
    }
    /** Check if provided correct arguments for a request.
     *
     * Only number of parameters is checked.
     * metadataPrefix has to be checked before it is used.
     * set has to be checked before it is used.
     * resumptionToken has to be checked before it is used.
     * from and until can easily checked here because no extra information
     * is needed.
     */
    function checkArgs($args, $checkList) {
//	global $errors, $TOKEN_VALID, $METADATAFORMATS;
        global $errors, $METADATAFORMATS;
//	$verb = $args['verb'];
        unset($args["verb"]);
        debug_print_r('checkList', $checkList);
        debug_print_r('args', $args);
        // "verb" has been checked before, no further check is needed
        if (isset($checkList['required'])) {
            for ($i = 0; $i < count($checkList["required"]); $i++) {
                debug_message("Checking: par$i: " . $checkList['required'][$i] . " in ");
                debug_var_dump("isset(\$args[\$checkList['required'][\$i]])", isset($args[$checkList['required'][$i]]));
                // echo "key exists". array_key_exists($checkList["required"][$i],$args)."\n";
                if (isset($args[$checkList['required'][$i]]) == false) {
                    // echo "caught\n";
                    $errors[] = oai_error('missingArgument', $checkList["required"][$i]);
                } else {
                    // if metadataPrefix is set, it is in required section
                    if (isset($args['metadataPrefix'])) {
                        $metadataPrefix = $args['metadataPrefix'];
                        // Check if the format is supported, it has enough infor (an array), last if a handle has been defined.
                        if (!array_key_exists($metadataPrefix, $METADATAFORMATS) || !(is_array($METADATAFORMATS[$metadataPrefix]) || !isset($METADATAFORMATS[$metadataPrefix]['myhandler']))) {
                            $errors[] = oai_error('cannotDisseminateFormat', 'metadataPrefix', $metadataPrefix);
                        }
                    }
                    unset($args[$checkList["required"][$i]]);
                }
            }
        }
        debug_message('Before return');
        debug_print_r('errors', $errors);
        if (!empty($errors))
            return;
        // check to see if there is unwanted
        foreach ($args as $key => $val) {
            debug_message("checkArgs: $key");
            if (!in_array($key, $checkList["ops"])) {
                debug_message("Wrong\n" . print_r($checkList['ops'], true));
                $errors[] = oai_error('badArgument', $key, $val);
            }
            switch ($key) {
                case 'from':
                case 'until':
                    if (!checkDateFormat($val)) {
                        $errors[] = oai_error('badGranularity', $key, $val);
                    }
                    break;
                case 'resumptionToken':
                    // only check for expairation
                    if ((int) $val + TOKEN_VALID < time())
                        $errors[] = oai_error('badResumptionToken');
                    break;
            }
        }
    }
    /** Validates an identifier. The pattern is: '/^[-a-z\.0-9]+$/i' which means
     * it accepts -, letters and numbers.
     * Used only by function <B>oai_error</B> code idDoesNotExist.
     * \param $url Type: string
     */
    function is_valid_uri($url) {
        return((bool) preg_match('/^[-a-z\.0-9]+$/i', $url));
    }
    /** Validates attributes come with the query.
     * It accepts letters, numbers, ':', '_', '.' and -.
     * Here there are few more match patterns than is_valid_uri(): ':_'.
     * \param $attrb Type: string
     */
    function is_valid_attrb($attrb) {
        return preg_match("/^[_a-zA-Z0-9\-\:\.]+$/", $attrb);
    }
    /** All datestamps used in this system are GMT even
     * return value from database has no TZ information
     */
    function formatDatestamp($datestamp) {
        return date("Y-m-d\TH:i:s\Z", strtotime($datestamp));
    }
    /** The database uses datastamp without time-zone information.
     * It needs to clean all time-zone informaion from time string and reformat it
     */
    function checkDateFormat($date) {
        $date = str_replace(array("T", "Z"), " ", $date);
        $time_val = strtotime($date);
        if (!$time_val)
            return false;
        if (strstr($date, ":")) {
            return date("Y-m-d H:i:s", $time_val);
        } else {
            return date("Y-m-d", $time_val);
        }
    }
    /** Retrieve all defined 'setSpec' from configuraiton of $SETS.
     * It is used by ANDS_TPA::create_obj_node();
     */
    function prepare_set_names() {
        global $SETS;
        $n = count($SETS);
        $a = array_fill(0, $n, '');
        for ($i = 0; $i < $n; $i++) {
            $a[$i] = $SETS[$i]['setSpec'];
        }
        return $a;
    }
    /** Finish a request when there is an error: send back errors. */
    function oai_exit($args,$errors) {
        header($this->CONTENT_TYPE);
        $e = new ANDS_Error_XML($args, $errors);
        $e->display();
        exit();
    }


    /** Generate a string based on the current Unix timestamp in microseconds for creating resumToken file name. */
    function get_token() {
        list($usec, $sec) = explode(" ", microtime());
        return ((int) ($usec * 1000) + (int) ($sec * 1000));
    }
    /** Create a token file.
     * It has three parts which is separated by '#': cursor, extension of query, metadataPrefix.
     * Called by listrecords.php.
     */
    function createResumToken($cursor, $from,$until,$sets, $metadataPrefix) {
        $token = $this->get_token();
        $fp = fopen(TOKEN_PREFIX . $token, 'w');
        if ($fp == false) {
            exit("Cannot write. Writer permission needs to be changed.");
        }
        fputs($fp, "$cursor#");
        fputs($fp, "$from#");
        fputs($fp, "$until#");
        fputs($fp, "$sets#");
        fputs($fp, "$metadataPrefix#");
        fclose($fp);
        return $token;
    }
    /** Read a saved ResumToken */
    function readResumToken($resumptionToken) {
        $rtVal = false;
        $fp = fopen($resumptionToken, 'r');
        if ($fp != false) {
            $filetext = fgets($fp, 255);
            $textparts = explode('#', $filetext);
            fclose($fp);
            unlink($resumptionToken);
            $rtVal = array((int) $textparts[0], $textparts[1], $textparts[2],$textparts[3],$textparts[4]);
        }
        return $rtVal;
    }

    /**
     * function has_mapping($collection_id)
     * @param int $collection_id
     * @return boolean
     * @author: Eduardo Humberto
     */
    public function has_mapping($collection_id) {

    }

    /**
     * function get_mapping_harvested($collection_id)
     * @param int $collection_id
     * @return boolean
     * @author: Eduardo Humberto
     */
    public function get_mapping_harvested($collection_id) {

    }

    /**
     * function list_collections_mapped()
     * @author: Eduardo Humberto
     */
    public function get_harvesting_mappings() {

    }

    /**
     * function list_collections_mapped()
     * @description metodo em retornar apenas as colecoes mapeadas
     * @author: Eduardo Humberto
     */
    public function list_collections_mapped() {

    }

    /** utility funciton to mapping error codes to readable messages */
    function oai_error($code, $argument = '', $value = '') {
        switch ($code) {
            case 'badArgument' :
                $this->text = "The argument '$argument' (value='$value') included in the request is not valid.";
                break;
            case 'badGranularity' :
                $this->text = "The value '$value' of the argument '$argument' is not valid.";
                $this->code = 'badArgument';
                break;
            case 'badResumptionToken' :
                $this->text = "The resumptionToken '$value' does not exist or has already expired.";
                break;
            case 'badRequestMethod' :
                $this->text = "The request method '$argument' is unknown.";
                $this->code = 'badVerb';
                break;
            case 'badVerb' :
                $this->text = "The verb '$argument' provided in the request is illegal.";
                break;
            case 'cannotDisseminateFormat' :
                $this->text = "The metadata format '$value' given by $argument is not supported by this repository.";
                break;
            case 'exclusiveArgument' :
                $this->text = 'The usage of resumptionToken as an argument allows no other arguments.';
                $this->code = 'badArgument';
                break;
            case 'idDoesNotExist' :
                $this->text = "The value '$value' of the identifier does not exist in this repository.";
                if (!is_valid_uri($value)) {
                    $this->code = 'badArgument';
                    $this->text .= ' Invalidated URI has been detected.';
                }
                break;
            case 'missingArgument' :
                $this->text = "The required argument '$argument' is missing in the request.";
                $this->code = 'badArgument';
                break;
            case 'noRecordsMatch' :
                $this->text = 'The combination of the given values results in an empty list.';
                break;
            case 'noMetadataFormats' :
                $this->text = 'There are no metadata formats available for the specified item.';
                break;
            case 'noVerb' :
                $this->text = 'The request does not provide any verb.';
                $this->code = 'badVerb';
                break;
            case 'noSetHierarchy' :
                $this->text = 'This repository does not support sets.';
                break;
            case 'sameArgument' :
                $this->text = 'Do not use the same argument more than once.';
                $this->code = 'badArgument';
                break;
            case 'sameVerb' :
                $this->text = 'Do not use verb more than once.';
                $this->code = 'badVerb';
                break;
            case 'notImp' :
                $this->text = 'Not yet implemented.';
                $this->code = 'debug';
                break;
            default:
                $this->text = "Unknown error: $this->code: '$this->code', argument: '$argument', value: '$value'";
                $this->code = 'badArgument';
        }
        return $this->code . "|" . $this->text;
    }

    /**
     * function get_metadata_formats($collection_id)
     * @param int $object_id
     * @return boolean
     * @description metodo responsavel em verificar os tipos de metadados
     * @author: Eduardo Humberto
     */
    public function get_metadata_formats($object_id) {

    }
}