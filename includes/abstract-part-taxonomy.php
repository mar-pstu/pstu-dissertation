<?php


namespace pstu_dissertation;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Абстрактный класс для произвольных таксономий плагина
 *
 * @package    pstu_dissertation
 * @subpackage pstu_dissertation/includes
 * @author     chomovva <chomovva@gmail.com>
 */
abstract class Taxonomy extends Part {


	/**
	 * Идентификатор (имя) типа поста
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version      идентификатор типа поста
	 */
	protected $taxonomy_name;


	/**
	 * Метаполя
	 * @since    2.0.0
	 * @access   private
	 * @var      array     $meta_fields  идентификатор => имя поля
	 */
	protected $meta_fields;


	/**
	 * Возвращает идентификатор (имя) типа поста
	 * @since     2.0.0
	 * @return    string    Номер текущей версии плагина
	 */
	public function get_taxonomy_name() {
		return $this->taxonomy_name;
	}


}