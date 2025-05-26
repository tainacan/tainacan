<?php

namespace Tainacan\Mappers;


class Inbcm_Bibliographic extends Mapper {
	public $slug = 'inbcm-bibliografico';
	public $name = 'INBCM: Bibliográfico';
	public $allow_extra_metadata = true;
	
	function __construct() {
		parent::__construct();

		/* Metadata should be set here to allow translable labels */ 
		$this->metadata = [
			'inbcm:numRegistro' => [
				'label' => __( 'Reference Number', 'tainacan')
			],
			'inbcm:outrosNum' => [
				'label' => __( 'Other Numbers', 'tainacan')
			],
			'inbcm:situacao' => [
				'label' => __( 'Condition/Status', 'tainacan'),
			],
			'inbcm:titulo' => [
				'label' => __( 'Title', 'tainacan'),
                'core_metadatum' => 'title'
			],
			'inbcm:tipo' => [
				'label' => __( 'Type', 'tainacan'),
			],
			'inbcm:idenResponsabilidade' => [
				'label' => __( 'Statement of Responsibility', 'tainacan')
			],
			'inbcm:localProd' => [
				'label' => __( 'Place of Production', 'tainacan')
			],
			'inbcm:editora' => [
				'label' => __( 'Publisher', 'tainacan')
			],
			'inbcm:dataProd' => [
				'label' => __( 'Date of Production', 'tainacan')
			],
			'inbcm:dimFisica' => [
				'label' => __( 'Physical Dimensions', 'tainacan')
			],
			'inbcm:matTecnica' => [
				'label' => __( 'Material / Technique', 'tainacan')
			],
			'inbcm:encadernacao' => [
				'label' => __( 'Binding', 'tainacan')
			],
			'inbcm:resumoDes' => [
				'label' => __( 'Descriptive Summary', 'tainacan')
			],
			'inbcm:conservacao' => [
				'label' => __( 'Conservation Status', 'tainacan')
			],
			'inbcm:assuntoPrincipal' => [
				'label' => __( 'Main Subject', 'tainacan')
			],
			'inbcm:assuntoCronologico' => [
				'label' => __( 'Chronological Subject', 'tainacan')
			],
			'inbcm:assuntoGeo' => [
				'label' => __( 'Geographical Subject', 'tainacan')
			],
			'inbcm:condReproducao' => [
				'label' => __( 'Reproduction Conditions', 'tainacan')
			],
			'inbcm:midias' => [
				'label' => __( 'Related Media', 'tainacan')
			]
		];
	}
	
}