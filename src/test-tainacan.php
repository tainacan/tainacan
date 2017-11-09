<?php
/*
Plugin Name: Tainacan
Plugin URI: 
Description: Lorem Ipsum
Author: MediaLab UFG
Version: 10.9.8.7.6.5.4
*/

include('classes/Repositories/Collections.php');
include('classes/Repositories/Items.php');
include('classes/Repositories/Metatadas.php');
include('classes/Entity.php');
include('classes/Entities/Collection.php');
include('classes/Entities/Metadata.php');

/**
 * 
 *
 * nos loops instancia a Classes
 *
 * as classes no plural em repositories (talvez troccar esse nome pra não confundir) 
 * lidam com registro de post type, incialiação
 * e tem o metodo find() pra busca, q usa o WP_Query, mas itera e substitui por objetos
 * certos, aí talvez não precise instanciar na mão
 * Nessas classes tb vão ter metodos, sõ ativos se quisermos ver a interface dev padrao do WP
 * q vai criar os metaboxes
 * e tb os pre_get_posts... 
 * 
 *
 * 
 * as classe em entities mapeiam suas propriedades para o esquema do WP, e não tem nenhuma lõgica,
 * sõ são objetos com propriedades, collection pode acessar seus metadados. item pode 
 * aessar sua coleção e metdados
 * talvez ter um getter que tenta passar a propriedade buscada pra dentro da propriedade o objeto wp,
 * usando o mapeamento ao contrãrio. assim um tema padrão não quebra
 *
 * 
 * Repository (não confundir) tem as opções gerais do repo, como o slug padrão das coisas (colecoes, item...)
 *
 * Vai no banco:
 * Collections**
 * Metadata
 * Taxonomies
 * Items**
 * Filters
 * 
 * ** Items e Collections vão aparecer na hierarquia de templates e podem ter loops
 *
 * $collections ou $items registra os post types das coleções?
 *
 * db_identifier das coleções não pode mudar, mesmo q mude nome e slug
 *
 * essas classes tem q ter um esquema de validação, (filtro, unicidade)
 * 
 * $Collections->add(), find(), get()
 *
 * $collection->getItems(), getItem(), addItem(), deleteItem()
 *
 * metadados registrado via codigo deinem ibase_add_user
 * colecoes registradas via cõdigo passam o objeto inteiro e marcamos de algum jeito q não são editaveis
 * (source)
 *
 * 
 */














function tnc_enable_dev_wp_interface() {
    return defined('TNC_ENABLE_DEV_WP_INTERFACE') && true === TNC_ENABLE_DEV_WP_INTERFACE ? true : false;
}