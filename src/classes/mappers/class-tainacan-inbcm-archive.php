<?php

namespace Tainacan\Mappers;


class Inbcm_Archive extends Mapper {
	public $slug = 'inbcm-arquivistico';
	public $name = 'INBCM: Arquivístico';
	public $allow_extra_metadata = true;
	
	function __construct() {
		parent::__construct();

		/* Metadata should be set here to allow translable labels */ 
		$this->metadata = [
			'inbcm:codRef' => [
				'label' => __( 'Reference Code', 'tainacan')
			],
			'inbcm:titulo' => [
				'label' => __( 'Title', 'tainacan'),
                'core_metadatum' => 'title'
			],
			'inbcm:data' => [
				'label' => __( 'Date', 'tainacan'),
			],
			'inbcm:nivelDescricao' => [
				'label' => __( 'Level of Description', 'tainacan')
			],
			'inbcm:dimSuporte' => [
				'label' => __( 'Extent and Medium', 'tainacan'),
			],
			'inbcm:nomeProdutor' => [
				'label' => __( 'Name of Creator', 'tainacan')
			],
			'inbcm:biografia' => [
				'label' => __( 'Administrative History / Biography', 'tainacan')
			],
			'inbcm:historiaArquivistica' => [
				'label' => __( 'Archival History', 'tainacan')
			],
			'inbcm:procedencia' => [
				'label' => __( 'Provenance', 'tainacan')
			],
			'inbcm:conteudo' => [
				'label' => __( 'Scope and Content', 'tainacan')
			],
			'inbcm:arranjo' => [
				'label' => __( 'System of Arrangement', 'tainacan')
			],
			'inbcm:reproducao' => [
				'label' => __( 'Conditions of Reproduction', 'tainacan')
			],
			'inbcm:originais' => [
				'label' => __( 'Existence and Location of Originals', 'tainacan')
			],
			'inbcm:conservacao' => [
				'label' => __( 'Existence and State of Conservation of Originals', 'tainacan')
			],
			'inbcm:indexacao' => [
				'label' => __( 'Access Points and Subject Indexing', 'tainacan')
			],
			'inbcm:midias' => [
				'label' => __( 'Related Media', 'tainacan')
			]
		];
	}
}