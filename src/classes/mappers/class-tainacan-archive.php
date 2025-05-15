<?php

namespace Tainacan\Mappers;


class Archive extends Mapper {
	public $slug = 'inbcm-archival';
	public $name = 'Archival';
	public $allow_extra_metadata = true;
	
	function __construct() {
		parent::__construct();

		/* Metadata should be set here to allow translable labels */ 
		$this->metadata = [
			'inbcm:codRef' => [
				'label' => __( 'Código de Referência', 'tainacan')
			],
			'inbcm:titulo' => [
				'label' => __( 'Título', 'tainacan')
			],
			'inbcm:data' => [
				'label' => __( 'Data', 'tainacan'),
			],
			'inbcm:nivelDescricao' => [
				'label' => __( 'Nível de Descrição', 'tainacan')
			],
			'inbcm:dimSuporte' => [
				'label' => __( 'Dimensão e suporte', 'tainacan'),
			],
			'inbcm:nomeProdutor' => [
				'label' => __( 'Nome do Produtor', 'tainacan')
			],
			'inbcm:biografia' => [
				'label' => __( 'História administrativo / Biografia', 'tainacan')
			],
			'inbcm:historiaArquivistica' => [
				'label' => __( 'História arquivística', 'tainacan')
			],
			'inbcm:procedencia' => [
				'label' => __( 'Procedência', 'tainacan')
			],
			'inbcm:conteudo' => [
				'label' => __( 'Âmbito e conteúdo', 'tainacan')
			],
			'inbcm:arranjo' => [
				'label' => __( 'Sistema de Arranjo', 'tainacan')
			],
			'inbcm:reproducao' => [
				'label' => __( 'Condições de Reprodução', 'tainacan')
			],
			'inbcm:originais' => [
				'label' => __( 'Existência e localização dos originais', 'tainacan')
			],
			'inbcm:conservacao' => [
				'label' => __( 'Existência e conservação dos originais', 'tainacan')
			],
			'inbcm:indexacao' => [
				'label' => __( 'Pontos de acesso e indexação de assuntos', 'tainacan')
			],
			'inbcm:midias' => [
				'label' => __( 'Mídias relacionadas', 'tainacan')
			]
		];
	}
	
}