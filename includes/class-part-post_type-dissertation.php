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


	/**
	 * Идентификатор части плагина
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version    идентификатор "части" плагина
	 */
	protected $settings;


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
		$this->settings = array_merge( [
			'deletion_interval' => '+1 month',
			'auto_delete'       => false,
		], get_option( $this->post_type_name, [] ) );
	}


	/**
	 * регистрациятипа поста "Диссертация"
	 */
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
	 * Планирование событий для типа поста "Диссертации"
	 */
	public function registration_schedule_event() {
		if ( ! wp_next_scheduled( "delete_old_{$this->post_type_name}-run" ) ) {
			if ( $this->settings[ 'auto_delete' ] ) {
				wp_schedule_event( time(), 'daily', "delete_old_{$this->post_type_name}-run" );
			} else {
				wp_clear_scheduled_hook( "delete_old_{$this->post_type_name}-run" );
			}
		}
		if ( ! wp_next_scheduled( "delete_old_{$this->post_type_name}-notification" ) ) {
			if ( $this->settings[ 'auto_delete' ] ) {
				wp_schedule_event( time(), 'daily', "delete_old_{$this->post_type_name}-notification" );
			} else {
				wp_clear_scheduled_hook( "delete_old_{$this->post_type_name}-notification" );
			}
		}
	}


	/**
	 * Выполняем очистку старых диссертаций
	 */
	public function delete_old_posts_run() {
		if ( $this->settings[ 'auto_delete' ] ) {
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
	}


	/**
	 * Выполняем очистку старых диссертаций
	 */
	public function delete_old_posts_notification() {
		if ( $this->settings[ 'auto_delete' ] ) {
			$entries = get_posts( [
				'post_type'  => $this->post_type_name,
				'meta_query' => [
					'relation' => 'AND',
					[
						'key'     => 'delete_date',
						'value'   => date( 'Y-m-d', strtotime( '+2 day' ) ),
						'compare' => '<=',
						'type'    => 'DATE',
					],
					[
						'key'     => 'delete_notification',
						'compare' => 'NOT EXISTS',
					]
				],
			] );
			if ( is_array( $entries ) && ! empty( $entries ) ) {
				foreach ( $entries as $entry ) {
					$author_email = get_the_author_meta( 'user_email', $entry->post_author );
					if ( ! empty( $author_email ) ) {
						add_post_meta( $entry->ID, 'delete_notification', $author_email, true );
						wp_mail(
							$author_email,
							$subject = sprintf( '%1$s %2$s', __( 'Сообщение с сайта', RESUME_TEXTDOMAIN ), get_bloginfo( 'name', 'raw' ) ),
							sprintf( __( 'Оповещение! %s будет автоматически удалена Ваша публикая <a href="%s">"%s"</a>', $this->plugin_name ), get_post_meta( $entry->ID, 'delete_date', true ), get_permalink( $entry->ID, false ), $entry->post_title ),
							$headers = sprintf( 'From: %1$s <%2$s>%3$sContent-type: text/html%3$scharset=utf-8%3$s', $fields[ 'author' ], $fields[ 'email' ], "\r\n" ),
							[]
						);
					}
				}
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


	/**
	 * Проверяет доступ пользователя к метаданных
	 * @param  null/array/string   $value        значение
	 * @param  int                 $object_id    Идентификатор объекта для которого получаем метаданые
	 * @param  string              $meta_key     ключ метаданных
	 * @param  bool                $single       Возвращать только первое значение или весь массив значений
	 * @param  string              $meta_type    тип объекта
	 * @return null/array/string                 результат после обработки
	 */
	public function default_meta( $value, $object_id, $meta_key, $single, $meta_type ) {
		if ( get_post_type( $object_id ) == $this->post_type_name && empty( $value ) ) {
			switch ( $meta_key ) {
				case 'delete_date':
					$publication = get_post_meta( $object_id, 'publication', true );
					$value = date( 'Y-m-d', strtotime( $this->settings[ 'deletion_interval' ], strtotime( ( empty( $publication ) ? get_the_date( 'Y-m-d', $object_id ) : $publication ) ) ) );
					break;
				case 'publication':
					$value = get_the_date( 'Y-m-d', $object_id );
					break;
			}
		}
		return $value;
	}


	/**
	 * Очищает массив с информацией о оппонентах
	 * @param    array   $opponents   неочищенный массиы
	 * @return   array
	 */
	public static function sanitize_opponents( $opponents = [] ) {
		return ( is_array( $opponents ) ) ? array_filter( array_map( function ( $item ) {
			return Control::parse_only_allowed_args( [
				'last_name'   => '',
				'first_name'  => '',
				'middle_name' => '',
				'degree'      => '',
				'workplace'   => '',
				'opinion'     => '',
			], $item, [
				'sanitize_text_field',
				'sanitize_text_field',
				'sanitize_text_field',
				'sanitize_text_field',
				'sanitize_text_field',
				'esc_url_raw',
			], [
				'last_name', 'first_name', 'middle_name'
			], [
				'last_name', 'first_name', 'middle_name'
			] );
		}, $opponents ) ) : [];
	}


}