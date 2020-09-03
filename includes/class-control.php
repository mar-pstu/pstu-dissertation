<?php


namespace pstu_dissertation;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


class Control {


	/**
	 * Имя плагина и слаг метаполей
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    Уникальный идентификтор плагина в контексте WP
	 */
	protected $plugin_name;


	/**
	 * Версия плагина
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    Номер текущей версии плагина
	 */
	protected $version;



	/**
	 * Инициализация класса и установка его свойства.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name       Имя плагин и слаг метаполей
	 * @param    string    $version           Текущая версия
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}


	/**
	 * Функция для очистки массива параметров
	 * @param  array $default           расзерённые парметры и стандартные значения
	 * @param  array $args              неочищенные параметры
	 * @param  array $sanitize_callback одномерный массив с именами функция, с помощью поторых нужно очистить параметры
	 * @param  array $required          обязательные параметры
	 * @param  array $not_empty         параметры которые не могут быть пустыми
	 * @return array                    возвращает ощиченный массив разрешённых параметров
	 */
	public static function parse_only_allowed_args( $default, $args, $sanitize_callback = [], $required = [], $not_empty = [] ) {
		$args = ( array ) $args;
		$result = [];
		$count = 0;
		while ( ( $value = current( $default ) ) !== false ) {
			$key = key( $default );
			if ( array_key_exists( $key, $args ) ) {
				$result[ $key ] = $args[ $key ];
				if ( isset( $sanitize_callback[ $count ] ) && ! empty( $sanitize_callback[ $count ] ) ) {
					$result[ $key ] = $sanitize_callback[ $count ]( $result[ $key ] );
				}
			} elseif ( in_array( $key, $required ) ) {
				return null;
			} else {
				$result[ $key ] = $value;
			}
			if ( empty( $result[ $key ] ) && in_array( $key, $not_empty ) ) {
				return null;
			}
			$count = $count + 1;
			next( $default );
		}
		return $result;
	}


	/**
	 * Регистрирует стили для "части" плагина
	 * @since    2.0.0
	 */
	public function admin_enqueue_styles() {
		wp_enqueue_style( "{$this->plugin_name}-control", plugin_dir_url( dirname( __FILE__ ) ) . 'admin/styles/admin-control.css', [], $this->version, 'all' );
	}


	/**
	 * Регистрирует скрипты для "части" плагина
	 * @since    2.0.0
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-datepicker' ); 
		wp_enqueue_style( 'jquery-ui', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/styles/jquery-ui.css', [], '1.11.4', 'all' );
		wp_enqueue_media();
		wp_enqueue_script( "{$this->plugin_name}-control", plugin_dir_url( dirname( __FILE__ ) ) . 'admin/scripts/admin-control.js',  [ 'jquery', 'wp-color-picker' ], $this->version, false );
	}


}