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

	public function register_taxonomy_for_object_type() {
		register_taxonomy_for_object_type( 'science_counsil', 'dissertation' );
	}
	
}