<?php


namespace pstu_dissertation;


/**
 * Файл, который определяет основной класс плагина
 *
 * @link       https://events.pstu.edu/konkurs-energy/
 * @since      2.0.0
 *
 * @package    pstu_dissertation
 * @subpackage pstu_dissertation/includes
 */

/**
 * Основной класс плагина
 * @since      2.0.0
 * @package    pstu_dissertation
 * @subpackage pstu_dissertation/includes
 * @author     Your Name <chomovva@gmail.com>
 */
class Manager {

	/**
	 * Загрузчик, который отвечает за регистрацию всех хуков, фильтров и шорткодов.
	 * @since    2.0.0
	 * @access   protected
	 * @var      Plugin_Name_Loader    $loader    Регистрирует хуки, фильтры, шорткоды
	 */
	protected $loader;

	/**
	 * Уникальый идентификаторв плагина
	 * @since    2.0.0
	 * @access   protected
	 * @var      string    $plugin_name    Строка используется для идентификации плагина в Wp и интернационализации
	 */
	protected $plugin_name;

	/**
	 * Текущая версия плагина
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string    $version    Текущая версия плагина
	 */
	protected $version;

	/**
	 * Инициализация переменных плагина, подключение файлов.
	 * @since    2.0.0
	 */
	public function __construct() {
		$this->version = ( defined( 'PSTU_DISSERTATION_VERSION' ) ) ? PSTU_DISSERTATION_VERSION : '2.0.0';
		$this->plugin_name = ( defined( 'PSTU_DISSERTATION_NAME' ) ) ? PSTU_DISSERTATION_NAME : 'pstu_dissertation';
		$this->load_dependencies();
		$this->set_locale();
		$this->init();
		if ( is_admin() && ! wp_doing_ajax() ) {
			$this->define_admin_hooks();
		} else {
			$this->define_public_hooks();
		}
	}

	/**
	 * Подключает файлы с "зависимостями"
	 * @since    2.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/abstract-part.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/abstract-part-post_type.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/abstract-part-taxonomy.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/abstract-part-user_role.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-control.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-parsedown.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-part-post_type-dissertation.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-part-taxonomy-science_counsil.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-part-user_role-science_counsil_editor.php';

		/**
		 * Классы отвечающие за функционал админки
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/trait-controls.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-part-admin-readme_tab.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-part-admin-update_tab.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-part-admin-settings-manager.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-part-admin-post_type-dissertation.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-part-admin-taxonomy-science_counsil.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-part-admin-user_role-science_counsil_editor.php';

		/**
		 * Классы отвечающие за функционал публичной части сайта
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-part-public-post_type-dissertation.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-part-public-taxonomy-science_counsil.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-part-public-user_role-science_counsil_editor.php';

		/**
		 * Класс, отвечающий за регистрацию хуков, фильтров и шорткодов.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-loader.php';

		/**
		 * Класс отвечающий за интернализацию.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-i18n.php';

		/**
		 * Касс, который регистрирует типов записей и таксономий.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-init.php';

		$this->loader = new Loader();

	}

	/**
	 * Добавлет функциональность для интернационализации.
	 * @since    2.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new I18n( $this->get_plugin_name() );
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}


	/**
	 * Регистрирует новые типы постов и таксономии
	 * @since    2.0.0
	 * @access   private
	 */
	private function init() {

		$plugin_init = new Init( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'init', $plugin_init, 'register_taxonomy_for_object_type', 20, 0 );

		$class_post_type_dissertation = new PartPostTypeDessertation( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'init', $class_post_type_dissertation, 'register_post_type', 10, 0 );
		$this->loader->add_action( 'wp', $class_post_type_dissertation, 'registration_deletion_of_old_posts', 10, 0 );
		$this->loader->add_action( 'delete_old_' . $class_post_type_dissertation->get_part_name(), $class_post_type_dissertation, 'delete_old_posts_run', 10, 0 );

		$class_taxonomy_science_counsil = new PartTaxonomyScienceCounsil( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'init', $class_taxonomy_science_counsil, 'register_taxonomy', 10, 0 );

		$class_user_role_science_counsil_editor = new PartUserRoleScienceCounsilEditor( $this->get_plugin_name(), $this->get_version() );

		$class_part_update_tab = new PartAdminUpdateTab( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'get_header', $class_part_update_tab, 'enable_maintenance_mode', 10, 0 );
		$this->loader->add_action( 'wp_ajax_' . $this->get_plugin_name() . '_settings', $class_part_update_tab, 'run_ajax', 10, 0 );

	}


	/**
	 * Регистрация хуков и фильтров для админ части плагина
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		// роль пользователя "Редактор научного совета"
		$class_user_role_science_counsil_editor = new PartAdminUserRoleScienceCounsilEditor( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_init', $class_user_role_science_counsil_editor, 'add_capabilities', 10, 0 );
		$this->loader->add_action( 'pre_get_posts', $class_user_role_science_counsil_editor, 'posts_of_the_current_user', 10, 1 );
		$this->loader->add_action( 'admin_menu', $class_user_role_science_counsil_editor, 'remove_menus', 10, 0 );
		$this->loader->add_action( 'admin_init', $class_user_role_science_counsil_editor, 'menus_redirect', 10, 0 );

		// элементы форм
		$object_control = new Control( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $object_control, 'admin_enqueue_styles', 10, 0 );
		$this->loader->add_action( 'admin_enqueue_scripts', $object_control, 'admin_enqueue_scripts', 10, 0 );

		// страница настроек плагина
		$class_part_settings_manager = new PartAdminSettingsManager( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_menu', $class_part_settings_manager, 'add_page' );
		$this->loader->add_action( 'current_screen', $class_part_settings_manager, 'run_tab' );
		$this->loader->add_action( 'admin_init', $class_part_settings_manager, 'register_settings', 10, 0 );
		$this->loader->add_action( 'wp_ajax_' . $this->get_plugin_name() . '_settings', $class_part_settings_manager, 'run_ajax', 20, 0 );

		// вывод справки по плагину
		$class_part_readme_tab = new PartAdminReadmeTab( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_filter( $this->get_plugin_name() . '_settings-tabs', $class_part_readme_tab, 'add_settings_tab', 10, 1 );
		$this->loader->add_action( $this->get_plugin_name() . '_settings-form_' . $class_part_readme_tab->get_part_name(), $class_part_readme_tab, 'render_tab', 10, 1 );

		// вкладка обновления
		$class_part_update_tab = new PartAdminUpdateTab( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_notices', $class_part_update_tab, 'check_update_notice', 10, 0 );
		$this->loader->add_filter( $this->get_plugin_name() . '_settings-tabs', $class_part_update_tab, 'add_settings_tab', 10, 1 );
		$this->loader->add_action( $this->get_plugin_name() . '_settings-form_' . $class_part_update_tab->get_part_name(), $class_part_update_tab, 'render_tab', 10, 1 );
		$this->loader->add_action( 'admin_enqueue_scripts', $class_part_update_tab, 'enqueue_scripts', 10, 0 );

		// тип поста "Диссертации"
		$class_post_type_dissertation = new PartAdminPostTypeDessertation( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'add_meta_boxes', $class_post_type_dissertation, 'add_meta_box', 10, 1 );
		$this->loader->add_action( 'save_post', $class_post_type_dissertation, 'save_post', 10, 2 );
		$this->loader->add_action( 'admin_enqueue_scripts', $class_post_type_dissertation, 'enqueue_scripts', 10, 0 );
		$this->loader->add_action( $this->get_plugin_name() . '_register_settings', $class_post_type_dissertation, 'register_settings', 10, 1 );
		$this->loader->add_filter( $this->get_plugin_name() . '_settings-tabs', $class_post_type_dissertation, 'add_settings_tab', 10, 2 );
		$this->loader->add_action( $this->get_plugin_name() . '_settings-form_' . $class_post_type_dissertation->get_part_name(), $class_post_type_dissertation, 'render_settings_form', 10, 1 );
		$this->loader->add_action( 'before_delete_post', $class_post_type_dissertation, 'delete_post_attachment', 10, 2 );
		$this->loader->add_filter( 'pre_trash_post', $class_post_type_dissertation, 'disable_trash_for_post_type', 10, 2 );
		$this->loader->add_action( 'added_post_meta', $class_post_type_dissertation, 'attach_file_to_post', 10, 4 );
		$this->loader->add_action( 'updated_post_meta', $class_post_type_dissertation, 'attach_file_to_post', 10, 4 );
		$this->loader->add_filter( 'manage_' . $class_post_type_dissertation->get_post_type_name() . '_posts_columns', $class_post_type_dissertation, 'add_custom_columns', 10, 1 );
		$this->loader->add_action( 'manage_' . $class_post_type_dissertation->get_post_type_name() . '_posts_custom_column', $class_post_type_dissertation, 'render_custom_columns', 5, 2 );
		$this->loader->add_action( 'admin_print_footer_scripts-edit.php', $class_post_type_dissertation, 'render_custom_columns_styles', 10, 4 );

		// таксономия "Научный совет"
		$class_taxonomy_science_counsil = new PartAdminTaxonomyScienceCounsil( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'add_meta_boxes', $class_taxonomy_science_counsil, 'add_meta_box', 10, 1 );
		$this->loader->add_action( 'save_post', $class_taxonomy_science_counsil, 'save_post', 10, 2 );
		$this->loader->add_action( 'manage_edit-' . $class_taxonomy_science_counsil->get_taxonomy_name() . '_columns', $class_taxonomy_science_counsil, 'add_columns', 10, 1 );
		$this->loader->add_action( 'manage_' . $class_taxonomy_science_counsil->get_taxonomy_name() . '_custom_column', $class_taxonomy_science_counsil, 'render_custom_columns', 10, 3 );

	}


	/**
	 * Регистрация хуков и фильтров для публично части плагина
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		// роль пользователя "Редактор научного совета"
		$class_user_role_science_counsil_editor = new PartPublicUserRoleScienceCounsilEditor( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_filter( 'login_redirect', $class_user_role_science_counsil_editor, 'login_redirect_filter', 10, 3 );
		$this->loader->add_filter( 'ajax_query_attachments_args', $class_user_role_science_counsil_editor, 'attachments_of_the_current_user', 10, 1 );
		
		// тп поста "Диссертации"
		$class_post_type_dissertation = new PartPublicPostTypeDessertation( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_filter( 'the_content', $class_post_type_dissertation, 'filter_single_content', 10, 1 );
		$this->loader->add_filter( 'the_title', $class_post_type_dissertation, 'filter_title', 10, 2 );
		$this->loader->add_shortcode( $class_post_type_dissertation->get_part_name() . '_' . 'author', $class_post_type_dissertation, 'shortode_manager', 10, 3 );
		$this->loader->add_shortcode( $class_post_type_dissertation->get_part_name() . '_' . 'publication', $class_post_type_dissertation, 'shortode_manager', 10, 3 );
		$this->loader->add_shortcode( $class_post_type_dissertation->get_part_name() . '_' . 'protection', $class_post_type_dissertation, 'shortode_manager', 10, 3 );
		$this->loader->add_shortcode( $class_post_type_dissertation->get_part_name() . '_' . 'protection_time', $class_post_type_dissertation, 'shortode_manager', 10, 3 );
		$this->loader->add_shortcode( $class_post_type_dissertation->get_part_name() . '_' . 'file_link', $class_post_type_dissertation, 'shortode_manager', 10, 3 );
		$this->loader->add_shortcode( $class_post_type_dissertation->get_part_name() . '_' . 'abstract_link', $class_post_type_dissertation, 'shortode_manager', 10, 3 );
		$this->loader->add_shortcode( $class_post_type_dissertation->get_part_name() . '_' . 'opponents', $class_post_type_dissertation, 'shortode_manager', 10, 3 );
		
		// таксономия "Научный совет"
		$class_taxonomy_science_counsil = new PartPublicTaxonomyScienceCounsil( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_shortcode( $class_taxonomy_science_counsil->get_part_name() . '_' . 'list_of_posts', $class_taxonomy_science_counsil, 'shortode_manager', 10, 3 );

	}


	/**
	 * Запск загрузчика для регистрации хукой, фильтров и шорткодов в WordPress
	 * @since    2.0.0
	 */
	public function run() {
		$this->loader->run();
	}


	/**
	 * Возвращает имя плагина используется для уникальной идентификации его в контексте
	 * WordPress и для определения функциональности интернационализации.
	 * @since     2.0.0
	 * @return    string    Идентификатор плагина
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}


	/**
	 * Возвращает ссылку на класс, который управляет хуками с плагином.
	 * @since     2.0.0
	 * @return    Loader    Класс "загрузчик" хуков, фильтров и шорткодов.
	 */
	public function get_loader() {
		return $this->loader;
	}


	/**
	 * Возвращает номер версии плагина. Используется при регистрации файлов
	 * скриптов, стилей и обновлении плагина.
	 * @since     2.0.0
	 * @return    string    Номер текущей версии плагина
	 */
	public function get_version() {
		return $this->version;
	}


}