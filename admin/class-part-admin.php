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
class PartAdmin extends Part {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->part_name = 'admin';
	}


	/**
	 * Регистрирует стили для "части" плагина
	 * @since    2.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->part_name, plugin_dir_url( __FILE__ ) . "styles/{$this->part_name}.css", [], $this->version, 'all' );
	}


	/**
	 * Регистрирует скрипты для "части" плагина
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-datepicker' ); 
		wp_enqueue_style( 'jquery-ui', plugin_dir_url( __FILE__ ) . 'styles/jquery-ui.css', [], '1.11.4', 'all' );
		wp_enqueue_media();
		wp_enqueue_script( $this->part_name, plugin_dir_url( __FILE__ ) . "scripts/{$this->part_name}.js",  [ 'jquery', 'wp-color-picker' ], $this->version, false );
	}


}