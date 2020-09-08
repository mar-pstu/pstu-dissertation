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
		$options = get_option( PSTU_DISSERTATION_NAME );
		if ( ! is_array( $options ) && ! array_key_exists( 'version', $options ) && empty( $options[ 'version' ] ) ) {
			$options = [
				'version'           => PSTU_DISSERTATION_VERSION,
				'updating_progress' => false,
			];
			update_option( PSTU_DISSERTATION_NAME, $options );
		}
		add_role( 'science_counsil_editor', __( 'Редактор научного совета', PSTU_DISSERTATION_NAME ), [
			'read'         => true,
			'upload_files' => true,
			'delete_posts' => true,
			'delete_published_posts' => true,
			'manage_media_library' => true,
		] );
		wp_clear_scheduled_hook( 'delete_old_dissertation-run' );
		wp_clear_scheduled_hook( 'delete_old_dissertation-notification' );
		wp_schedule_event( time(), 'daily', 'delete_old_dissertation-run');
		wp_schedule_event( time(), 'daily', 'delete_old_dissertation-notification');
	}


}