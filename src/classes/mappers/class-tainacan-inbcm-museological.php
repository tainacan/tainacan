<?php

namespace Tainacan\Mappers;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Inbcm_Museological extends Mapper {
	public $slug = 'inbcm-museologico';
	public $name = 'INBCM: Museológico';
	public $allow_extra_metadata = true;

	function __construct() {
		parent::__construct();

		$this->metadata = [
			'inbcm:numRegistro' => [
				'label' => __( 'Registration number', 'tainacan' ),
				'field' => 'Nº de Registro'
			],
			'inbcm:outrosNum' => [
				'label' => __( 'Other numbers', 'tainacan' ),
				'field' => 'Outros Números'
			],
			'inbcm:situacao' => [
				'label' => __( 'Situation', 'tainacan' ),
				'field' => 'Situação',
			],
			'inbcm:denominacao' => [
				'label' => __( 'Designation', 'tainacan'),
				'field' => 'Denominação'
			],
			'inbcm:titulo' => [
				'label' => __( 'Title', 'tainacan' ),
                'core_metadatum' => 'title',
				'field' => 'Título'
			],
			'inbcm:autor' => [
				'label' => __( 'Author', 'tainacan' ),
				'field' => 'Autor'
			],
			'inbcm:classificacao' => [
				'label' => __( 'Classification', 'tainacan' ),
				'field' => 'Classificação'
			],
			'inbcm:resumoDes' => [
				'label' => __( 'Descriptive Summary', 'tainacan' ),
				'field' => 'Resumo Descritivo'
			],
			'inbcm:dimensoes' => [
				'label' => __( 'Dimensions', 'tainacan' ),
				'field' => 'Dimensões'
			],
			'inbcm:matTecnica' => [
				'label' => __( 'Material/Technique', 'tainacan' ),
				'field' => 'Material/Técnica'
			],
			'inbcm:conservacao' => [
				'label' => __( 'Conservation status', 'tainacan' ),
				'field' => 'Estado de Conservação'
			],
			'inbcm:localProd' => [
				'label' => __( 'Production Site', 'tainacan' ),
				'label' => 'Local de Produção'
			],
			'inbcm:dataProd' => [
				'label' => __( 'Production Date', 'tainacan' ),
				'field' => 'Data de Produção'
			],
			'inbcm:condReproducao' => [
				'label' => __( 'Reproduction Conditions', 'tainacan' ),
				'field' => 'Condições de Reprodução'
			],
			'inbcm:midias' => [
				'label' => __( 'Related media', 'tainacan' ),
				'field' => 'Mídias Relacionadas'
			]
		];
	}
}
