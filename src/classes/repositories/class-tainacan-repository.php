<?php

namespace Tainacan\Repositories;

interface Repository {

    public function insert($object);
    public function delete($object);
    public function fetch($object);
    public function update($object);

}

?>