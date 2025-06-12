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
				'label' => ('Nº de Registro')
			],
			'inbcm:outrosNum' => [
				'label' => ('Outros Números')
			],
			'inbcm:situacao' => [
				'label' => ('Situação'),
			],
			'inbcm:titulo' => [
				'label' => ('Título'),
                'core_metadatum' => 'title'
			],
			'inbcm:tipo' => [
				'label' => ('Tipo'),
			],
			'inbcm:idenResponsabilidade' => [
				'label' => ('Identificação de responsabilidade')
			],
			'inbcm:localProd' => [
				'label' => ('Local de produção')
			],
			'inbcm:editora' => [
				'label' => ('Editora')
			],
			'inbcm:dataProd' => [
				'label' => ('Data de Produção')
			],
			'inbcm:dimFisica' => [
				'label' => ('Dimensão física')
			],
			'inbcm:matTecnica' => [
				'label' => ('Material / Técnica')
			],
			'inbcm:encadernacao' => [
				'label' => ('Encadernação')
			],
			'inbcm:resumoDes' => [
				'label' => ('Resumo Descritivo')
			],
			'inbcm:conservacao' => [
				'label' => ('Estado de Conservação')
			],
			'inbcm:assuntoPrincipal' => [
				'label' => ('Assunto Principal')
			],
			'inbcm:assuntoCronologico' => [
				'label' => ('Assunto Cronológico')
			],
			'inbcm:assuntoGeo' => [
				'label' => ('Assunto Geográfico')
			],
			'inbcm:condReproducao' => [
				'label' => ('Condições de Reprodução')
			],
			'inbcm:midias' => [
				'label' => ('Mídias Relacionadas')
			]
		];
	}
	
}