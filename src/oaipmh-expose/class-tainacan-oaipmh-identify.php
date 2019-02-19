<?php

namespace Tainacan\OAIPMHExpose;

class OAIPMH_Identify extends OAIPMH_Expose {

    protected $working_node;

    /**
     * @param $data
     */
    public function identify($data) {

        $this->config();
        $this->xml_creater = new Xml_Response($data);
        $this->xml_creater->add2_verbNode('repositoryName',$this->identifyResponse["repositoryName"]);
        $this->xml_creater->add2_verbNode('baseURL',$this->identifyResponse["baseURL"]);
        $this->xml_creater->add2_verbNode('protocolVersion',$this->identifyResponse["protocolVersion"]);
        $this->xml_creater->add2_verbNode('adminEmail',$this->adminEmail);
        $this->xml_creater->add2_verbNode('earliestDatestamp',$this->identifyResponse["earliestDatestamp"]);
        $this->xml_creater->add2_verbNode('deletedRecord',$this->identifyResponse["deletedRecord"]);
        $this->xml_creater->add2_verbNode('granularity',$this->identifyResponse["granularity"]);

        $description_node = $this->xml_creater->add2_verbNode('description');
        $this->working_node = $this->xml_creater->addChild($description_node, 'oai-identifier');
        $this->working_node->setAttribute('xmlns', "http://www.openarchives.org/OAI/2.0/oai-identifier");
        $this->working_node->setAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
        $this->working_node->setAttribute('xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/oai-identifier http://www.openarchives.org/OAI/2.0/oai-identifier.xsd');
        $this->xml_creater->addChild($this->working_node, 'scheme',  'oai');
        $this->xml_creater->addChild($this->working_node, 'repositoryIdentifier',  $this->repositoryIdentifier);
        $this->xml_creater->addChild($this->working_node, 'delimiter',  ':');
        $this->xml_creater->addChild($this->working_node, 'sampleIdentifier',  'oai:'.$this->repositoryIdentifier.':1');

        header($this->CONTENT_TYPE);
        if (isset($this->xml_creater)) {
            $this->xml_creater->display();
        } else {
            exit("There is a bug in codes");
        }
    }

}