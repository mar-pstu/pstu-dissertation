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
class PartAdminReadmeTab extends Part {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->part_name = 'readme';
	}


	/**
	 * Фильтр, который добавляет вкладку с опциями
	 * на страницу настроектплагина
	 * @since    2.0.0
	 * @param    array     $tabs     исходный массив вкладок идентификатор вкладки=>название
	 * @return   array     $tabs     отфильтрованный массив вкладок идентификатор вкладки=>название
	 */
	public function add_settings_tab( $tabs ) {
		$tabs[ $this->part_name ] = __( 'Справка', $this->plugin_name );
		return $tabs;
	}


	/**
	 * Генерируем html код страницы настроек
	 */
	public function render_tab() {
		echo $this->get_parsedown_text( plugin_dir_path( dirname( __FILE__ ) ) . 'README.md' );
	}


}