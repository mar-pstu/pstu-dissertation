<?php


namespace pstu_dissertation;


if ( ! defined( 'ABSPATH' ) ) {	exit; };


class Control {


	/**
	 * Имя плагина и слаг метаполей
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    Уникальный идентификтор плагина в контексте WP
	 */
	protected $plugin_name;


	/**
	 * Версия плагина
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    Номер текущей версии плагина
	 */
	protected $version;


	/**
	 * Тип элемента цправления
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    Номер текущей версии плагина
	 */
	protected $type;


	/**
	 * Дополнительные аргументы
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    Номер текущей версии плагина
	 */
	protected $args;


	/**
	 * HTMl-код элемента управления
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    Номер текущей версии плагина
	 */
	protected $html;


	/**
	 * Инициализация класса и установка его свойства.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name       Имя плагин и слаг метаполей
	 * @param    string    $version           Текущая версия
	 * @param    string    $plugin_name       Имя плагин и слаг метаполей
	 * @param    string    $version           Текущая версия
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->type = 'text';
		$this->args = [];
	}


	/**
	 * Устанавливает тип контрола
	 * @param   string   $type   дополнительные параметры
	 */
	public function set_type( $type ) {
		$this->type = $type;
	}


	/**
	 * Устанавливает дополнитеьные параметры
	 * @param   array   $args   дополнительные параметры
	 */
	public function set_args( $args ) {
		$this->args = array_merge( [
			'atts' => [],
		], $args );
	}


	/**
	 * Формирует html-код контрола
	 */
	public function render() {
		switch ( $this->type ) {

			case 'composite':
				$this->args = array_merge( array(
					'controls' => [],
				), $this->args );
				$this->args[ 'atts' ][ 'class' ] .= ' composite-field';
				$this->html = '<div ' . $this->render_atts() . ' >' . implode( "\r\n", array_map( function ( $control_params ) {
					$control_object = new Control( $this->plugin_name, $this->varsion );
					$control_params = array_merge( [
						'type' => 'text',
						'args' => [],
					], ( is_array( $control_params ) ) ? $control_params : [] );
					$control_object->set_type( $control_params[ 'type' ] );
					$control_object->set_args( $control_params[ 'args' ] );
					$control_object->render();
					return $control_object->get_html();
				}, $this->args[ 'controls' ] ) ) . '</div>';
				break;

			case 'list':
				$this->args = array_merge( [
					'name'     => 'list',
					'template' => $this->render_input( $name ),
				], $this->args );
				if ( ! is_array( $this->args[ 'value' ] ) ) {
					$this->args[ 'value' ] = wp_parse_list( $this->args[ 'value' ] );
				}
				if ( empty( $this->args[ 'value' ] ) ) {
					$data = '[]';
				} else {
					$this->args[ 'value' ] = array_map( function ( $item ) {
						return array( 'value' => $item );
					}, $this->args[ 'value' ] );
					$data = wp_json_encode( $this->args[ 'value' ] );
				}
				if ( ! empty( trim( $this->args[ 'template' ] ) ) ) {;
					ob_start();
					?>
						<div class="list-of-templates" data-list-of-templates="<?php echo $name; ?>" >
							<script type="text/javascript">
								var <?php echo $this->args[ 'name' ]; ?>_data = <?php echo $data; ?>;
							</script>
							<div class="list"></div>
							<button  class="button button-primary add-button" type="button"><?php _e( 'Добавить строку', $this->plugin_name ); ?></button>
							<script type="text/html" id="tmpl-<?php echo $name; ?>">
								<div class="list-item">
									<div class="template">
										<?php echo $this->args[ 'template' ]; ?>	
									</div>
									<button type="button" class="button remove-button">&times;</button>
								</div>
							</script>
						</div>
					<?
					$html = ob_get_contents();
					ob_end_clean();
				}
				return $html;
				break;

			case 'dropdown':
				$this->args = array_merge( [
					'choices'           => [],
					'selected'          => [],
					'echo'              => false,
					'show_option_none'  => '-',
					'option_none_value' => '',
					'atts'              => [],
				], $this->args );
				if ( is_array( $this->args[ 'choices' ] ) && ! empty( $this->args[ 'choices' ] ) ) {
					$this->html = '<select ' . $this->render_atts() . ' >';
					if ( ! is_array( $this->args[ 'selected' ] ) ) {
						$this->args[ 'selected' ] = [ $this->args[ 'selected' ] ];
					}
					if ( $this->args[ 'show_option_none' ] ) {
						$this->html .= sprintf( '<option value="%1$s">%2$s</option>', esc_attr( $this->args[ 'option_none_value' ] ), $this->args[ 'show_option_none' ] );
					}
					foreach ( $this->args[ 'choices' ] as $value => $label ) {
						$selected = selected( true, in_array( $value, $this->args[ 'selected' ] ), false );
						$this->html .= sprintf( '<option value="%1$s" %2$s>%3$s</option>', $value, $selected, $label );
					}
					$this->html .= '</select>';
				}
				break;

			case 'checkbox':
			case 'radio':
				$this->args[ 'atts' ][ 'type' ] = $this->type;
				$this->html = '<input type="' . $this->type . '" ' . $this->render_atts() . ' > ' . $this->args[ 'label' ] . '</label>';
				if ( array_key_exists( 'label', $this->args ) && ! empty( $this->args[ 'label' ] ) ) {
					$this->html = '<label class="' . $this->type . '">' . $this->html . '</label>';
				}
				break;
			
			case 'textarea':
				$this->html = '<textarea ' . $this->render_atts() . ' >' . $this->args[ 'value' ] . '</textarea>';
				break;
		
			case 'text':
			case 'number':
			case 'email':
			case 'password':
			case 'hidden':
			case 'date':
			default:
				$this->html = '<input type="' . $this->type . '" ' . $this->render_atts() . ' >';
				break;
		
		}
	}



	/**
	 * Создаёт редактируемый список полей
	 *
	 * @var   string    $name     имя поля
	 * @var   array     $value    значение полей
	 * @var   array()   $args     дополниьельные параметры
	 */
	function render_list_of_templates( $name, $value, $args = [] ) {
		
	}



	/**
	 * Возвращает html-код контрола
	 * @return   string   html-код
	 */
	public function get_html() {
		return $this->html;
	}


	public function render_atts() {
		$html = '';
		if ( ! empty( $this->args[ 'atts' ] ) ) {
			foreach ( $this->args[ 'atts' ] as $key => $value ) {
				$html .= ' ' . $key . '="' . $value . '"';
			}
		}
		return $html;
	}


	/**
	 * Регистрирует стили для "части" плагина
	 * @since    2.0.0
	 */
	public function admin_enqueue_styles() {
		wp_enqueue_style( "{$this->plugin_name}-control", plugin_dir_url( dirname( __FILE__ ) ) . 'admin/styles/admin-control.css', [], $this->version, 'all' );
	}


	/**
	 * Регистрирует скрипты для "части" плагина
	 * @since    2.0.0
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-datepicker' ); 
		wp_enqueue_style( 'jquery-ui', plugin_dir_url( dirname( __FILE__ ) ) . 'admin/styles/jquery-ui.css', [], '1.11.4', 'all' );
		wp_enqueue_media();
		wp_enqueue_script( "{$this->plugin_name}-control", plugin_dir_url( dirname( __FILE__ ) ) . 'admin/scripts/admin-control.js',  [ 'jquery', 'wp-color-picker' ], $this->version, false );
	}


}