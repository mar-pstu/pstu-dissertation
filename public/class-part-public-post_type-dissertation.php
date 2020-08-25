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


	/**
	 * Формирует html-код с информацией о конкурсной работе
	 * @param  string $content содержимое записи
	 * @return string          обработанное содержимое записи
	 */
	public function filter_single_content( $content = '' ) {
		if ( get_post_type( get_the_ID() ) == $this->post_type_name ) {
			$path = $this->get_template_file_path( "single-content-{$this->post_type_name}.php" );
			if ( $path ) {
				ob_start();
				include $path;
				$content = do_shortcode( ob_get_contents(), false );
				ob_end_clean();
			}
		}
		return $content;
	}


	/**
	 * Формирует html-код с информацией о конкурсной работе
	 * @param  string  $title  заголовок
	 * @param  int     $id     идентификатор поста
	 * @return string          заголовок
	 */
	public function filter_title( $title, $post_id = null ) {
		$author = self::render_author( $post_id );
		if ( ! empty( $author ) ) {
			$title = $author . ' - ' . $title;
		}
		return $title;
	}


	/**
	 * Ищет шаблон для вывода контента в текущей тему
	 * @since    1.0.0
	 * @param    string|array  $file  имя файла
	 * @return   string               путь к файлу-шаблону
	 */
	public function get_template_file_path( $file_names ) {
		$result = false;
		if ( ! is_array( $file_names ) ) {
			$file_names = [ $file_names ];
		}
		foreach ( $file_names as $file_name ) {
			$file_name = ltrim( $file_name, '/' );
			if ( ! empty( $file_name ) ) {
				$path = $this->plugin_name . '/' . $file_name;
				$path = get_stylesheet_directory() . '/' . $path;
				if ( file_exists( $path ) ) {
					$result = $path;
				} else {
					$path = get_template_directory() . '/' . $path;
					if ( file_exists( $path ) ) {
						$result = $path;
					} else {
						$path = plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/' . $file_name;
						if ( file_exists( $path ) ) {
							$result = $path;
						}
					}
				}
			}
			if ( $result ) {
				break;
			}
		}
		return $result;
	}


	/**
	 * Менеджер шорткодов, выбирает и запускает нужные методы
	 * @param  array       $atts           аргументы шорткода
	 * @param  string      $content        контент между "тегами" шорткода
	 * @param  string|null $shortcode_name имя шорткода
	 * @return string                      html-код
	 */
	public function shortode_manager( $atts = [], $content = '', $shortcode_name = null ) {
		$html = $content;
		if ( null != $shortcode_name ) {
			$key = str_replace( $this->part_name . '_', '', $shortcode_name );
			$atts = shortcode_atts( [
				'post_id' => get_the_ID(),
				'empty'   => '-',
			], $atts, $shortcode_name );
			$atts[ 'post_id' ] = sanitize_key( $atts[ 'post_id' ] );
			if ( $atts[ 'post_id' ] ) {
				switch ( $key ) {
					case 'author':
						$html = self::render_author( $atts[ 'post_id' ] );
						break;
					case 'file_link':
						$html = self::render_link( get_post_meta( $atts[ 'post_id' ], 'dissertation', true ), __( 'Скачать', $this->plugin_name ) );
						break;
					case 'abstract_link':
						$html = self::render_link( get_post_meta( $atts[ 'post_id' ], 'abstract', true ), __( 'Скачать', $this->plugin_name ) );
						break;
					case 'opponents':
						$meta = get_post_meta( $atts[ 'post_id' ], $key, true );
						if ( is_array( $meta ) && ! empty( $meta ) ) {
							$html = '<ul>' . implode( "\r\n", array_map( function ( $item ) {
								$item = array_merge( [
									'last_name'   => '',
									'first_name'  => '',
									'middle_name' => '',
									'degree'      => '',
									'workplace'   => '',
									'opinion'     => '',
								], $item );
								return sprintf(
									'<li><div><strong>%1$s %2$s %3$s</strong> <small>%4$s</small></div><div>%5$s</div></li>',
									$item[ 'last_name' ],
									$item[ 'first_name' ],
									$item[ 'middle_name' ],
									$item[ 'degree' ],
									$item[ 'workplace' ],
									( empty( $item[ 'opinion' ] ) ) ? '' : __( 'Отзыв', $this->plugin_name ) . ': ' . $this->render_link( $item[ 'opinion' ], __( 'Скачать', $this->plugin_name ) )
								);
							}, $meta ) ) . '</ul>';
						}
						break;
					case 'publication':
					case 'protection':
					case 'protection_time':
					default:
						$html = get_post_meta( $atts[ 'post_id' ], $key, true );
						break;
				}
				if ( empty( trim( $html ) ) ) {
					$html = $atts[ 'empty' ];
				}
			}
		}
		return $html;
	}


	public static function render_author( $post_id = null ) {
		$html = '';
		if ( is_null( $post_id ) ) {
			$post_id = get_the_ID();
		}
		$meta = get_post_meta( $post_id, 'author', true );
		if ( is_array( $meta ) && ! empty( $meta ) ) {
			$meta = array_merge( [
				'last_name'   => '',
				'first_name'  => '',
				'middle_name' => '',
			], $meta );
			$html = trim( sprintf(
				'%1$s %2$s %3$s',
				$meta[ 'last_name' ],
				$meta[ 'first_name' ],
				$meta[ 'middle_name' ]
			) );
		}
		return $html;
	}


	/**
	 * Формирует html-код ссылки по переданному url
	 * @param  string     $url    url
	 * @return string             html-код
	 */
	public static function render_link( $url, $label = '' ) {
		$html = '';
		if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
			$html = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_attr( $url ),
				( empty( $label ) ) ? $url : $label
			);
		}
		return $html;
	}


}