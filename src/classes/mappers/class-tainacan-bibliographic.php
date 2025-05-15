<?php

namespace Tainacan\Mappers;


class Bibliographic extends Mapper {
	public $slug = 'inbcm-bibliographic';
	public $name = 'Bibliographic';
	public $allow_extra_metadata = true;
	
	function __construct() {
		parent::__construct();

		/* Metadata should be set here to allow translable labels */ 
		$this->metadata = [
			'inbcm:numRegistro' => [
				'label' => __( 'Nº de Referência', 'tainacan')
			],
			'inbcm:outrosNum' => [
				'label' => __( 'Outros números', 'tainacan')
			],
			'inbcm:situacao' => [
				'label' => __( 'Situação', 'tainacan'),
			],
			'inbcm:titulo' => [
				'label' => __( 'Título', 'tainacan')
			],
			'inbcm:tipo' => [
				'label' => __( 'Tipo', 'tainacan'),
			],
			'inbcm:idenResponsabilidade' => [
				'label' => __( 'Identificação de responsabiblidade', 'tainacan')
			],
			'inbcm:localProd' => [
				'label' => __( 'Local de produção', 'tainacan')
			],
			'inbcm:editora' => [
				'label' => __( 'Editora', 'tainacan')
			],
			'inbcm:dataProd' => [
				'label' => __( 'Data de produção', 'tainacan')
			],
			'inbcm:dimFisica' => [
				'label' => __( 'Dimensão física', 'tainacan')
			],
			'inbcm:matTecnica' => [
				'label' => __( 'Material / técnica', 'tainacan')
			],
			'inbcm:encadernacao' => [
				'label' => __( 'Encadernação', 'tainacan')
			],
			'inbcm:resumoDes' => [
				'label' => __( 'Resumo descritivo', 'tainacan')
			],
			'inbcm:conservacao' => [
				'label' => __( 'Estado de conservação', 'tainacan')
			],
			'inbcm:assuntoPrincipal' => [
				'label' => __( 'Assunto Principal', 'tainacan')
			],
			'inbcm:assuntoCronologico' => [
				'label' => __( 'Assunto cronológico', 'tainacan')
            ],
            'inbcm:assuntoGeo' => [
				'label' => __( 'Assunto geográfico', 'tainacan')
			],
            'inbcm:condReproducao' => [
				'label' => __( 'Condições de reprodução', 'tainacan')
			],
            'inbcm:midias' => [
				'label' => __( 'Mídias relacionadas', 'tainacan')
			]
		];
	}
	
}