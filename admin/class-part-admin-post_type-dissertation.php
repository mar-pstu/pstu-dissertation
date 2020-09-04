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
				$result = self::sanitize_opponents( $value );
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
				case 'delete_date':
					$control = $this->render_input( $name, 'text', [
						'value' => ( empty( $value ) ) ? date( 'Y-m-d', strtotime( $this->settings[ 'deletion_interval' ] ) ) : $value,
						'id'    => $id,
					] ) . '&nbsp;&nbsp;' . $this->render_checkbox( $name . '_auto', '', __( 'авто', $this->plugin_name ), [ 'id' => $id . '_auto' ] );
					break;
				case 'publication':
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
		$delete_date_auto_script = <<< EOF
jQuery( document ).ready( function () {
	jQuery( '[name=delete_date_auto]' ).change( function ( e ) {
		jQuery( '[name=delete_date]' ).prop( 'disabled', jQuery( this ).is( ':checked' ) );
	} ).prop( 'checked', false );
} );
EOF;
		wp_enqueue_script( 'jquery.maskedinput', plugin_dir_url( __FILE__ ) . 'scripts/jquery.maskedinput.js',  [ 'jquery' ], '1.4.1', false );
		wp_add_inline_script( 'jquery.maskedinput', "jQuery(function($){jQuery('#{$this->part_name}_protection_time').mask('99:99');});", 'after' );
		wp_add_inline_script( 'jquery-ui-datepicker', "jQuery( '#{$this->part_name}_publication' ).datepicker( { dateFormat: 'yy-mm-dd' } );", 'after' );
		wp_add_inline_script( 'jquery-ui-datepicker', "jQuery( '#{$this->part_name}_delete_date' ).datepicker( { dateFormat: 'yy-mm-dd' } );", 'after' );
		wp_add_inline_script( 'jquery-ui-datepicker', "jQuery( '#{$this->part_name}_protection' ).datepicker( { dateFormat: 'yy-mm-dd' } );", 'after' );
		wp_add_inline_script( 'jquery', $delete_date_auto_script, 'after' );
	}


	/**
	 * Регистрирует настройки плагина
	 */
	public function register_settings() {
		register_setting( $this->post_type_name, $this->post_type_name, [ $this, 'sanitize_setting_callback' ] );
		add_settings_section( 'removal_procedure', __( 'Процедура удаления', $this->plugin_name ), [ $this, 'render_section_info' ], $this->post_type_name ); 
		add_settings_field( 'auto_delete', __( 'Автоудаление', $this->plugin_name ), [ $this, 'render_setting_field'], $this->post_type_name, 'removal_procedure', 'auto_delete' );
		add_settings_field( 'deletion_interval', __( 'Интервал удаления', $this->plugin_name ), [ $this, 'render_setting_field'], $this->post_type_name, 'removal_procedure', 'deletion_interval' );
	}


	/**
	 * Выводит html-код формы ввода настроек для таксономии
	 * @param    string    $page_slug    идентификатор страницы настроек
	 */
	public function render_settings_form( string $page_slug ) {
		?>
			<form action="options.php" method="POST">
				<?php
					settings_fields( $this->post_type_name );
					do_settings_sections( $this->post_type_name );
					submit_button();
				?>
			</form>
		<?php
	}


	/**
	 * Описание секции настроек
	 * @param  [type] $section [description]
	 */
	public function render_section_info( $section ) {
		if ( null != $this->part_name ) {
			$file_path = dirname( __FILE__ ) . "/helpers/{$this->part_name}-section_info-{$section[ 'id' ]}.md";
			echo $this->get_parsedown_text( $file_path );
		}
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

			case 'auto_delete':
				$atts = [ 'id' => $id ];
				if ( isset( $options[ $id ] ) ) {
					$atts[ 'checked' ] = 'checked';
				}
				echo $this->render_checkbox( $name, 'on', __( 'да', $this->plugin_name ), $atts );
				break;

			case 'deletion_interval':
				$value = ( isset( $options[ $id ] ) ) ? $options[ $id ] : [];
				echo $this->render_dropdown( $name, apply_filters( "{$this->plugin_name}-time_intervals", [] ), [
					'selected'          => $value,
					'echo'              => false,
					'show_option_none'  => false,
					'atts'              => [
						'id'                => $id,
						'class'             => 'form-control',
					]
				] );
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

				case 'auto_delete':
					if ( 'on' == trim( $value ) ) {
						$new_value = true;
					}
					break;

				case 'deletion_interval':
					if ( array_key_exists( $value, apply_filters( "{$this->plugin_name}-time_intervals", [] ) ) ) {
						$new_value = $value;
					}
					break;

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
	function disable_trash_for_post_type( $null, $post ) {
		if ( $this->post_type_name == $post->post_type ) {
			return wp_delete_post( $post->ID, true );
		}
		return $null;
	}


	/**
	 * Добавляет дополнительную колонку с информацией об авторе диссертации
	 * @param    array    $columns    массив и идентификторами и заголовками колонок
	 * @return   array
	 */
	public function add_custom_columns( $columns ) {
		return array_slice( $columns, 0, 2 ) + [
			'author_full_name' => __( 'Дата публикации на сайте', $this->plugin_name ),
			'opponents_list'   => __( 'Оппоненты', $this->plugin_name ),
			'publication'      => __( 'Публикация на сайте', $this->plugin_name ),
			'delete_date'      => __( 'Удаления с сайта', $this->plugin_name ),
			'protection'       => __( 'Защита', $this->plugin_name ),
		] + array_slice( $columns, 2 );
	}


	/**
	 * Выводит информацию об авторе диссертации на странице постов
	 * @param    string    $column_name    идентификатор колонки
	 * @param    int       $post_id        идентификатор поста
	 */
	public function render_custom_columns( $column_name, $post_id ) {
		switch ( $column_name ) {
			case 'author_full_name':
				echo self::render_person_full_name( get_post_meta( $post_id, 'author', true ) );
				break;
			case 'opponents_list':
				$opponents = get_post_meta( $post_id, 'opponents', true );
				if ( is_array( $opponents ) && ! empty( $opponents ) ) {
					echo implode( ", ", array_map( function ( $opponent ) {
						$opponent_full_name = self::render_person_full_name( $opponent );
						return ( empty( trim( $opponent_full_name ) ) ) ? '-' : $opponent_full_name;
					}, $opponents ) );
				}
				break;
			case 'publication':
				echo get_post_meta( $post_id, $column_name, true );
				break;
			case 'delete_date':
				$delete_date = get_post_meta( $post_id, $column_name, true );
				echo $delete_date;
				break;
			case 'protection':
				echo get_post_meta( $post_id, 'protection', true ) . ' ' . get_post_meta( $post_id, 'protection_time', true );
				break;
		}
	}


	/**
	 * Добавляем стили для зарегистрированных колонок
	 */
	public function render_custom_columns_styles() {
		echo self::css_array_to_css( [
			'.text-warning' => [
				'color'         => '#ff0000',
			],
		], [
			'indent'     => 0,
			'container'  => true,
		] );
	}


}