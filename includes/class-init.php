<?php


namespace pstu_dissertation;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Регистрирует произвольные типы записи и ппроизвольные таксономии
 *
 * @since      2.0.0
 * @package    pstu_dissertation
 * @subpackage pstu_dissertation/includes
 * @author     chomovva <chomovva@gmail.com>
 */
class Init extends Part {


	/**
	 * Привязывает указанные таксономии к типам постов.
	 */
	public function register_taxonomy_for_object_type() {
		register_taxonomy_for_object_type( 'science_counsil', 'dissertation' );
	}


	/**
	 * Возвращает ассоциативный массив с интервалами времени,
	 * которые используются в настройках плагина
	 */
	public function filter_time_intervals( $time_intervals = [] ) {
		return array_merge( [
			'+1 week'  => __( '+1 неделя', $this->plugin_name ),
			'+2 week'  => __( '+2 недели', $this->plugin_name ),
			'+3 week'  => __( '+3 недели', $this->plugin_name ),
			'+1 month'  => __( '+1 месяц', $this->plugin_name ),
			'+2 month'  => __( '+2 месяца', $this->plugin_name ),
			'+3 month'  => __( '+3 месяца', $this->plugin_name ),
			'+4 month'  => __( '+4 месяца', $this->plugin_name ),
		], ( is_array( $time_intervals ) ) ? $time_intervals : [] );
	}


}