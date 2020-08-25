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
class PartAdminTaxonomyScienceCounsil extends PartTaxonomyScienceCounsil {


	use Controls;


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->part_name = 'science_counsil_admin';
	}


	/**
	 *	Регистрация метабокса
	 * @since    2.0.0
	 * @var      string       $post_type
	 */
	public function add_meta_box( $post_type ) {
		$taxonomy = get_taxonomy( $this->taxonomy_name );
		if (
			$taxonomy
			&& ! is_wp_error( $taxonomy )
			&& is_array( $taxonomy->object_type )
			&& in_array( $post_type, $taxonomy->object_type )
		) {
			$terms = get_terms( [
				'taxonomy'   => $this->taxonomy_name,
				'hide_empty' => false,
			] );
			if ( is_array( $terms ) && ! empty( $terms ) ) {
				add_meta_box(
					$this->part_name,
					$taxonomy->labels->name,
					array( $this, 'render_metabox_content' ),
					$post_type,
					'side',
					'high',
					null
				);
			}
		}
	}


	/**
	 * Сохранение записи типа "конкурсная работа"
	 * @since    2.0.0
	 * @param    int          $post_id
	 * @param    WP_Post      $post
	 */
	public function save_post( $post_id, $post ) {
		if ( ! isset( $_POST[ "{$this->part_name}_nonce" ] ) ) return;
		if ( ! wp_verify_nonce( $_POST[ "{$this->part_name}_nonce" ], $this->part_name ) ) { wp_nonce_ays(); return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( wp_is_post_revision( $post_id ) ) return;
		if ( 'page' == $_POST[ 'post_type' ] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) return $post_id;
		} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_nonce_ays();
			return;
		}
		$terms = [];
		if ( isset( $_POST[ $this->part_name ] ) ) {
			$terms = wp_parse_id_list( $_POST[ $this->part_name ] );
		}
		wp_set_object_terms( $post_id, $terms, $this->taxonomy_name, false );
	}


	/**
	 * Регистрирует стили для админки
	 * @since    2.0.0
	 * @var      WP_Post       $post
	 */
	public function render_metabox_content( $post ) {
		wp_nonce_field( $this->part_name, "{$this->part_name}_nonce" );
		extract( apply_filters( "{$this->plugin_name}_{$this->part_name}_select_params", [
			'name'  => $this->part_name,
			'terms' => get_terms( array(
				'taxonomy'   => $this->taxonomy_name,
				'hide_empty' => false,
				'fields'     => 'id=>name',
			) ),
			'args'           => [
				'selected' => wp_get_object_terms( $post->ID, $this->taxonomy_name, array( 'fields' => 'ids' ) ),
				'atts'     => [
					'class'    => 'form-control',
					'id'       => $this->taxonomy_name,
				],
			],
		] ) );
		if ( is_array( $terms ) && ! empty( $terms ) ) {
			$id = '';
			$label = '';
			$control = $this->render_dropdown( $name, $terms, $args );
		} else {
			$control = __( 'Заполните таксономию или обратитесь к администратору сайта.', $this->plugin_name );
		}
		include dirname( __FILE__ ) . '/partials/form-group.php';
	}


	/**
	 * Регистрирует дополнительную колонку в таблице терпов
	 * @since    2.0.0
	 * @param    array        $columns    массив зарегистрированных еолонок
	 * @return   array
	 */
	public function add_columns( $columns ) {
		$columns[ "{$this->part_name}_term_id" ] = __( 'ID', $this->plugin_name );
		return $columns;
	}


	/**
	 * Формирует html код содержимого ячейки
	 * @since    2.0.0
	 * @param    string    $content      содержимое яцейки
	 * @param    string    $column_name  идентификатор ячейки
	 * @param    int       $term_id      идентификатор терма
	 * @return   string
	 */
	public function render_custom_columns( $content, $column_name, $term_id ) {
		if ( "{$this->part_name}_term_id" == $column_name ) {
			$content = '<b><code>' . $term_id . '</code></b>';
		}
		return $content;
	}


}