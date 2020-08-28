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
class PartPublicTaxonomyScienceCounsil extends PartTaxonomyScienceCounsil {


	function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
		$this->part_name = 'science_counsil_public';
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
				'term_id' => '',
				'empty'   => 'e-',
			], $atts, $shortcode_name );
			$atts[ 'term_id' ] = sanitize_key( $atts[ 'term_id' ] );
			if ( $atts[ 'term_id' ] ) {
				switch ( $key ) {

					case 'list_of_posts':
						$html = self::render_list_of_posts( $atts[ 'term_id' ], $this->plugin_name );
						break;
					
				}
				if ( empty( trim( $html ) ) ) {
					$html = $atts[ 'empty' ];
				}
			}
		}
		return $html;
	}


	public static function render_list_of_posts( $term_id, $plugin_name ) {
		global $post;
		$result = [];
		$entries = get_posts( [
			'numberposts' => -1,
			'science_counsil' => $term_id,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'post_type'   => 'dissertation',
			'suppress_filters' => true,
		] );
		if ( is_array( $entries ) && ! empty( $entries ) ) {
			foreach ( $entries as $entry ) {
				setup_postdata( $post = $entry );
				$result[] = sprintf(
					'<li><a href="%1$s" %2$s >%3$s</a></li>',
					get_the_permalink( $post, false ),
					is_post_type_viewable( 'dissertation' ),
					get_the_title( $post->ID )
				);
			}
			wp_reset_postdata();
		}
		return ( empty( $result ) ) ? '<p>' . __( 'Диссертации не добавлены', $plugin_name ) . '</p>' : '<ul>' . implode( "\r\n", $result ) . '</ul>';
	}


}