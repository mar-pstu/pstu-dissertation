<?php


namespace pstu_dissertation;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


/**
 * Абстрактный класс "частей" плагина
 * @since      2.0.0
 * @package    pstu_dissertation
 * @subpackage pstu_dissertation/includes
 * @author     chomovva <chomovva@gmail.com>
 */
class PartAdminSettingsManager extends Part {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->part_name = 'settings';
	}


	/**
	 * Создаем страницу настроек плагина
	 * @since      2.0.0
	 */
	public function add_page() {
		add_submenu_page(
			'edit.php?post_type=dissertation',
			__( 'Настройки', $this->plugin_name ),
			__( 'Настройки', $this->plugin_name ),
			'manage_options',
			$this->part_name,
			[ $this, 'render_page' ]
		);
	}


	/**
	 * Генерируем html код страницы настроек
	 */
	public function render_page() {
		$tabs = apply_filters( "{$this->plugin_name}_settings-tabs", [] );
		if ( ! empty( $tabs ) ) {
			$current_tab = ( isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET[ 'tab' ], $tabs ) ) ? $_GET[ 'tab' ] : array_keys( $tabs )[ 0 ];
			$page_title = get_admin_page_title();
			$page_content = $this->render_nav_tab_wrapper( $tabs, $current_tab );
			if ( ! empty( $current_tab ) ) {
				ob_start();
				do_action( $this->plugin_name . '_settings-form_' . $current_tab, $this->part_name );
				$page_content .= ob_get_contents();
			}
			ob_end_clean();
			include dirname( __FILE__ ) . '/partials/admin-page.php';
		}
	}


	/**
	 * Регистрирует настройки плагина
	 * @since      2.0.0
	 */
	public function register_settings() {
		do_action( "{$this->plugin_name}_register_settings", $this->part_name );
	}



	/**
	 * Генериреут html код вкладок
	 * @since      2.0.0
	 * @param      array     $tabs           массив идентификаторов и заголовков вкладок
	 * @param      string    $current_tab    идентификатор текущей вкладки
	 * @return     string                    html-код вкладок
	 */
	protected function render_nav_tab_wrapper( array $tabs, string $current_tab = '' ) {
		$result = [];
		if ( ! empty( $tabs ) ) {
			foreach ( $tabs as $slug => $label ) {
				$result[] = sprintf(
					'<a href="%1$s" class="nav-tab %2$s">%3$s</a>',
					add_query_arg( [ 'tab' => $slug ] ),
					( $slug == $current_tab ) ? 'nav-tab-active' : '',
					$label
				);
			}
		}
		return '<nav class="nav-tab-wrapper wp-clearfix">' . implode( "\r\n", $result ) . '</nav>';
	}



	function render_setting_field( $args ) {
		return;
	}



	/**
	 * Очистка данных
	 * @since      2.0.0
	 * @var        array    $options
	 */
	function sanitize_callback( $options ) {
		return $options;
	}


	/**
	 * Выполняет зарегистрированные действия для этой страницы
	 * @since      2.0.0
	 */
	public function run_tab() {
		$tab = ( isset( $_POST[ 'tab' ] ) ) ? $_POST[ 'tab' ] : '';
		do_action( "{$this->plugin_name}_settings-run_{$tab}" );
	}


}