<?php

namespace Tainacan\Repositories;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

interface Repository {

    public function insert($object);
    public function delete($object);
    public function fetch($object);
    public function update($object);

}

?>