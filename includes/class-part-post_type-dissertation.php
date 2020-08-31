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
			'capability_type'    => $this->post_type_name,
			'capabilities'       => [
				'edit_published_posts'=> "edit_published_{$this->post_type_name}",
				'publish_posts'       => "publish_{$this->post_type_name}",
				'delete_published_posts' => "delete_published_{$this->post_type_name}",
				'edit_posts'          => "edit_{$this->post_type_name}",
				'delete_posts'        => "delete_{$this->post_type_name}",
				'read_post'           => "read_{$this->post_type_name}",
				'read_private_posts'  => "read_private_{$this->post_type_name}",
			],
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_icon'          => 'dashicons-book',
			'menu_position'      => null,
			'supports'           => [ 'title' ],
		] );
	}


	/**
	 * Планируем функцию удаления старых диссертаций
	 */
	public function registration_deletion_of_old_posts() {
		if ( ! wp_next_scheduled( "delete_old_{$this->post_type_name}" ) ) {
			wp_schedule_event( time(), 'daily', "delete_old_{$this->post_type_name}" );
		}
	}


	/**
	 * Выполняем очистку старых диссертаций
	 */
	public function delete_old_posts_run() {
		$entries = get_posts( [
			'post_type'  => $this->post_type_name,
			'meta_query' => [
				'relation' => 'AND',
				[
					'key'     => 'delete_date',
					'value'   => date( 'Y-m-d' ),
					'compare' => '<=',
					'type'    => 'DATE',
				],
			],
		] );
		if ( is_array( $entries ) && ! empty( $entries ) ) {
			foreach ( $entries as $entry ) {
				wp_delete_post( $entry->ID, false );
			}
		}
	}


	/**
	 * Формирует ФИО из массива с данными
	 * @param    array    $info   массив с данными
	 * @return   string
	 */
	public static function render_person_full_name( $info = [] ) {
		$result = '';
		if ( is_array( $info ) ) {
			$info = array_merge( [
				'last_name'   => '',
				'first_name'  => '',
				'middle_name' => '',
			], $info );
			$result = trim( sprintf(
				'%1$s %2$s %3$s',
				$info[ 'last_name' ],
				$info[ 'first_name' ],
				$info[ 'middle_name' ]
			) );
		}
		return $result;
	}


}