<?php


namespace pstu_dissertation;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Класс отвечающий за функционал консоли для рли пользователя
 * "Секретарь научного совета"
 * @package    pstu_dissertation
 * @subpackage pstu_dissertation/includes
 * @author     chomovva <chomovva@gmail.com>
 */
class PartPublicUserRoleScienceCounsilEditor extends PartUserRoleScienceCounsilEditor {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->part_name = 'science_counsil_editor_public';
	}


	/**
	 * Редирект после аворизации пользователя
	 * @param   string             $redirect_to             URL который нашел WP, чтобы перенаправить.
	 * @param   string             $requested_redirect_to   Оригинальный «сырой» URL перенаправления, из параметра $_REQUEST['redirect_to']
	 * @param   WP_USER|WP_Error   $user                    Текущий пользователь (объект WP_User), если авторизация прошла успешно. Или объект WP_Error
	 * @return  string
	 */
	function login_redirect_filter( $redirect_to, $requested_redirect_to, $user ) {
		if ( isset( $user->ID ) && self::is_only_user_role( $this->user_role_name, $user->ID ) ) {
			$redirect_to = admin_url( '/edit.php?post_type=dissertation' );
		}
		return $redirect_to;
	}



	/**
	 * Показывать медиафайлы только текущего пользователя
	 * @param     array     $query    параметры
	 */
	public function attachments_of_the_current_user( $query_args ) {
		if ( self::is_only_user_role( $this->user_role_name ) ) {
			$query_args[ 'author' ] = get_current_user_id();
		}
		return $query_args;
	}


}