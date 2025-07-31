<?php

namespace Tainacan\Mappers;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Inbcm_Bibliographic extends Mapper {
	public $slug = 'inbcm-bibliografico';
	public $name = 'INBCM: Bibliográfico';
	public $allow_extra_metadata = true;
	
	function __construct() {
		parent::__construct();

		/* Metadata should be set here to allow translable labels */ 
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
			'inbcm:titulo' => [
				'label' => __( 'Title', 'tainacan' ),
                'core_metadatum' => 'title',
				'field' => 'Título'
			],
			'inbcm:tipo' => [
				'label' => __( 'Type', 'tainacan' ),
				'field' => 'Tipo',
			],
			'inbcm:idenResponsabilidade' => [
				'label' => __( 'Identification of Responsibility', 'tainacan' ),
				'field' => 'Identificação de responsabilidade'
			],
			'inbcm:localProd' => [
				'label' => __( 'Production Site', 'tainacan' ),
				'field' => 'Local de produção'
			],
			'inbcm:editora' => [
				'label' => __( 'Publisher', 'tainacan' ),
				'field' => 'Editora'
			],
			'inbcm:dataProd' => [
				'label' => __( 'Production Date', 'tainacan' ),
				'field' => 'Data de Produção'
			],
			'inbcm:dimFisica' => [
				'label' => __( 'Physical dimension', 'tainacan' ),
				'field' => 'Dimensão física'
			],
			'inbcm:matTecnica' => [
				'label' => __( 'Material/Technique', 'tainacan' ),
				'field' => 'Material/Técnica'
			],
			'inbcm:encadernacao' => [
				'label' => __( 'Bindings', 'tainacan' ),
				'field' => 'Encadernação'
			],
			'inbcm:resumoDes' => [
				'label' => __( 'Descriptive Summary', 'tainacan' ),
				'field' => 'Resumo Descritivo'
			],
			'inbcm:conservacao' => [
				'label' => __( 'Conservation status', 'tainacan' ),
				'field' => 'Estado de Conservação'
			],
			'inbcm:assuntoPrincipal' => [
				'label' => __( 'Main Subject', 'tainacan' ),
				'field' => 'Assunto Principal'
			],
			'inbcm:assuntoCronologico' => [
				'label' => __( 'Chronological Subject', 'tainacan' ),
				'field' => 'Assunto Cronológico',
			],
			'inbcm:assuntoGeo' => [
				'label' => __( 'Geographical Subject', 'tainacan' ),
				'field' => 'Assunto Geográfico',
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