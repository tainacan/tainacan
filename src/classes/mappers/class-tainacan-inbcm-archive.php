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
				'label' => __( 'Reference Code', 'tainacan' ),
				'field' => 'Cód. de Referência'
			],
			'inbcm:titulo' => [
				'label' => __( 'Title', 'tainacan' ),
                'core_metadatum' => 'title',
				'field' => 'Título'
			],
			'inbcm:data' => [
				'label' => __( 'Date', 'tainacan' ),
				'field' => 'Data'
			],
			'inbcm:nivelDescricao' => [
				'label' => __( 'Level of Description', 'tainacan' ),
				'field' => 'Nível de Descrição'
			],
			'inbcm:dimSuporte' => [
				'label' => __( 'Dimension and Support', 'tainacan' ),
				'field' => 'Dimensão e suporte'
			],
			'inbcm:nomeProdutor' => [
				'label' => __( 'Producer Name', 'tainacan' ),
				'field' => 'Nome do Produtor'
			],
			'inbcm:biografia' => [
				'label' => __( 'Administrative History / Biography', 'tainacan' ),
				'field' => 'História administrativa / Biografia',
			],
			'inbcm:historiaArquivistica' => [
				'label' => __( 'Archival History', 'tainacan' ),
				'field' => 'História Arquivística'
			],
			'inbcm:procedencia' => [
				'label' => __( 'Origin', 'tainacan' ),
				'field' => 'Procedência'
			],
			'inbcm:conteudo' => [
				'label' => __( 'Scope and Content', 'tainacan' ),
				'field' => 'Âmbito e Conteúdo'
			],
			'inbcm:arranjo' => [
				'label' => __( 'Arrangement System', 'tainacan' ),
				'field' => 'Sistema de Arranjo'
			],
			'inbcm:condReproducao' => [
				'label' => __( 'Reproduction Conditions', 'tainacan' ),
				'field' => 'Condições de Reprodução'
			],
			'inbcm:originais' => [
				'label' => __( 'Existence and Location of Originals', 'tainacan'),
				'field' => 'Existência e Localização dos Originais'
			],
			'inbcm:conservacao' => [
				'label' => __( 'Conservvation Notes', 'tainacan' ),
				'field' => 'Notas sobre conservação',
			],
			'inbcm:indexacao' => [
				'label' => __( 'Access Points and Subject Indexing', 'tainacan' ),
				'field' => 'Pontos de acesso e indexação de assuntos'
			],
			'inbcm:midias' => [
				'label' => __( 'Related media', 'tainacan' ),
				'field' => 'Mídias Relacionadas'
			]
		];
	}
}