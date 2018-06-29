<?php

class ScriptTainacanOld {

    var $step = 0;
    var $url = '';

    /**
     * start the execution
     */
    function __construct($argv) {
    
        $this->parse_args($argv);
        $this->run();
    
    }

    /**
     * parse args from and set the attributs
     * 1 - Old Tainacan url (required)
     */
    function parse_args($argv) {
    
        if (!is_array($argv))
            return;
            
        
        if (isset($argv[1])) {
        
            if (filter_var($argv[1], FILTER_VALIDATE_URL)) {
                $this->url = $argv[1];
            }
        
        }
        
        if (isset($argv[2])) {
        
            if (is_numeric($argv[2])) {
                $this->step = $argv[2];
            } else {
                $this->run = '';
            }
        
        }
    
    }
    
    /**
     * echo message in prompt line
     */
    function log($msg) {
    
        echo $msg . PHP_EOL;
    
    }

    function run() {
        
        $start = $partial = microtime(true);
        
        // Avoid warnings
        $_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
        $_SERVER['REQUEST_METHOD'] = "GET";
        
        define( 'WP_USE_THEMES', false );
        define( 'SHORTINIT', false );
        require( dirname(__FILE__) . '/../../../../wp-blog-header.php' );

        $old_tainacan = new \Tainacan\Importer\Old_Tainacan();
        $id = $old_tainacan->get_id();

        $_SESSION['tainacan_importer'][$id]->set_url($this->url);

        while (!$_SESSION['tainacan_importer'][$id]->is_finished()){
            $_SESSION['tainacan_importer'][$id]->run();
        }

        $scripttime = microtime(true) - $start;
        
        $this->log("==========================================================");
        $this->log("==========================================================");
        $this->log("=== Fim do script. Tempo de execução {$scripttime}s");
        $this->log("==========================================================");
        $this->log("==========================================================");
    
    }
}

$x = new ScriptTainacanOld($argv);