<?php


namespace pstu_dissertation;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Абстрактный класс для произвольных ролей пользователей
 *
 * @package    pstu_dissertation
 * @subpackage pstu_dissertation/includes
 * @author     chomovva <chomovva@gmail.com>
 */
abstract class UserRole extends Part {


	/**
	 * Идентификатор (имя) типа поста
	 * @since    2.0.0
	 * @access   private
	 * @var      string    $version      идентификатор типа поста
	 */
	protected $user_role_name;


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
	public function get_user_role_name() {
		return $this->user_role_name;
	}


	/**
	 * Проверяет единственная ли это роль пользователя 
	 * @since    2.0.0
	 * @param    string    $role_name   идентификатор роли пользователя
	 * @param    int       $user_id     идентификатор пользователя, по умолчанию текущий
	 * @return   bool                   результат проверки
	 */
	public static function is_only_user_role( $role_name, $user_id = null ) {
		$result = false;
		$user = is_numeric( $user_id ) ? get_userdata( $user_id ) : wp_get_current_user();
		if ( $user ) {
			$result = in_array( $role_name, ( array ) $user->roles ) && 1 == count( $user->roles );
		}
		return $result;
	}


}