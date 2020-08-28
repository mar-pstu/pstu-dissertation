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
class PartAdminPostTypeDessertation extends PartPostTypeDessertation {


	use Controls;


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->part_name = 'dissertation_admin';
	}


	/**
	 *	Регистрация метабокса
	 * @since    2.0.0
	 * @var      string       $post_type
	 */
	public function add_meta_box( $post_type ) {
		if ( $post_type == $this->post_type_name ) {
			add_meta_box(
				$this->part_name,
				__( 'Параметры', $this->plugin_name ),
				array( $this, 'render_metabox_content' ),
				$post_type,
				'advanced',
				'high',
				null
			);
		}
	}


	/**
	 * Сохранение записи типа "конкурсная работа"
	 * @since    2.0.0
	 * @var      int          $post_id
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
		foreach ( $this->meta_fields as $name => $label ) {
			$new_value = ( isset( $_REQUEST[ $name ] ) ) ? $this->sanitize_meta_field( $name, $_REQUEST[ $name ] ) : '';
			if ( empty( $new_value ) ) {
				delete_post_meta( $post_id, $name );
			} else {
				update_post_meta( $post_id, $name, $new_value );
			}
		}
	}


	/**
	 * Прикрепляет файлы из метаданных к посту
	 * @param   int      $meta_id      идентификатор поля в БД
	 * @param   int      $post_id      идентификатор поста
	 * @param   string   $meta_key     идентификатор метапоря
	 * @param   mixed    $meta_value   значение поля
	 */
	public function attach_file_to_post( $meta_id, $post_id, $meta_key, $meta_value ) {
		if ( get_post_type( $post_id ) == $this->post_type_name ) {
			if ( is_array( $meta_value ) ) {
				array_map( function ( $value ) use ( $meta_id, $post_id, $meta_key ) {
					$this->attach_file_to_post( $meta_id, $post_id, $meta_key, $value );
				}, ( self::is_assoc_array( $meta_value ) ) ? array_values( $meta_value ) : $meta_value );
			} elseif ( is_string( $meta_value ) ) {
				if ( self::is_url( $meta_value ) && $attachment_id = attachment_url_to_postid( $meta_value ) ) {
					wp_update_post( [
						'ID'          => $attachment_id,
						'post_parent' => $post_id,
					], false );
				}
			}
		}
	}


	/**
	 * Проверка полученного поля перед сохранением в базу
	 * @since    2.0.0
	 * @var      string    $key      Идентификатор поля
	 * @var      string    $value    Новое значение металополя
	 */
	protected function sanitize_meta_field( $key, $value ) {
		switch ( $key ) {
			case 'publication':
				$result = ( empty( trim( $value ) ) ) ? date( 'Y-m-d' ) : date( 'Y-m-d', strtotime( $value ) );
				break;
			case 'delete_date':
				$result = ( empty( trim( $value ) ) ) ? date( 'Y-m-d', strtotime( '+3 month' ) ) : date( 'Y-m-d', strtotime( $value ) );
				break;
			case 'protection':
				$result = ( empty( trim( $value ) ) ) ? '' : date( 'Y-m-d', strtotime( $value ) );
				break;
			case 'dissertation':
			case 'abstract':
				$result = esc_url_raw( $value );
				break;
			case 'author':
				$result = $this->parse_only_allowed_args( [
					'last_name'   => '',
					'first_name'  => '',
					'middle_name' => '',
				], $value, [
					'sanitize_text_field',
					'sanitize_text_field',
					'sanitize_text_field',
				], [
					'last_name', 'first_name', 'middle_name'
				], [
					'last_name', 'first_name', 'middle_name'
				] );
				break;
			case 'opponents':
				$result = ( is_array( $value ) && ! empty( $value ) ) ? array_filter( array_map( function ( $item ) {
					return $this->parse_only_allowed_args( [
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
				} , $value ) ) : [];
				break;
			default:
				$result = sanitize_text_field( $value );
				break;
		}
		return $result;
	}


	/**
	 * Регистрирует стили для админки
	 * @since    2.0.0
	 * @var      WP_Post       $post
	 */
	public function render_metabox_content( $post ) {
		wp_nonce_field( $this->part_name, "{$this->part_name}_nonce" );
		foreach ( $this->meta_fields as $name => $label ) {
			$control = '';
			$id = "{$this->part_name}_{$name}";
			$value = get_post_meta( $post->ID, $name, true );
			switch ( $name ) {
				case 'protection_time':
					$control = $this->render_input( $name, 'text', [
						'value' => $value,
						'id'    => $id,
					] );
					break;
				case 'publication':
				case 'delete_date':
				case 'protection':
					$control = $this->render_input( $name, 'text', [
						'value' => $value,
						'id'    => $id,
					] );
					break;
				case 'dissertation':
				case 'abstract':
					$control = $this->render_file_choice( $name, [
						'value' => $value,
					] );
					break;
				case 'author':
					$value = array_merge( [
						'first_name'  => '',
						'last_name'   => '',
						'middle_name' => '',
					], ( is_array( $value ) ) ? $value : [] );
					$control = $this->render_composite_field(
						$this->render_input( "{$name}[last_name]", 'text', [
							'value'    => $value[ 'last_name' ],
							'class'    => 'form-control',
							'id'       => '',
							'placeholder' => __( 'Фамилия', $this->plugin_name ),
						] ),
						$this->render_input( "{$name}[first_name]", 'text', [
							'value'    => $value[ 'first_name' ],
							'class'    => 'form-control',
							'id'       => '',
							'placeholder' => __( 'Имя', $this->plugin_name ),
						] ),
						$this->render_input( "{$name}[middle_name]", 'text', [
							'value'    => $value[ 'middle_name' ],
							'class'    => 'form-control',
							'id'       => '',
							'placeholder' => __( 'Отчество', $this->plugin_name ),
						] )
					);
					break;
				case 'opponents':
					$value = array_map( function ( $item ) {
						return array_merge( [
							'last_name'   => '',
							'first_name'  => '',
							'middle_name' => '',
							'degree'      => '',
							'workplace'   => '',
							'opinion'     => '',
						], ( is_array( $item ) ) ? $item : [] );
					}, ( is_array( $value ) ) ? $value : [] );
					$control = $this->render_list_of_templates( $name, $value, [
						'template' => $this->render_composite_field(
							$this->render_input( $name . '[{{data.i}}][last_name]', 'text', [
								'value'    => '{{data.value.last_name}}',
								'class'    => 'form-control',
								'id'       => '',
								'placeholder' => __( 'Фамилия', $this->plugin_name ),
							] ),
							$this->render_input( $name . '[{{data.i}}][first_name]', 'text', [
								'value'    => '{{data.value.first_name}}',
								'class'    => 'form-control',
								'id'       => '',
								'placeholder' => __( 'Имя', $this->plugin_name ),
							] ),
							$this->render_input( $name . '[{{data.i}}][middle_name]', 'text', [
								'value'    => '{{data.value.middle_name}}',
								'class'    => 'form-control',
								'id'       => '',
								'placeholder' => __( 'Отчество', $this->plugin_name ),
							] )
						) . $this->render_input( $name . '[{{data.i}}][degree]', 'text', [
							'value'    => '{{data.value.degree}}',
							'class'    => 'form-control',
							'id'       => '',
							'placeholder' => __( 'Степень / звание', $this->plugin_name ),
						] ) . $this->render_input( $name . '[{{data.i}}][workplace]', 'text', [
							'value'    => '{{data.value.workplace}}',
							'class'    => 'form-control',
							'id'       => '',
							'placeholder' => __( 'Место работы', $this->plugin_name ),
						] ) . $this->render_file_choice( $name . '[{{data.i}}][opinion]', [
							'value'    => '{{data.value.opinion}}',
							'class'    => 'form-control',
							'id'       => '',
							'placeholder' => __( 'Отзыв', $this->plugin_name ),
						] ),
					] );
					break;
				default:
					$control = $this->render_input( $name, 'text', [
						'value' => $value,
						'class' => 'form-control',
						'id'    => $id,
					] );
					break;
			}
			include dirname( __FILE__ ) . '/partials/form-group.php';
		}
	}


	/**
	 * Регистрирует скрипты для "части" плагина
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'jquery.maskedinput', plugin_dir_url( __FILE__ ) . 'scripts/jquery.maskedinput.js',  [ 'jquery' ], '1.4.1', false );
		wp_add_inline_script( 'jquery.maskedinput', "jQuery(function($){jQuery('#{$this->part_name}_protection_time').mask('99:99');});", 'after' );
		wp_add_inline_script( 'jquery-ui-datepicker', "jQuery( '#{$this->part_name}_publication' ).datepicker( { dateFormat: 'yy-mm-dd' } );", 'after' );
		wp_add_inline_script( 'jquery-ui-datepicker', "jQuery( '#{$this->part_name}_delete_date' ).datepicker( { dateFormat: 'yy-mm-dd' } );", 'after' );
		wp_add_inline_script( 'jquery-ui-datepicker', "jQuery( '#{$this->part_name}_protection' ).datepicker( { dateFormat: 'yy-mm.dd' } );", 'after' );
	}


	/**
	 * Регистрирует настройки плагина
	 */
	public function register_settings() {
		// register_setting( $this->part_name, $this->part_name, [ $this, 'sanitize_setting_callback' ] );
		// add_settings_section( 'archive_page', __( 'Страница архива', $this->plugin_name ), [ $this, 'render_section_info' ], $this->part_name ); 
		// add_settings_field( 'archive_container_fluid', __( 'Контейнер по ширине', $this->plugin_name ), [ $this, 'render_setting_field'], $this->part_name, 'archive_page', 'archive_container_fluid' );
		// add_settings_field( 'archive_description', __( 'Описание архива', $this->plugin_name ), [ $this, 'render_setting_field'], $this->part_name, 'archive_page', 'archive_description' );
		// do_action( "{$this->plugin_name}_register_settings", $this->settings_page_slug );
		// add_settings_section( 'single_page', __( 'Страница поста', $this->plugin_name ), [ $this, 'render_section_info' ], $this->part_name ); 
		// add_settings_field( 'single_container_fluid', __( 'Контейнер по ширине', $this->plugin_name ), [ $this, 'render_setting_field'], $this->part_name, 'single_page', 'single_container_fluid' );
		// add_settings_field( 'single_template', __( 'Использовать отдельный шаблон', $this->plugin_name ), [ $this, 'render_setting_field'], $this->part_name, 'single_page', 'single_template' );
		// add_settings_field( 'single_content_before', __( 'Текст перед', $this->plugin_name ), [ $this, 'render_setting_field'], $this->part_name, 'single_page', 'single_content_before' );
		// add_settings_field( 'single_content_after', __( 'Текст после', $this->plugin_name ), [ $this, 'render_setting_field'], $this->part_name, 'single_page', 'single_content_after' );
		// add_settings_section( 'access', __( 'Доступ', $this->plugin_name ), [ $this, 'render_section_info' ], $this->part_name );
		// add_settings_field( 'no_access_message', __( 'Текст, который отображается если нет доступа', $this->plugin_name ), [ $this, 'render_setting_field'], $this->part_name, 'access', 'no_access_message' );
		// add_settings_field( 'meta_only_after_auth', __( 'Мета-параметры доступные только после аутентификации', $this->plugin_name ), [ $this, 'render_setting_field'], $this->part_name, 'access', 'meta_only_after_auth' );
	}


	/**
	 * Формирует и вывоит html-код элементов формы настроек плагина
	 * @since    1.0.0
	 * @param    string    $id       идентификатор опции
	 */
	public function render_setting_field( $id ) {
		$options = get_option( $this->post_type_name );
		$name = "{$this->post_type_name}[{$id}]";
		switch ( $id ) {

			
			case 'meta_only_after_auth':
				// $value = ( isset( $options[ $id ] ) ) ? $options[ $id ] : [];
				// echo $this->render_list_of_checkboxes( $name, $this->metabox_fields, [ 'checked' => $value ] );
				break;
			

			}
		}


	/**
	 * Очистка данных
	 * @since    1.0.0
	 * @var      array    $options
	 */
	public function sanitize_setting_callback( $options ) {
		$result = [];
		foreach ( $options as $name => &$value ) {
			$new_value = null;
			switch ( $name ) {

				// case 'meta_only_after_auth':
				// 	$new_value = [];
				// 	if ( is_array( $value ) ) {
				// 		foreach ( $value as $key ) {
				// 			if ( array_key_exists( $key, $this->metabox_fields ) ) {
				// 				$new_value[] = $key;
				// 			}
				// 		}
				// 	}
				// 	break;
				// case 'no_access_message':
				// 	$new_value = sanitize_text_field( $value );
				// 	break;

			}
			if ( null != $new_value && ! empty( $new_value ) ) {
				$result[ $name ] = $new_value;
			}
		}
		return $result;
	}


	/**
	 * Фильтр, который добавляет вкладку с опциями для текущего типа записи
	 * на страницу настроектплагина
	 * @since    1.0.0
	 * @param    array     $tabs     исходный массив вкладок идентификатор вкладки=>название
	 * @param    array     $slug     идентификатор объекта, который вызвал это событие
	 * @return   array     $tabs     отфильтрованный массив вкладок идентификатор вкладки=>название
	 */
	public function add_settings_tab( $tabs, string $slug = '' ) {
		$post_type = get_post_type_object( $this->post_type_name );
		if ( ! is_null( $post_type ) ) {
			$tabs[ $this->post_type_name ] = $post_type->labels->name;
		}
		return $tabs;
	}


	/**
	 * Удаляем вложения прикреплённые к диссертации при её удалении
	 * @param     int      $post_id     ID поста, который передается в функцию прикрепленную к событию
	 * @param     WP_Post  $post        объект поста
	 */
	function delete_post_attachment( $post_id, $post ) {
		$attachments = get_attached_media( '', $post_id );
		if ( is_array( $attachments ) && ! empty( $attachments ) ) {
			foreach ( $attachments as $attachment ) {
				wp_delete_attachment( $attachment->ID, true );
			}
		}
	}


	/**
	 * Отключаем корзину для диссертаций
	 * @param    true|false|null    $postid    Whether to go forward with trashing.
	 * @param    WP_Post            $post      объект поста
	 * @return   true|false|null
	 */
	function disable_trash_for_post_type( $null, $post ){
		if ( $this->post_type_name == $post->post_type ){
			return wp_delete_post( $post->ID, true );
		}
		return $null;
	}


}