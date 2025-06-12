<?php

namespace Tainacan\Mappers;

class Inbcm_Museological extends Mapper {
	public $slug = 'inbcm-museologico';
	public $name = 'INBCM: Museológico';
	public $allow_extra_metadata = true;

	function __construct() {
		parent::__construct();

		$this->metadata = [
			'inbcm:numRegistro' => [
				'label' => ('Nº de Registro')
			],
			'inbcm:outrosNum' => [
				'label' => ('Outros Números')
			],
			'inbcm:situacao' => [
				'label' => ('Situação')
			],
			'inbcm:denominacao' => [
				'label' => ('Denominação')
			],
			'inbcm:titulo' => [
				'label' => ('Título'),
                'core_metadatum' => 'title'
			],
			'inbcm:autor' => [
				'label' => ('Autor')
			],
			'inbcm:classificacao' => [
				'label' => ('Classificação')
			],
			'inbcm:resumoDes' => [
				'label' => ('Resumo Descritivo')
			],
			'inbcm:dimensoes' => [
				'label' => ('Dimensões')
			],
			'inbcm:matTecnica' => [
				'label' => ('Material/Técnica')
			],
			'inbcm:conservacao' => [
				'label' => ('Estado de Conservação')
			],
			'inbcm:localProd' => [
				'label' => ('Local de Produção')
			],
			'inbcm:dataProd' => [
				'label' => ('Data de Produção')
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
