<?php


namespace pstu_dissertation;


/**
 * Запускается при деактивации плагина
 *
 * @link       http://cct.pstu.edu
 * @since      2.0.0
 *
 * @package    pstu_dissertation
 * @subpackage pstu_dissertation/includes
 */

/**
 * Запускается при деактивации плагина
 *
 * В этом классе находится весь код, который необходимый при деактивации плагина.
 *
 * @since      2.0.0
 * @package    pstu_dissertation
 * @subpackage pstu_dissertation/includes
 * @author     chomovva <chomovva@gmail.com>
 */
class Deactivator {

	/**
	 * Действия при деактивации
	 *
	 * @since    2.0.0
	 */
	public static function deactivate() {
		remove_role( 'science_counsil_editor' );
	}

}
