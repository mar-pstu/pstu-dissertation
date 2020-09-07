<?php


namespace pstu_dissertation;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за страницу экспорта
 * конкурсных работ
 *
 * @package    pstu_dissertation
 * @subpackage pstu_dissertation/admin
 * @author     chomovva <chomovva@gmail.com>
 */
class PartAdminUpdateTab extends Part {


	use Controls;


	protected $options;


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->part_name = 'update';
		$this->options = get_option( $this->plugin_name, [] );
		$this->options = array_merge( [
			'version'           => '',
			'updating_progress' => false,
		], $this->options );
	}


	/**
	 * 
	 */
	public function check_update_notice() {
		$notice = '';
		if ( empty( $this->options[ 'version' ] ) ) {
			$notice = __( 'Невозможно определить версию базы данных. Возможно при установке плагина произошка ошибка. Если плагин установлен поверх версии 1.0.0, то сделайте резервную копию и запустите обновление, активируей плагин заново.', $this->plugin_name );
		} elseif ( $this->version < $this->options[ 'version' ] ) {
			$notice = __( 'Установлена устаревшая версия плагина. Сделайте резервную копию и обновитесь.', $this->plugin_name );
		} elseif ( $this->version > $this->options[ 'version' ] ) {
			$notice = __( 'Плагин запущен с устаревшей версией базы данных. Для корректной работы необходимо обновить базу данных. Сделайте резервную копию и нажмите кнопку "Запустить обновление".', $this->plugin_name );
		}
		if ( ! empty( $notice ) ) {
			echo '<div id="' . $this->part_name . '-notice" class="notice notice-error"><p>' . $notice . '</p></div>';
		}
	}


	/**
	 * Фильтр, который добавляет вкладку с опциями
	 * на страницу настроектплагина
	 * @since    2.0.0
	 * @param    array     $tabs     исходный массив вкладок идентификатор вкладки=>название
	 * @return   array     $tabs     отфильтрованный массив вкладок идентификатор вкладки=>название
	 */
	public function add_settings_tab( $tabs ) {
		$tabs[ $this->part_name ] = __( 'Обновление', $this->plugin_name );
		return $tabs;
	}


	/**
	 * Генерируем html код страницы настроек
	 */
	public function render_tab() {
		?>
			<p><?php printf( __( 'Версия плагина: %s', $this->plugin_name ), $this->version ); ?></p>
			<p><?php printf( __( 'Версия базы данных: %s', $this->plugin_name ), ( empty( $this->options[ 'version' ] ) ) ? __( 'не определена', $this->plugin_name ) : $this->options[ 'version' ] ); ?></p>
		<?
		if ( $this->version == $this->options[ 'version' ] ) {
			?>
				<p>
					<?php printf( __( 'Версия плагина совпадает с версией базы данных. Дополнительные действия не требуются.', $this->plugin_name ), $this->version ); ?>
				</p>
			<?php
		} elseif ( $this->version > $this->options[ 'version' ] ) {
			?>
				<form id="options-update-form">
					<?php
						wp_nonce_field( __FILE__, 'update_nonce' );
						echo $this->render_input( 'tab', 'hidden', [ 'value' => $this->part_name ] );
						echo $this->render_input( 'action', 'hidden', [ 'value' => $this->plugin_name . '_settings' ] );
						submit_button( __( 'Запустить обновление', $this->plugin_name ), 'primary', 'submit', true, null );
					?>
				</form>
				<div id="options-update-result"></div>
			<?php
		}
	}



	/**
	 * Генерируем html код страницы настроек
	 */
	public function run_ajax() {
		if (
			isset( $_POST[ 'tab' ] )
			&& $this->part_name == $_POST[ 'tab' ]
			&& isset( $_POST[ 'update_nonce' ] )
			&& wp_verify_nonce( $_POST[ 'update_nonce' ], __FILE__ )
			&& $this->version > $this->options[ 'version' ]
		) {
			wp_send_json_success( $this->update_db(), 'success' );
		}
	}


	/**
	 * Переносит данные со старого формата в новый, т.е.
	 * с версии 1.0.0 на версию 2.0.0
	 * @since    2.0.0
	 */
	protected function update_db() {
		global $post;
		$result = [
			'done'    => true,
			'message' => '',
		];
		if ( ! $this->options[ 'updating_progress' ] ) {
			$this->options[ 'updating_progress' ] = [
				'dissertation' => 0,
			];
		}

		// обновление контакторв
		$count_dissertations = wp_count_posts( 'dissertation' )->publish;
		$dissertation_settings = get_option( 'dissertation', [] );
		if ( ! is_array( $dissertation_settings ) ) {
			$$dissertation_settings = [];
		}
		if ( ! array_key_exists( 'deletion_interval', $dissertation_settings ) ) {
			$dissertation_settings[ 'deletion_interval' ] = '+1 month';
		}
		if ( $count_dissertations > $this->options[ 'updating_progress' ][ 'dissertation' ] ) {
			$result[ 'done' ] = false;
			$dissertations = get_posts( [
				'numberposts' => 2,
				'offset'      => $this->options[ 'updating_progress' ][ 'dissertation' ],
				'post_type'   => 'dissertation',
			] );
			if ( is_array( $dissertations ) ) {
				foreach ( $dissertations as $dissertation ) {
					setup_postdata( $post = $dissertation );
					// процесс обновления
					$old_meta = get_post_meta( $dissertation->ID, '_pstu_metabox_dissertation', true );
					if ( is_array( $old_meta ) ) {
						$old_meta = array_merge( [
							'opponents'       => [],
							'author'          => [],
							'protection'      => '',
							'publication'     => '',
							'protection_time' => '',
						], $old_meta );
						foreach ( [ 'author', 'protection', 'publication', 'protection_time' ] as $meta_key ) {
							add_post_meta( $dissertation->ID, $meta_key, $old_meta[ $meta_key ], true );
						}
						add_post_meta( $dissertation->ID, 'opponents', PartPostTypeDessertation::sanitize_opponents( $old_meta[ 'opponents' ] ), true );
						delete_post_meta( $dissertation->ID, $meta_key );
						$delete_date = date( 'Y-m-d', strtotime( $dissertation_settings[ 'deletion_interval' ], strtotime( get_the_date() ) ) );
						update_post_meta( $dissertation->ID, 'delete_date', $delete_date, true );
					}
				}
				$this->options[ 'updating_progress' ][ 'dissertation' ] += count( $dissertations );
				$result[ 'message' ] .= sprintf( __( '<p>Обновлено %s диссертаций из %s</p>', $this->plugin_name ), $this->options[ 'updating_progress' ][ 'dissertation' ], $count_dissertations );
			} else {
				$result[ 'message' ] .= '<p>' . __( 'Ошибка обновления диссертаций!', $this->plugin_name ) . '</p>';
			}
		} else {
			// сообщ ние об успешном обновлении
			$result[ 'message' ] .= '<p>' . __( 'Диссертации обновлены!', $this->plugin_name ) . '</p>';
		}
		// завершаем обновление
		if ( $result[ 'done' ] ) {
			$this->options[ 'version' ] = $this->version;
			$result[ 'message' ] .= '<p>' . __( 'Обновление завершено! Перезагрузка ...', $this->plugin_name ) . '</p>';
			$this->options[ 'updating_progress' ] = false;
		}
		update_option( $this->plugin_name, $this->options );
		return $result;
	}


	/**
	 * Регистрирует скрипты для админки
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->part_name, plugin_dir_url( __FILE__ ) . "scripts/{$this->part_name}.js",  array( 'jquery' ), $this->version, false );
	}


	/**
	 * Закрываем сайт на время обновления
	 **/
	public function enable_maintenance_mode() {
		if ( $this->options[ 'updating_progress' ] && ! wp_doing_ajax() ) {
			wp_die( sprintf(
				'<h1>%1$s</h1><p>%2$s</p>',
				__( 'Сайт закрыт на обслуживание', $this->plugin_name ),
				__( 'Обновите страницу через несколько минут', $this->plugin_name )
			) );
		}
	}


}