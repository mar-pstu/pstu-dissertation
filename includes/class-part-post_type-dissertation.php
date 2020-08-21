<?php


namespace pstu_dissertation;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Абстрактный класс "частей" плагина
 *
 * @package    pstu_dissertation
 * @subpackage pstu_dissertation/includes
 * @author     chomovva <chomovva@gmail.com>
 */
class PartPostTypeDessertation extends PostType {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->part_name = 'dissertation';
		$this->post_type_name = 'dissertation';
		$this->meta_fields = [
			'publication'     => __( 'Дата публикации на сайте', $this->plugin_name ),
			'delete_date'     => __( 'Дата удаления диссертации с сайта', $this->plugin_name ),
			'protection'      => __( 'Дата защиты', $this->plugin_name ),
			'protection_time' => __( 'Время защиты', $this->plugin_name ),
			'dissertation'    => __( 'Диссертация', $this->plugin_name ),
			'abstract'        => __( 'Автореферат', $this->plugin_name ),
			'author'          => __( 'Автор', $this->plugin_name ),
			'opponents'       => __( 'Оппоненты', $this->plugin_name ),
		];
	}


	public function register_post_type() {
		register_post_type( $this->post_type_name, [
			'labels'             => [
				'name'               => __( 'Диссертации', $this->plugin_name ), // Основное название типа записи
				'singular_name'      => __( 'Диссертация', $this->plugin_name ), // отдельное название записи типа Book
				'add_new'            => __( 'Добавить новую', $this->plugin_name ),
				'add_new_item'       => __( 'Добавить новую диссертацию', $this->plugin_name ),
				'edit_item'          => __( 'Редактировать диссертацию', $this->plugin_name ),
				'new_item'           => __( 'Новая диссертация', $this->plugin_name ),
				'view_item'          => __( 'Посмотреть диссертацию', $this->plugin_name ),
				'search_items'       => __( 'Найти диссертацию', $this->plugin_name ),
				'not_found'          => __( 'Записей не найдено', $this->plugin_name ),
				'not_found_in_trash' => __( 'В корзине ничего не найдено', $this->plugin_name ),
				'parent_item_colon'  => '',
				'menu_name'          => __( 'Диссертации', $this->plugin_name ),

			],
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => true,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_icon'          => 'dashicons-book',
			'menu_position'      => null,
			'supports'           => [ 'title' ],
		] );
	}


	


}