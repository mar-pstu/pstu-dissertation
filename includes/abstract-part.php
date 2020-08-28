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
	 * Формирует текст MD
	 * @param  string $file_path путь к текстовому файлу
	 * @return string            html
	 */
	protected function get_parsedown_text( $file_path ) {
		$result = '';
		if ( file_exists( $file_path ) ) {
			$class_parsedown = new \Parsedown();
			$result = $class_parsedown->text( file_get_contents( $file_path ) );
		}
		return $result;
	}


	/**
	 * Проверяет ассоциативный ли массив
	 * @param    array   $arr   массив для проверки
	 * @return   bool           результат проверки
	 */
	public static function is_assoc_array( array $arr ) {
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}


	/**
	 * Проверяет является ли сторока url адресом
	 * @param    string   $string   исходная строка
	 * @return   bool               результат проверки
	 */
	public static function is_url( $string ) {
		return filter_var( $string, FILTER_VALIDATE_URL );
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