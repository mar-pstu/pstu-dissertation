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
class PartTaxonomyScienceCounsil extends Taxonomy {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->part_name = 'science_counsil';
		$this->taxonomy_name = 'science_counsil';
		$this->meta_fields = [
			
		];
	}


	public function register_taxonomy() {
		register_taxonomy( $this->taxonomy_name, [], [ 
			'label'                 => '', // определяется параметром $labels->name
			'labels'                => [
				'name'                => __( 'Научный совет', $this->plugin_name ),
				'singular_name'       => __( 'Научный совет', $this->plugin_name ),
				'search_items'        => __( 'Найти научный совет', $this->plugin_name ),
				'all_items'           => __( 'Все научные советы', $this->plugin_name ),
				'edit_item'           => __( 'Редагувати наукову раду', $this->plugin_name ),
				'update_item'         => __( 'Обновить запись', $this->plugin_name ),
				'add_new_item'        => __( 'Добавить новый научный совет', $this->plugin_name ),
				'new_item_name'       => __( 'Название научного совета', $this->plugin_name ),
				'menu_name'           => __( 'Научные советы', $this->plugin_name ),
			],
			'description'           => '', // описание таксономии
			'public'                => false,
			// 'publicly_queryable'    => null, // равен аргументу public
			// 'show_in_nav_menus'     => true, // равен аргументу public
			'show_ui'               => true, // равен аргументу public
			'show_in_menu'          => true, // равен аргументу show_ui
			// 'show_tagcloud'         => true, // равен аргументу show_ui
			// 'show_in_quick_edit'    => null, // равен аргументу show_ui
			'hierarchical'          => false,
			'rewrite'               => false,
			// 'query_var'             => $taxonomy, // название параметра запроса
			'capabilities'          => array(),
			'meta_box_cb'           => false, // html метабокса. callback: `post_categories_meta_box` или `post_tags_meta_box`. false — метабокс отключен.
			'show_admin_column'     => true, // авто-создание колонки таксы в таблице ассоциированного типа записи. (с версии 3.5)
			'show_in_rest'          => null, // добавить в REST API
			'rest_base'             => null, // $taxonomy
			// '_builtin'              => false,
			// 'update_count_callback' => '_update_post_term_count',
		] );
	}


}