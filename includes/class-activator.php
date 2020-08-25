<?php


namespace pstu_dissertation;


/**
 * Запускается при активации плагина
 *
 * @link       http://cct.pstu.edu
 * @since      2.0.0
 *
 * @package    pstu_dissertation
 * @subpackage pstu_dissertation/includes
 */

/**
 * Запускается при активации плагина.
 * В этом классе находится весь код, который необходимый при активации плагина.
 * @since      2.0.0
 * @package    pstu_dissertation
 * @subpackage pstu_dissertation/includes
 * @author     chomovva <chomovva@gmail.com>
 */
class Activator {

	/**
	 * Действия которые необходимо выполнить при активации
	 * @since    2.0.0
	 */
	public static function activate() {
		add_role( 'science_counsil_editor', __( 'Редактор научного совета', GETGEN_SRM_NAME ), array(
			'read'  => true,
		) );
	}

}
