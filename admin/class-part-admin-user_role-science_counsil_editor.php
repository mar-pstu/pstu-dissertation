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
class PartAdminUserRoleScienceCounsilEditor extends PartUserRoleScienceCounsilEditor {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->part_name = 'science_counsil_editor_admin';
	}


	/**
	 * Добавляет новую возможность указанной роли или пользователю.
	 */
	function add_capabilities() {
		$science_counsil_editor = get_role( $this->user_role_name );
		if ( ! is_null( $science_counsil_editor ) ) {
			$capability_type = 'dissertation';
			$science_counsil_editor->add_cap( "edit_published_{$capability_type}" );
			$science_counsil_editor->add_cap( "publish_{$capability_type}" );
			$science_counsil_editor->add_cap( "delete_published_{$capability_type}" );
			$science_counsil_editor->add_cap( "edit_{$capability_type}" );
			$science_counsil_editor->add_cap( "delete_{$capability_type}" );
			$science_counsil_editor->add_cap( "read_{$capability_type}" );
			$science_counsil_editor->add_cap( "read_private_{$capability_type}" );
		}
	}


	/**
	 * Удаляем пункты меню консоли
	 */
	public function remove_menus() {
		if ( self::is_only_user_role( $this->user_role_name ) ) {
			remove_menu_page( 'index.php' );
		}
	}


	/**
	 * Редиректы с удалённых пунктов меню
	 */
	public function menus_redirect() {
		global $pagenow;
		if ( self::is_only_user_role( $this->user_role_name ) && in_array( $pagenow, [ 'index.php' ] ) ) {
			wp_redirect( admin_url( '/edit.php?post_type=dissertation' ) );
			exit;
		}
	}


	/**
	 * Показывать посты только текущего пользователя
	 * @param     WP_Query     $query     объект запроса WP_Query
	 */
	public function posts_of_the_current_user( $query ) {
		if ( self::is_only_user_role( $this->user_role_name ) ) {
			$query->set( 'author', get_current_user_id() );
		}
		return $query;
	}


	/**
	 * Редирект после аворизации пользователя
	 * @param   string             $redirect_to             URL который нашел WP, чтобы перенаправить.
	 * @param   string             $requested_redirect_to   Оригинальный «сырой» URL перенаправления, из параметра $_REQUEST['redirect_to']
	 * @param   WP_USER|WP_Error   $user                    Текущий пользователь (объект WP_User), если авторизация прошла успешно. Или объект WP_Error
	 * @return  string
	 */
	public function login_redirect_filter( $redirect_to, $requested_redirect_to, $user ) {
		if ( self::is_only_user_role( $this->user_role_name, $user->ID ) ) {
			$redirect_to = admin_url( '/edit.php?post_type=dissertation' );
		}
		return $redirect_to;
	}

}