<?php

namespace Tainacan\Mappers;

class Museological extends Mapper {
	public $slug = 'inbcm-museological';
	public $name = 'Museological';
	public $allow_extra_metadata = true;
	
	function __construct() {
		parent::__construct();

		$this->metadata = [
			'inbcm:numRegistro' => [
				'label' => __( 'Nº de Registro', 'tainacan' )
			],
			'inbcm:outrosNum' => [
				'label' => __( 'Outros números', 'tainacan' )
			],
			'inbcm:situacao' => [
				'label' => __( 'Situação', 'tainacan' )
			],
			'inbcm:denominacao' => [
				'label' => __( 'Denominação', 'tainacan' )
			],
			'inbcm:titulo' => [
				'label' => __( 'Título', 'tainacan' )
			],
			'inbcm:autor' => [
				'label' => __( 'Autor', 'tainacan' )
			],
			'inbcm:classificacao' => [
				'label' => __( 'Classificação', 'tainacan' )
			],
			'inbcm:resumoDes' => [
				'label' => __( 'Resumo Descritivo', 'tainacan' )
			],
			'inbcm:dimensoes' => [
				'label' => __( 'Dimensões', 'tainacan' )
			],
			'inbcm:matTecnica' => [
				'label' => __( 'Material/técnica', 'tainacan' )
			],
			'inbcm:conservacao' => [
				'label' => __( 'Estado de Conservação', 'tainacan' )
			],
			'inbcm:localProd' => [
				'label' => __( 'Local de Produção', 'tainacan' )
			],
			'inbcm:dataProd' => [
				'label' => __( 'Data de Produção', 'tainacan' )
			],
			'inbcm:condReproducao' => [
				'label' => __( 'Condições de Reprodução', 'tainacan' )
			],
			'inbcm:midias' => [
				'label' => __( 'Mídias Relacionadas', 'tainacan' )
			]
		];
	}
}
