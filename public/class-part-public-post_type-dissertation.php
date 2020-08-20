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
class PartPublicPostTypeDessertation extends PartPostTypeDessertation {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->part_name = 'dissertation_public';
	}


}