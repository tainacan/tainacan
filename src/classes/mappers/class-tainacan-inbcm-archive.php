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
				'label' =>('Cód. de Referência')
			],
			'inbcm:titulo' => [
				'label' => ('Título'),
                'core_metadatum' => 'title'
			],
			'inbcm:data' => [
				'label' => ('Data'),
			],
			'inbcm:nivelDescricao' => [
				'label' => ( 'Nível de Descrição')
			],
			'inbcm:dimSuporte' => [
				'label' => ('Dimensão e suporte'),
			],
			'inbcm:nomeProdutor' => [
				'label' => ('Nome do Produtor')
			],
			'inbcm:biografia' => [
				'label' => ('História administrativa / Biografia')
			],
			'inbcm:historiaArquivistica' => [
				'label' => ('História Arquivística')
			],
			'inbcm:procedencia' => [
				'label' => ('Procedência')
			],
			'inbcm:conteudo' => [
				'label' => ('Âmbito e Conteúdo')
			],
			'inbcm:arranjo' => [
				'label' => ('Sistema de Arranjo')
			],
			'inbcm:reproducao' => [
				'label' => ('Condições de Reprodução')
			],
			'inbcm:originais' => [
				'label' => ('Existência e Localização dos Originais')
			],
			'inbcm:conservacao' => [
				'label' => ('Notas sobre conservação')
			],
			'inbcm:indexacao' => [
				'label' => ('Pontos de acesso e indexação de assuntos')
			],
			'inbcm:midias' => [
				'label' => ('Mídias Relacionadas')
			]
		];
	}
}