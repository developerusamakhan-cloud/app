<?php
/**
 * Allowed HTML for escaping widget and admin markup with wp_kses().
 *
 * @package AI_Site_Assistant
 */

defined( 'ABSPATH' ) || exit;

/**
 * Allowed tag lists.
 */
class Aisa_Kses {

	/**
	 * Cached full list.
	 *
	 * @var array|null
	 */
	private static $html = null;

	/**
	 * Cached SVG list.
	 *
	 * @var array|null
	 */
	private static $svg = null;

	/**
	 * Attributes allowed on every tag.
	 *
	 * @return array<string, bool>
	 */
	private static function global_attributes() {
		return array(
			'class'            => true,
			'id'               => true,
			'title'            => true,
			'role'             => true,
			'dir'              => true,
			'lang'             => true,
			'hidden'           => true,
			'tabindex'         => true,
			'style'            => true,
			'draggable'        => true,
			'data-*'           => true,
			'aria-controls'    => true,
			'aria-current'     => true,
			'aria-describedby' => true,
			'aria-expanded'    => true,
			'aria-haspopup'    => true,
			'aria-hidden'      => true,
			'aria-label'       => true,
			'aria-labelledby'  => true,
			'aria-live'        => true,
			'aria-pressed'     => true,
			'aria-relevant'    => true,
			'aria-selected'    => true,
		);
	}

	/**
	 * Inline SVG icons.
	 *
	 * @return array
	 */
	public static function svg() {
		if ( null !== self::$svg ) {
			return self::$svg;
		}
		$shape     = array(
			'class'           => true,
			'd'               => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'transform'       => true,
			'opacity'         => true,
		);
		self::$svg = array(
			'svg'      => array(
				'class'       => true,
				'viewbox'     => true,
				'width'       => true,
				'height'      => true,
				'fill'        => true,
				'stroke'      => true,
				'xmlns'       => true,
				'role'        => true,
				'aria-hidden' => true,
				'aria-label'  => true,
				'focusable'   => true,
			),
			'g'        => $shape,
			'path'     => $shape,
			'circle'   => array_merge(
				$shape,
				array(
					'cx' => true,
					'cy' => true,
					'r'  => true,
				)
			),
			'rect'     => array_merge(
				$shape,
				array(
					'x'      => true,
					'y'      => true,
					'width'  => true,
					'height' => true,
					'rx'     => true,
					'ry'     => true,
				)
			),
			'line'     => array_merge(
				$shape,
				array(
					'x1' => true,
					'y1' => true,
					'x2' => true,
					'y2' => true,
				)
			),
			'polyline' => array_merge( $shape, array( 'points' => true ) ),
			'polygon'  => array_merge( $shape, array( 'points' => true ) ),
		);
		return self::$svg;
	}

	/**
	 * Post HTML plus SVG icons and the form controls the widget and settings screens use.
	 *
	 * @return array
	 */
	public static function html() {
		if ( null !== self::$html ) {
			return self::$html;
		}
		$tags = array_merge( wp_kses_allowed_html( 'post' ), self::svg() );
		$form = array(
			'form'     => array(
				'action'     => true,
				'method'     => true,
				'novalidate' => true,
				'enctype'    => true,
			),
			'input'    => array(
				'type'         => true,
				'name'         => true,
				'value'        => true,
				'checked'      => true,
				'disabled'     => true,
				'readonly'     => true,
				'required'     => true,
				'placeholder'  => true,
				'min'          => true,
				'max'          => true,
				'step'         => true,
				'maxlength'    => true,
				'pattern'      => true,
				'list'         => true,
				'autocomplete' => true,
				'spellcheck'   => true,
				'inputmode'    => true,
				'size'         => true,
			),
			'textarea' => array(
				'name'         => true,
				'rows'         => true,
				'cols'         => true,
				'placeholder'  => true,
				'maxlength'    => true,
				'required'     => true,
				'readonly'     => true,
				'disabled'     => true,
				'autocomplete' => true,
				'spellcheck'   => true,
			),
			'select'   => array(
				'name'     => true,
				'multiple' => true,
				'disabled' => true,
				'required' => true,
			),
			'option'   => array(
				'value'    => true,
				'selected' => true,
				'disabled' => true,
			),
			'optgroup' => array(
				'label'    => true,
				'disabled' => true,
			),
			'datalist' => array(),
			'button'   => array(
				'type'     => true,
				'name'     => true,
				'value'    => true,
				'disabled' => true,
			),
			'label'    => array( 'for' => true ),
			'output'   => array( 'for' => true ),
			'progress' => array(
				'value' => true,
				'max'   => true,
			),
			'meter'    => array(
				'value' => true,
				'min'   => true,
				'max'   => true,
			),
			'img'      => array(
				'src'      => true,
				'srcset'   => true,
				'sizes'    => true,
				'alt'      => true,
				'width'    => true,
				'height'   => true,
				'loading'  => true,
				'decoding' => true,
			),
			'a'        => array(
				'href'     => true,
				'target'   => true,
				'rel'      => true,
				'download' => true,
			),
		);
		foreach ( $form as $tag => $attrs ) {
			$tags[ $tag ] = array_merge( $tags[ $tag ] ?? array(), $attrs );
		}
		foreach ( array( 'nav', 'aside', 'header', 'footer', 'section', 'details', 'summary', 'ol', 'ul', 'li', 'span', 'div', 'p', 'strong', 'em', 'code', 'small', 'h1', 'h2', 'h3', 'h4', 'hr', 'table', 'thead', 'tbody', 'tr', 'th', 'td' ) as $tag ) {
			$tags[ $tag ] = $tags[ $tag ] ?? array();
		}
		foreach ( $tags as $tag => $attrs ) {
			$tags[ $tag ] = array_merge( is_array( $attrs ) ? $attrs : array(), self::global_attributes() );
		}
		$tags['details']['open'] = true;
		$tags['th']['scope']     = true;
		$tags['td']['colspan']   = true;

		/**
		 * Filters the HTML the plugin allows in widget and settings markup.
		 *
		 * @param array $tags Allowed tags and attributes for wp_kses().
		 */
		self::$html = (array) apply_filters( 'aisa_allowed_html', $tags );
		return self::$html;
	}
}
