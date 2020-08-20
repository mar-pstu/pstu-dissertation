<?php


namespace pstu_dissertation;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Абстрактный класс "частей" плагина
 * @package    pstu_dissertation
 * @subpackage pstu_dissertation/includes
 * @author     chomovva <chomovva@gmail.com>
 */
abstract class Part {

	/**
	 * Имя плагина и слаг метаполей
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $plugin_name    Уникальный идентификтор плагина в контексте WP
	 */
	protected $plugin_name;

	/**
	 * Версия плагина
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version    Номер текущей версии плагина
	 */
	protected $version;


	/**
	 * Идентификатор части плагина
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version    идентификатор "части" плагина
	 */
	protected $part_name;


	/**
	 * Инициализация класса и установка его свойства.
	 * @since    2.0.0
	 * @param    string    $plugin_name       Имя плагин и слаг метаполей
	 * @param    string    $version           Текущая версия
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}


	/**
	 * Вывод информации о переменной 
	 * @since    2.0.0
	 * @param    mixed      $var       переменная
	 */
	public function var_dump( $var ) {
		echo "<pre>";
		var_dump( $var );
		echo "</pre>";
	}


	/**
	 * Возвращает имя плагина используется для уникальной идентификации его в контексте
	 * WordPress и для определения функциональности интернационализации.
	 * @since     2.0.0
	 * @return    string    Идентификатор плагина
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}


	/**
	 * Возвращает номер версии плагина. Используется при регистрации файлов
	 * скриптов, стилей и обновлении плагина.
	 * @since     2.0.0
	 * @return    string    Номер текущей версии плагина
	 */
	public function get_version() {
		return $this->version;
	}


	/**
	 * Возвращает идентификатор "части" плагина
	 * @since     2.0.0
	 * @return    string    Номер текущей версии плагина
	 */
	public function get_part_name() {
		return $this->part_name;
	}


}