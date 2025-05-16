<?php

namespace Tainacan\Mappers;

class Inbcm_Museological extends Mapper {
	public $slug = 'inbcm-museológico';
	public $name = 'INBCM: Museológico';
	public $allow_extra_metadata = true;

	function __construct() {
		parent::__construct();

		$this->metadata = [
			'inbcm:numRegistro' => [
				'label' => __( 'Registration Number', 'tainacan' )
			],
			'inbcm:outrosNum' => [
				'label' => __( 'Other Numbers', 'tainacan' )
			],
			'inbcm:situacao' => [
				'label' => __( 'Condition/Status', 'tainacan' )
			],
			'inbcm:denominacao' => [
				'label' => __( 'Denomination', 'tainacan' )
			],
			'inbcm:titulo' => [
				'label' => __( 'Title', 'tainacan' )
			],
			'inbcm:autor' => [
				'label' => __( 'Author', 'tainacan' )
			],
			'inbcm:classificacao' => [
				'label' => __( 'Classification', 'tainacan' )
			],
			'inbcm:resumoDes' => [
				'label' => __( 'Descriptive Summary', 'tainacan' )
			],
			'inbcm:dimensoes' => [
				'label' => __( 'Dimensions', 'tainacan' )
			],
			'inbcm:matTecnica' => [
				'label' => __( 'Material/Technique', 'tainacan' )
			],
			'inbcm:conservacao' => [
				'label' => __( 'Conservation Status', 'tainacan' )
			],
			'inbcm:localProd' => [
				'label' => __( 'Place of Production', 'tainacan' )
			],
			'inbcm:dataProd' => [
				'label' => __( 'Date of Production', 'tainacan' )
			],
			'inbcm:condReproducao' => [
				'label' => __( 'Reproduction Conditions', 'tainacan' )
			],
			'inbcm:midias' => [
				'label' => __( 'Related Media', 'tainacan' )
			]
		];
	}
}
