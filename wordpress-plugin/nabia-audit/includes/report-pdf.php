<?php
/**
 * Branded PDF report built with FPDF (bundled in lib/fpdf).
 *
 * Pages: cover with the overall score, action plan, one page per category, and a
 * closing page with the call to action and all contact links.
 *
 * @package NabiaAudit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'FPDF' ) ) {
	require_once NWA_DIR . 'lib/fpdf/fpdf.php';
}

/**
 * FPDF with transparency, arcs, rounded boxes and brand helpers.
 */
class NWA_PDF extends FPDF {

	/** @var bool Plain page (no header / footer). */
	public $plain = true;

	/** @var array Brand colours (hex). */
	public $c = array();

	/** @var string Domain shown in headers. */
	public $domain = '';

	/** @var string Brand name. */
	public $brand = '';

	/** @var string Website link. */
	public $site = '';

	/** @var array Plain flag per page number. */
	protected $page_plain = array();

	/** @var array Transparency states. */
	protected $extgstates = array();

	/** @var array Alpha value to state index. */
	protected $alpha_cache = array();

	/**
	 * UTF-8 to the Windows-1252 encoding used by the core fonts.
	 *
	 * @param string $s Text.
	 * @return string
	 */
	public function t( $s ) {
		$s = str_replace( array( "\u{2014}", "\u{2013}", "\u{2019}", "\u{2018}", "\u{201C}", "\u{201D}" ), array( ', ', ' to ', "'", "'", '"', '"' ), (string) $s );
		$out = function_exists( 'iconv' ) ? @iconv( 'UTF-8', 'windows-1252//TRANSLIT//IGNORE', $s ) : false; // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return false === $out ? mb_convert_encoding( $s, 'ISO-8859-1', 'UTF-8' ) : $out;
	}

	/**
	 * Hex colour to RGB.
	 *
	 * @param string $hex Hex.
	 * @return int[]
	 */
	public static function rgb( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		return array( hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) );
	}

	/**
	 * Fill colour.
	 *
	 * @param string $hex Hex or palette key.
	 */
	public function fill( $hex ) {
		list( $r, $g, $b ) = self::rgb( isset( $this->c[ $hex ] ) ? $this->c[ $hex ] : $hex );
		$this->SetFillColor( $r, $g, $b );
	}

	/**
	 * Text colour.
	 *
	 * @param string $hex Hex or palette key.
	 */
	public function ink( $hex ) {
		list( $r, $g, $b ) = self::rgb( isset( $this->c[ $hex ] ) ? $this->c[ $hex ] : $hex );
		$this->SetTextColor( $r, $g, $b );
	}

	/**
	 * Line colour.
	 *
	 * @param string $hex Hex or palette key.
	 */
	public function stroke( $hex ) {
		list( $r, $g, $b ) = self::rgb( isset( $this->c[ $hex ] ) ? $this->c[ $hex ] : $hex );
		$this->SetDrawColor( $r, $g, $b );
	}

	/**
	 * Font shortcut.
	 *
	 * @param float  $size  Size.
	 * @param string $style '' or B.
	 */
	public function font( $size, $style = '' ) {
		$this->SetFont( 'Helvetica', $style, $size );
	}

	/**
	 * Opacity for everything drawn next (1 = solid).
	 *
	 * @param float $alpha 0 to 1.
	 */
	public function alpha( $alpha ) {
		$key = sprintf( '%.2F', $alpha );
		if ( ! isset( $this->alpha_cache[ $key ] ) ) {
			$n                         = count( $this->extgstates ) + 1;
			$this->extgstates[ $n ]    = array( 'ca' => $alpha );
			$this->alpha_cache[ $key ] = $n;
		}
		$this->_out( sprintf( '/GS%d gs', $this->alpha_cache[ $key ] ) );
	}

	/**
	 * Write the transparency states.
	 */
	protected function _putextgstates() {
		foreach ( $this->extgstates as $i => $state ) {
			$this->_newobj();
			$this->extgstates[ $i ]['n'] = $this->n;
			$this->_put( sprintf( '<</Type /ExtGState /ca %.3F /CA %.3F /BM /Normal>>', $state['ca'], $state['ca'] ) );
			$this->_put( 'endobj' );
		}
	}

	/**
	 * Resources with transparency states.
	 */
	protected function _putresources() {
		$this->_putextgstates();
		parent::_putresources();
	}

	/**
	 * Resource dictionary with transparency states.
	 */
	protected function _putresourcedict() {
		parent::_putresourcedict();
		$this->_put( '/ExtGState <<' );
		foreach ( $this->extgstates as $i => $state ) {
			$this->_put( '/GS' . $i . ' ' . $state['n'] . ' 0 R' );
		}
		$this->_put( '>>' );
	}

	/**
	 * PDF 1.4 for transparency.
	 */
	protected function _enddoc() {
		if ( $this->extgstates && $this->PDFVersion < '1.4' ) {
			$this->PDFVersion = '1.4';
		}
		parent::_enddoc();
	}

	/**
	 * Point on a circle, angle in degrees clockwise from 12 o'clock.
	 *
	 * @param float $cx X.
	 * @param float $cy Y.
	 * @param float $r  Radius.
	 * @param float $a  Angle.
	 * @return float[]
	 */
	protected function pt( $cx, $cy, $r, $a ) {
		$rad = deg2rad( $a );
		return array( $cx + $r * sin( $rad ), $cy - $r * cos( $rad ) );
	}

	/**
	 * Bezier path of an arc (no paint operator).
	 *
	 * @param float $cx   X.
	 * @param float $cy   Y.
	 * @param float $r    Radius.
	 * @param float $from Start angle.
	 * @param float $to   End angle.
	 * @param bool  $move Start a new path.
	 * @return string
	 */
	protected function arc_path( $cx, $cy, $r, $from, $to, $move = true ) {
		$k    = $this->k;
		$h    = $this->h;
		$out  = '';
		$segs = max( 1, (int) ceil( ( $to - $from ) / 90 ) );
		$step = ( $to - $from ) / $segs;
		list( $x0, $y0 ) = $this->pt( $cx, $cy, $r, $from );
		if ( $move ) {
			$out .= sprintf( '%.2F %.2F m ', $x0 * $k, ( $h - $y0 ) * $k );
		}
		for ( $i = 0; $i < $segs; $i++ ) {
			$a1    = deg2rad( $from + $i * $step );
			$a2    = deg2rad( $from + ( $i + 1 ) * $step );
			$alpha = 4 / 3 * tan( ( $a2 - $a1 ) / 4 );
			$p0    = array( $cx + $r * sin( $a1 ), $cy - $r * cos( $a1 ) );
			$p3    = array( $cx + $r * sin( $a2 ), $cy - $r * cos( $a2 ) );
			$p1    = array( $p0[0] + $alpha * $r * cos( $a1 ), $p0[1] + $alpha * $r * sin( $a1 ) );
			$p2    = array( $p3[0] - $alpha * $r * cos( $a2 ), $p3[1] - $alpha * $r * sin( $a2 ) );
			$out  .= sprintf( '%.2F %.2F %.2F %.2F %.2F %.2F c ', $p1[0] * $k, ( $h - $p1[1] ) * $k, $p2[0] * $k, ( $h - $p2[1] ) * $k, $p3[0] * $k, ( $h - $p3[1] ) * $k );
		}
		return $out;
	}

	/**
	 * Filled circle.
	 *
	 * @param float  $cx  X.
	 * @param float  $cy  Y.
	 * @param float  $r   Radius.
	 * @param string $hex Colour.
	 */
	public function dot( $cx, $cy, $r, $hex ) {
		$this->fill( $hex );
		$this->_out( $this->arc_path( $cx, $cy, $r, 0, 360 ) . 'f' );
	}

	/**
	 * Progress ring.
	 *
	 * @param float  $cx    X.
	 * @param float  $cy    Y.
	 * @param float  $r     Radius.
	 * @param float  $width Line width.
	 * @param int    $pct   0 to 100.
	 * @param string $color Arc colour.
	 * @param string $track Track colour.
	 * @param float  $track_alpha Track opacity.
	 */
	public function ring( $cx, $cy, $r, $width, $pct, $color, $track, $track_alpha = 1 ) {
		$this->SetLineWidth( $width );
		$this->stroke( $track );
		$this->alpha( $track_alpha );
		$this->_out( $this->arc_path( $cx, $cy, $r, 0, 360 ) . 'S' );
		$this->alpha( 1 );
		if ( $pct > 0 ) {
			$this->stroke( $color );
			$this->_out( '1 J ' . $this->arc_path( $cx, $cy, $r, 0, 360 * min( 100, $pct ) / 100 ) . 'S 0 J' );
		}
		$this->SetLineWidth( 0.2 );
	}

	/**
	 * Rounded rectangle.
	 *
	 * @param float  $x     X.
	 * @param float  $y     Y.
	 * @param float  $w     Width.
	 * @param float  $h     Height.
	 * @param float  $r     Radius.
	 * @param string $style F, D or DF.
	 */
	public function box( $x, $y, $w, $h, $r, $style = 'F' ) {
		$k   = $this->k;
		$hp  = $this->h;
		$r   = min( $r, $w / 2, $h / 2 );
		$op  = 'F' === $style ? 'f' : ( 'DF' === $style || 'FD' === $style ? 'B' : 'S' );
		$c   = 0.5523 * $r;
		$p   = sprintf( '%.2F %.2F m ', ( $x + $r ) * $k, ( $hp - $y ) * $k );
		$p  .= sprintf( '%.2F %.2F l ', ( $x + $w - $r ) * $k, ( $hp - $y ) * $k );
		$p  .= sprintf( '%.2F %.2F %.2F %.2F %.2F %.2F c ', ( $x + $w - $r + $c ) * $k, ( $hp - $y ) * $k, ( $x + $w ) * $k, ( $hp - ( $y + $r - $c ) ) * $k, ( $x + $w ) * $k, ( $hp - ( $y + $r ) ) * $k );
		$p  .= sprintf( '%.2F %.2F l ', ( $x + $w ) * $k, ( $hp - ( $y + $h - $r ) ) * $k );
		$p  .= sprintf( '%.2F %.2F %.2F %.2F %.2F %.2F c ', ( $x + $w ) * $k, ( $hp - ( $y + $h - $r + $c ) ) * $k, ( $x + $w - $r + $c ) * $k, ( $hp - ( $y + $h ) ) * $k, ( $x + $w - $r ) * $k, ( $hp - ( $y + $h ) ) * $k );
		$p  .= sprintf( '%.2F %.2F l ', ( $x + $r ) * $k, ( $hp - ( $y + $h ) ) * $k );
		$p  .= sprintf( '%.2F %.2F %.2F %.2F %.2F %.2F c ', ( $x + $r - $c ) * $k, ( $hp - ( $y + $h ) ) * $k, $x * $k, ( $hp - ( $y + $h - $r + $c ) ) * $k, $x * $k, ( $hp - ( $y + $h - $r ) ) * $k );
		$p  .= sprintf( '%.2F %.2F l ', $x * $k, ( $hp - ( $y + $r ) ) * $k );
		$p  .= sprintf( '%.2F %.2F %.2F %.2F %.2F %.2F c ', $x * $k, ( $hp - ( $y + $r - $c ) ) * $k, ( $x + $r - $c ) * $k, ( $hp - $y ) * $k, ( $x + $r ) * $k, ( $hp - $y ) * $k );
		$this->_out( $p . $op );
	}

	/**
	 * Number of lines a MultiCell will use.
	 *
	 * @param float  $w   Width.
	 * @param string $txt Text (already encoded).
	 * @return int
	 */
	public function lines( $w, $txt ) {
		$cw   = $this->CurrentFont['cw'];
		$wmax = ( $w - 2 * $this->cMargin ) * 1000 / $this->FontSize;
		$s    = str_replace( "\r", '', (string) $txt );
		$nb   = strlen( $s );
		if ( $nb > 0 && "\n" === $s[ $nb - 1 ] ) {
			--$nb;
		}
		$sep = -1;
		$i   = 0;
		$j   = 0;
		$l   = 0;
		$nl  = 1;
		while ( $i < $nb ) {
			$c = $s[ $i ];
			if ( "\n" === $c ) {
				++$i;
				$sep = -1;
				$j   = $i;
				$l   = 0;
				++$nl;
				continue;
			}
			if ( ' ' === $c ) {
				$sep = $i;
			}
			$l += isset( $cw[ $c ] ) ? $cw[ $c ] : 500;
			if ( $l > $wmax ) {
				if ( -1 === $sep ) {
					if ( $i === $j ) {
						++$i;
					}
				} else {
					$i = $sep + 1;
				}
				$sep = -1;
				$j   = $i;
				$l   = 0;
				++$nl;
			} else {
				++$i;
			}
		}
		return $nl;
	}

	/**
	 * Start a new content page when less than $h mm is left.
	 *
	 * @param float $h Needed height.
	 */
	public function need( $h ) {
		if ( $this->GetY() + $h > $this->h - 20 ) {
			$this->AddPage();
			$this->SetY( 30 );
		}
	}

	/**
	 * Light background and slim header on content pages.
	 */
	public function Header() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
		$this->page_plain[ $this->page ] = $this->plain;
		if ( $this->plain ) {
			return;
		}
		$this->fill( 'bg' );
		$this->Rect( 0, 0, $this->w, $this->h, 'F' );
		$this->fill( 'primary' );
		$this->Rect( 0, 0, $this->w, 2.2, 'F' );
		$this->SetXY( 18, 9 );
		$this->font( 9.5, 'B' );
		$this->ink( 'dark' );
		$this->Cell( 80, 6, $this->t( $this->brand ), 0, 0, 'L', false, $this->site );
		$this->font( 8.5 );
		$this->ink( 'muted' );
		$this->SetXY( 100, 9 );
		$this->Cell( 92, 6, $this->t( 'Website audit  ·  ' . $this->domain ), 0, 0, 'R' );
		$this->stroke( 'line' );
		$this->SetLineWidth( 0.2 );
		$this->Line( 18, 18, 192, 18 );
		$this->SetY( 26 );
	}

	/**
	 * Page number and link on content pages.
	 */
	public function Footer() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
		if ( ! empty( $this->page_plain[ $this->page ] ) ) {
			return;
		}
		$this->SetY( -14 );
		$this->font( 8 );
		$this->ink( 'muted' );
		$this->Cell( 87, 6, $this->t( 'Prepared by ' . $this->brand . '  ·  ' . wp_parse_url( $this->site, PHP_URL_HOST ) ), 0, 0, 'L', false, $this->site );
		$this->Cell( 87, 6, $this->t( 'Page ' . $this->PageNo() ), 0, 0, 'R' );
	}
}

/**
 * Build the PDF and return it as a string.
 *
 * @param array $result Audit result.
 * @param array $lead   { name, email, date }.
 * @return string
 */
function nwa_build_pdf( $result, $lead = array() ) {
	$s    = nwa_settings();
	$cats = nwa_categories();
	$pdf  = new NWA_PDF( 'P', 'mm', 'A4' );

	$pdf->c      = array(
		'primary' => $s['color_primary'],
		'dark'    => $s['color_dark'],
		'accent'  => $s['color_accent'],
		'gold'    => '#fbbf24',
		'good'    => '#16a34a',
		'ok'      => '#f59e0b',
		'bad'     => '#ef4444',
		'bg'      => '#fbfaff',
		'soft'    => '#f1edfd',
		'line'    => '#e7e2f5',
		'muted'   => '#6b6780',
		'body'    => '#3b3552',
		'white'   => '#ffffff',
		'lilac'   => '#c9b8ff',
	);
	$pdf->domain = preg_replace( '/^www\./', '', (string) wp_parse_url( $result['final_url'], PHP_URL_HOST ) );
	$pdf->brand  = $s['brand_name'];
	$pdf->site   = $s['website'];
	$pdf->SetTitle( $pdf->t( 'Website audit report for ' . $pdf->domain ) );
	$pdf->SetAuthor( $pdf->t( $s['brand_name'] ) );
	$pdf->SetCreator( 'Nabia Website Audit' );
	$pdf->SetMargins( 18, 26, 18 );
	$pdf->SetAutoPageBreak( true, 20 );

	$mode   = isset( $result['mode'] ) ? $result['mode'] : 'full';
	$date   = ! empty( $lead['date'] ) ? $lead['date'] : wp_date( get_option( 'date_format' ) );
	$state  = array(
		'good' => 'good',
		'ok'   => 'ok',
		'bad'  => 'bad',
	);
	$wa     = nwa_whatsapp_url();
	$help   = $wa ? $wa : $s['contact_url'];
	$status = array(
		'pass' => array( 'PASS', 'good', '#dcfce7' ),
		'warn' => array( 'IMPROVE', 'ok', '#fef3c7' ),
		'fail' => array( 'FIX', 'bad', '#fee2e2' ),
	);

	/* ------------------------------------------------------------ Cover */
	$pdf->plain = true;
	$pdf->AddPage();
	$pdf->SetAutoPageBreak( false );
	$pdf->fill( 'dark' );
	$pdf->Rect( 0, 0, 210, 297, 'F' );
	$pdf->alpha( 0.55 );
	$pdf->dot( 196, 22, 62, 'primary' );
	$pdf->alpha( 0.35 );
	$pdf->dot( 8, 292, 46, 'accent' );
	$pdf->alpha( 1 );

	$pdf->SetXY( 18, 18 );
	$pdf->font( 16, 'B' );
	$pdf->ink( 'white' );
	$pdf->Cell( 100, 8, $pdf->t( $s['brand_name'] ), 0, 2, 'L', false, $s['website'] );
	$pdf->font( 9 );
	$pdf->ink( 'lilac' );
	$pdf->Cell( 100, 5, $pdf->t( $s['brand_role'] ), 0, 2 );

	$pdf->alpha( 0.14 );
	$pdf->fill( 'white' );
	$pdf->box( 140, 18, 52, 10, 5 );
	$pdf->alpha( 1 );
	$pdf->SetXY( 140, 18 );
	$pdf->font( 7.5, 'B' );
	$pdf->ink( 'white' );
	$pdf->Cell( 52, 10, $pdf->t( 'FREE WEBSITE AUDIT' ), 0, 0, 'C' );

	$pdf->SetXY( 18, 50 );
	$pdf->font( 36, 'B' );
	$pdf->ink( 'white' );
	$pdf->Cell( 170, 14, $pdf->t( 'Website Audit' ), 0, 2 );
	$pdf->Cell( 170, 14, $pdf->t( 'Report' ), 0, 2 );
	$pdf->SetXY( 18, 82 );
	$pdf->font( 14, 'B' );
	$pdf->ink( 'lilac' );
	$pdf->Cell( 170, 8, $pdf->t( $pdf->domain ), 0, 2, 'L', false, $result['final_url'] );
	$pdf->font( 9.5 );
	$pdf->ink( 'white' );
	$pdf->alpha( 0.75 );
	$prepared = ! empty( $lead['name'] ) ? 'Prepared for ' . $lead['name'] . ' on ' . $date : 'Generated on ' . $date;
	$pdf->Cell( 170, 6, $pdf->t( $prepared ), 0, 2 );
	if ( ! empty( $result['tech'] ) ) {
		$pdf->Cell( 170, 6, $pdf->t( 'Built with ' . implode( ', ', array_slice( $result['tech'], 0, 5 ) ) ), 0, 2 );
	}
	$pdf->alpha( 1 );

	$score = (int) $result['overall'];
	$color = $state[ nwa_state( $score ) ];
	$pdf->ring( 105, 138, 30, 7, $score, $color, 'white', 0.13 );
	$pdf->SetXY( 75, 126 );
	$pdf->font( 40, 'B' );
	$pdf->ink( 'white' );
	$pdf->Cell( 60, 16, (string) $score, 0, 2, 'C' );
	$pdf->font( 9 );
	$pdf->ink( 'lilac' );
	$pdf->Cell( 60, 5, $pdf->t( 'OVERALL SCORE' ), 0, 0, 'C' );
	$pdf->dot( 132, 113, 8.5, 'accent' );
	$pdf->SetXY( 123.5, 108 );
	$pdf->font( 16, 'B' );
	$pdf->ink( 'white' );
	$pdf->Cell( 17, 10, $result['grade'], 0, 0, 'C' );

	$pdf->SetXY( 28, 176 );
	$pdf->font( 12 );
	$pdf->ink( 'white' );
	$pdf->MultiCell( 154, 6.5, $pdf->t( nwa_verdict( $score ) ), 0, 'C' );

	$i = 0;
	foreach ( $cats as $key => $cat ) {
		$x  = 0 === $i % 2 ? 18 : 108;
		$y  = $i < 2 ? 200 : 234;
		$sc = (int) $result['categories'][ $key ]['score'];
		$pdf->alpha( 0.08 );
		$pdf->fill( 'white' );
		$pdf->box( $x, $y, 84, 28, 4 );
		$pdf->alpha( 1 );
		$pdf->SetXY( $x + 6, $y + 5 );
		$pdf->font( 10, 'B' );
		$pdf->ink( 'white' );
		$pdf->Cell( 50, 6, $pdf->t( $cat['title'] ), 0, 0 );
		$pdf->SetXY( $x + 54, $y + 3.5 );
		$pdf->font( 20, 'B' );
		$pdf->ink( $state[ nwa_state( $sc ) ] );
		$pdf->Cell( 24, 9, (string) $sc, 0, 0, 'R' );
		$pdf->alpha( 0.15 );
		$pdf->fill( 'white' );
		$pdf->box( $x + 6, $y + 18.5, 72, 2.6, 1.3 );
		$pdf->alpha( 1 );
		$pdf->fill( $state[ nwa_state( $sc ) ] );
		$pdf->box( $x + 6, $y + 18.5, max( 2.6, 72 * $sc / 100 ), 2.6, 1.3 );
		++$i;
	}

	$pdf->SetXY( 18, 276 );
	$pdf->font( 8.5 );
	$pdf->ink( 'white' );
	$pdf->alpha( 0.7 );
	$pdf->Cell( 110, 6, $pdf->t( 'Scores out of 100  ·  75+ good  ·  50 to 74 needs work  ·  under 50 urgent' ), 0, 0 );
	$pdf->alpha( 1 );
	$pdf->ink( 'lilac' );
	$pdf->font( 8.5, 'B' );
	$pdf->Cell( 64, 6, $pdf->t( wp_parse_url( $s['website'], PHP_URL_HOST ) ), 0, 0, 'R', false, $s['website'] );

	/* ------------------------------------------------------- Action plan */
	$pdf->plain = false;
	$pdf->SetAutoPageBreak( true, 20 );
	$pdf->AddPage();
	$pdf->SetXY( 18, 28 );
	$pdf->font( 22, 'B' );
	$pdf->ink( 'dark' );
	$pdf->Cell( 174, 10, $pdf->t( 'Your action plan' ), 0, 2 );
	$pdf->font( 10.5 );
	$pdf->ink( 'muted' );
	$pdf->Cell( 174, 6, $pdf->t( 'Start at the top: these fixes make the biggest difference first.' ), 0, 2 );
	if ( 'basic' === $mode ) {
		$pdf->SetY( $pdf->GetY() + 2 );
		$by = $pdf->GetY();
		$pdf->fill( '#fef3c7' );
		$pdf->box( 18, $by, 174, 16, 3 );
		$pdf->SetXY( 24, $by + 3 );
		$pdf->font( 9.5, 'B' );
		$pdf->ink( '#92400e' );
		$pdf->MultiCell( 162, 5, $pdf->t( 'Quick report: your website did not let our scanner in, so this is a first look. I will review it by hand and email you the full picture within 24 hours.' ), 0, 'L' );
		$pdf->SetY( $by + 18 );
		$stats_y = $pdf->GetY();
	}

	$st    = $result['stats'];
	$na    = function ( $value, $format ) {
		return null === $value || '' === $value ? 'n/a' : $format( $value );
	};
	$stats = array(
		array( $na( isset( $st['load_time'] ) ? $st['load_time'] : null, function ( $v ) { return sprintf( '%.2fs', $v ); } ), 'Server response' ),
		array( $na( isset( $st['size_kb'] ) ? $st['size_kb'] : null, function ( $v ) { return ( $v < 10 ? number_format( (float) $v, 1 ) : number_format( (float) $v ) ) . ' KB'; } ), 'pagespeed' === $mode ? 'Page weight' : 'HTML size' ),
		'pagespeed' === $mode
			? array( $na( isset( $st['lcp'] ) ? $st['lcp'] : null, function ( $v ) { return $v . 's'; } ), 'Main content loads' )
			: array( $na( isset( $st['words'] ) ? $st['words'] : null, 'number_format' ), 'Words' ),
		array( $na( isset( $st['images'] ) ? $st['images'] : null, 'strval' ), 'Images' ),
		array( $na( isset( $st['files'] ) ? $st['files'] : null, 'strval' ), 'pagespeed' === $mode ? 'Requests' : 'Scripts & styles' ),
	);
	$stats_y = isset( $stats_y ) ? $stats_y : 48;
	if ( 'basic' !== $mode ) :
	$tw      = ( 174 - 4 * 3.5 ) / 5;
	foreach ( $stats as $n => $stat ) {
		$x = 18 + $n * ( $tw + 3.5 );
		$pdf->fill( 'white' );
		$pdf->stroke( 'line' );
		$pdf->box( $x, $stats_y, $tw, 21, 3, 'DF' );
		$pdf->SetXY( $x, $stats_y + 3.5 );
		$pdf->font( 14, 'B' );
		$pdf->ink( 'primary' );
		$pdf->Cell( $tw, 7, $pdf->t( $stat[0] ), 0, 2, 'C' );
		$pdf->font( 7.5 );
		$pdf->ink( 'muted' );
		$pdf->Cell( $tw, 5, $pdf->t( $stat[1] ), 0, 0, 'C' );
	}

	endif;
	$list_y = 'basic' === $mode ? $stats_y + 2 : $stats_y + 30;
	$pdf->SetXY( 18, $list_y );
	$pdf->font( 13, 'B' );
	$pdf->ink( 'dark' );
	$pdf->Cell( 174, 8, $pdf->t( 'Top priorities' ), 0, 2 );
	$pdf->SetY( $pdf->GetY() + 2 );
	$priorities = nwa_priorities( $result, 6 );
	if ( ! $priorities ) {
		$pdf->font( 10 );
		$pdf->ink( 'body' );
		$pdf->MultiCell( 174, 5.5, $pdf->t( 'Nothing urgent. Your website passes every check we run. Great work!' ), 0, 'L' );
	}
	foreach ( $priorities as $n => $item ) {
		$pdf->font( 9.5 );
		$fix_lines = $pdf->lines( 150, $pdf->t( $item['fix'] ) );
		$h         = 14 + $fix_lines * 4.8;
		$pdf->need( $h + 3 );
		$y = $pdf->GetY();
		$pdf->fill( 'white' );
		$pdf->stroke( 'line' );
		$pdf->box( 18, $y, 174, $h, 3.5, 'DF' );
		$pdf->dot( 27, $y + 7.5, 4.2, 'fail' === $item['status'] ? 'bad' : 'ok' );
		$pdf->SetXY( 22.8, $y + 5 );
		$pdf->font( 9, 'B' );
		$pdf->ink( 'white' );
		$pdf->Cell( 8.4, 5, (string) ( $n + 1 ), 0, 0, 'C' );
		$pdf->SetXY( 35, $y + 4.5 );
		$pdf->font( 11, 'B' );
		$pdf->ink( 'dark' );
		$pdf->Cell( 100, 6, $pdf->t( $item['label'] ), 0, 0 );
		$tag  = strtoupper( $cats[ $item['cat'] ]['short'] );
		$impact = 'fail' === $item['status'] ? 'HIGH IMPACT' : 'QUICK WIN';
		$pdf->font( 7, 'B' );
		$w1 = $pdf->GetStringWidth( $tag ) + 6;
		$w2 = $pdf->GetStringWidth( $impact ) + 6;
		$pdf->fill( 'soft' );
		$pdf->box( 188 - $w1 - $w2 - 2, $y + 4.5, $w1, 5.5, 2.75 );
		$pdf->SetXY( 188 - $w1 - $w2 - 2, $y + 4.5 );
		$pdf->ink( 'primary' );
		$pdf->Cell( $w1, 5.5, $pdf->t( $tag ), 0, 0, 'C' );
		$pdf->fill( 'fail' === $item['status'] ? '#fee2e2' : '#fef3c7' );
		$pdf->box( 188 - $w2, $y + 4.5, $w2, 5.5, 2.75 );
		$pdf->SetXY( 188 - $w2, $y + 4.5 );
		$pdf->ink( 'fail' === $item['status'] ? '#b91c1c' : '#b45309' );
		$pdf->Cell( $w2, 5.5, $pdf->t( $impact ), 0, 0, 'C' );
		$pdf->SetXY( 35, $y + 11 );
		$pdf->font( 9.5 );
		$pdf->ink( 'body' );
		$pdf->MultiCell( 150, 4.8, $pdf->t( $item['fix'] ), 0, 'L' );
		$pdf->SetY( $y + $h + 3 );
	}

	// Show as many strengths as fit on this page (at least 3, or move them all to a new page).
	$room      = (int) floor( ( 297 - 20 - $pdf->GetY() - 14 ) / 8 );
	$strengths = nwa_strengths( $result, $room >= 3 ? min( 6, $room ) : 6 );
	if ( $strengths ) {
		$pdf->need( 14 + 8 * count( $strengths ) );
		$pdf->SetY( $pdf->GetY() + 3 );
		$pdf->font( 13, 'B' );
		$pdf->ink( 'dark' );
		$pdf->Cell( 174, 8, $pdf->t( 'What is already working' ), 0, 2 );
		$pdf->SetY( $pdf->GetY() + 1 );
		foreach ( $strengths as $item ) {
			$pdf->need( 9 );
			$y = $pdf->GetY();
			$pdf->dot( 21.5, $y + 3.5, 3, 'good' );
			$pdf->SetXY( 18.5, $y + 1 );
			$pdf->SetFont( 'ZapfDingbats', '', 7 );
			$pdf->ink( 'white' );
			$pdf->Cell( 6, 5, '4', 0, 0, 'C' );
			$pdf->SetXY( 27, $y + 0.8 );
			$pdf->font( 10, 'B' );
			$pdf->ink( 'dark' );
			$pdf->Cell( 50, 5.5, $pdf->t( $item['label'] ), 0, 0 );
			$pdf->font( 9.5 );
			$pdf->ink( 'muted' );
			$pdf->Cell( 115, 5.5, $pdf->t( nwa_cut( $item['found'], 78 ) ), 0, 2 );
			$pdf->SetY( $y + 8 );
		}
	}

	// Help strip, only when it fits (the last page has the full call to action).
	$y = $pdf->GetY() + 4;
	if ( $y + 24 > 297 - 20 ) {
		$y = -1;
	}
	if ( $y > 0 ) :
	$pdf->fill( 'dark' );
	$pdf->box( 18, $y, 174, 24, 5 );
	$pdf->SetXY( 26, $y + 5 );
	$pdf->font( 12, 'B' );
	$pdf->ink( 'white' );
	$pdf->Cell( 110, 7, $pdf->t( 'Short on time? I can fix all of this for you.' ), 0, 2 );
	$pdf->font( 9.5 );
	$pdf->ink( 'lilac' );
	$pdf->Cell( 110, 6, $pdf->t( 'Fixed price, no surprises. Most fixes are done in a few days.' ), 0, 0 );
	$pdf->fill( 'primary' );
	$pdf->box( 142, $y + 6.5, 44, 11, 5.5 );
	$pdf->SetXY( 142, $y + 6.5 );
	$pdf->font( 10, 'B' );
	$pdf->ink( 'white' );
	$pdf->Cell( 44, 11, $pdf->t( $wa ? 'WhatsApp me' : 'Contact me' ), 0, 0, 'C', false, $help );
	endif;

	/* -------------------------------------------------------- Categories */
	$num = 0;
	foreach ( $cats as $key => $cat ) {
		++$num;
		$data   = $result['categories'][ $key ];
		$sc     = (int) $data['score'];
		$counts = array(
			'pass' => 0,
			'warn' => 0,
			'fail' => 0,
		);
		foreach ( $data['checks'] as $check ) {
			++$counts[ $check['status'] ];
		}
		$pdf->AddPage();
		$pdf->SetXY( 18, 28 );
		$pdf->font( 8.5, 'B' );
		$pdf->ink( 'primary' );
		$pdf->Cell( 140, 5, $pdf->t( sprintf( '0%d  ·  %s', $num, strtoupper( $cat['short'] ) ) ), 0, 2 );
		$pdf->font( 22, 'B' );
		$pdf->ink( 'dark' );
		$pdf->Cell( 140, 11, $pdf->t( $cat['title'] ), 0, 2 );
		$pdf->font( 10 );
		$pdf->ink( 'muted' );
		$pdf->MultiCell( 138, 5.2, $pdf->t( $cat['why'] ), 0, 'L' );
		$pdf->ring( 176, 44, 13, 3.6, $sc, $state[ nwa_state( $sc ) ], 'line' );
		$pdf->SetXY( 163, 39 );
		$pdf->font( 16, 'B' );
		$pdf->ink( 'dark' );
		$pdf->Cell( 26, 7, (string) $sc, 0, 2, 'C' );
		$pdf->font( 6.5, 'B' );
		$pdf->ink( 'muted' );
		$pdf->Cell( 26, 3, '/100', 0, 0, 'C' );

		$pdf->SetXY( 18, max( $pdf->GetY(), 62 ) + 2 );
		$pdf->SetY( max( $pdf->GetY(), 64 ) );
		$chips = array(
			array( $counts['pass'] . ' passed', 'good', '#dcfce7' ),
			array( $counts['warn'] . ' to improve', 'ok', '#fef3c7' ),
			array( $counts['fail'] . ' to fix', 'bad', '#fee2e2' ),
		);
		$x = 18;
		$y = $pdf->GetY();
		$pdf->font( 8.5, 'B' );
		foreach ( $chips as $chip ) {
			$w = $pdf->GetStringWidth( $chip[0] ) + 10;
			$pdf->fill( $chip[2] );
			$pdf->box( $x, $y, $w, 7, 3.5 );
			$pdf->dot( $x + 4, $y + 3.5, 1.3, $chip[1] );
			$pdf->SetXY( $x + 6, $y );
			$pdf->ink( 'dark' );
			$pdf->Cell( $w - 7, 7, $pdf->t( $chip[0] ), 0, 0 );
			$x += $w + 3;
		}
		$pdf->SetY( $y + 12 );

		// Problems first, then passes.
		$checks = $data['checks'];
		usort(
			$checks,
			function ( $a, $b ) {
				$order = array(
					'fail' => 0,
					'warn' => 1,
					'pass' => 2,
				);
				return $order[ $a['status'] ] - $order[ $b['status'] ] ?: $b['weight'] - $a['weight'];
			}
		);
		foreach ( $checks as $check ) {
			$st = $status[ $check['status'] ];
			$pdf->font( 9.5 );
			$why         = ! empty( $check['why'] ) ? $check['why'] : '';
			$found_lines = $pdf->lines( 142, $pdf->t( $check['found'] ) );
			$why_lines   = $why ? $pdf->lines( 121, $pdf->t( $why ) ) : 0;
			$fix_lines   = $check['fix'] ? $pdf->lines( 121, $pdf->t( $check['fix'] ) ) : 0;
			$h           = 13 + $found_lines * 4.6 + ( $why_lines ? 1.5 + $why_lines * 4.6 : 0 ) + ( $fix_lines ? 1.5 + $fix_lines * 4.6 : 0 );
			$pdf->need( $h + 3 );
			$y = $pdf->GetY();
			$pdf->fill( 'white' );
			$pdf->stroke( 'line' );
			$pdf->box( 18, $y, 174, $h, 3, 'DF' );
			$pdf->fill( $st[1] );
			$pdf->box( 18, $y, 1.8, $h, 0.9 );
			$pdf->dot( 27, $y + 7, 3.4, $st[1] );
			if ( 'warn' === $check['status'] ) {
				$pdf->SetXY( 23.6, $y + 4.3 );
				$pdf->font( 9, 'B' );
				$pdf->ink( 'white' );
				$pdf->Cell( 6.8, 5.4, '!', 0, 0, 'C' );
			} else {
				$pdf->SetXY( 23.6, $y + 4.3 );
				$pdf->SetFont( 'ZapfDingbats', '', 7.5 );
				$pdf->ink( 'white' );
				$pdf->Cell( 6.8, 5.4, 'pass' === $check['status'] ? '4' : '8', 0, 0, 'C' );
			}
			$pdf->SetXY( 34, $y + 4 );
			$pdf->font( 10.5, 'B' );
			$pdf->ink( 'dark' );
			$pdf->Cell( 120, 6, $pdf->t( $check['label'] ), 0, 0 );
			$pdf->font( 7, 'B' );
			$pw = $pdf->GetStringWidth( $st[0] ) + 7;
			$pdf->fill( $st[2] );
			$pdf->box( 186 - $pw, $y + 4.3, $pw, 5.5, 2.75 );
			$pdf->SetXY( 186 - $pw, $y + 4.3 );
			$pdf->ink( 'pass' === $check['status'] ? '#15803d' : ( 'warn' === $check['status'] ? '#b45309' : '#b91c1c' ) );
			$pdf->Cell( $pw, 5.5, $st[0], 0, 0, 'C' );
			$pdf->SetXY( 34, $y + 10.5 );
			$pdf->font( 9.5 );
			$pdf->ink( 'body' );
			$pdf->MultiCell( 142, 4.6, $pdf->t( $check['found'] ), 0, 'L' );
			$rows = array();
			if ( $why ) {
				$rows[] = array( 'WHY IT MATTERS', 'accent', $why );
			}
			if ( $check['fix'] ) {
				$rows[] = array( 'HOW TO FIX', 'primary', $check['fix'] );
			}
			foreach ( $rows as $row ) {
				$fy = $pdf->GetY() + 1.5;
				$pdf->SetXY( 34, $fy );
				$pdf->font( 7.5, 'B' );
				$pdf->ink( $row[1] );
				$pdf->Cell( 27, 4.6, $pdf->t( $row[0] ), 0, 0 );
				$pdf->SetXY( 61, $fy );
				$pdf->font( 9.5 );
				$pdf->ink( 'WHY IT MATTERS' === $row[0] ? 'body' : 'muted' );
				$pdf->MultiCell( 121, 4.6, $pdf->t( $row[2] ), 0, 'L' );
			}
			$pdf->SetY( $y + $h + 3 );
		}
	}

	/* ------------------------------------------------------------ Closing */
	$pdf->plain = true;
	$pdf->AddPage();
	$pdf->SetAutoPageBreak( false );
	$pdf->fill( 'primary' );
	$pdf->Rect( 0, 0, 210, 297, 'F' );
	$pdf->alpha( 0.35 );
	$pdf->dot( 205, 250, 90, 'dark' );
	$pdf->alpha( 0.45 );
	$pdf->dot( 10, 12, 30, 'accent' );
	$pdf->alpha( 1 );

	$pdf->SetXY( 18, 30 );
	$pdf->font( 9, 'B' );
	$pdf->ink( 'white' );
	$pdf->alpha( 0.8 );
	$pdf->Cell( 170, 6, $pdf->t( 'YOUR NEXT STEP' ), 0, 2 );
	$pdf->alpha( 1 );
	$pdf->SetY( 40 );
	$pdf->font( 28, 'B' );
	$pdf->MultiCell( 170, 12, $pdf->t( $s['cta_title'] ), 0, 'L' );
	$pdf->SetY( $pdf->GetY() + 3 );
	$pdf->font( 12 );
	$pdf->MultiCell( 165, 6.5, $pdf->t( $s['cta_text'] ), 0, 'L' );
	$pdf->SetY( $pdf->GetY() + 6 );

	$perks = array(
		'A fixed price quote for every fix in this report',
		'Most fixes done within 3 to 5 working days',
		'A free follow up audit to show the improvement',
		'WordPress, Shopify, Wix, Webflow, Squarespace and custom sites',
	);
	foreach ( $perks as $perk ) {
		$y = $pdf->GetY();
		$pdf->dot( 21.5, $y + 3.4, 3.2, 'white' );
		$pdf->SetXY( 18.3, $y + 0.8 );
		$pdf->SetFont( 'ZapfDingbats', '', 7.5 );
		$pdf->ink( 'primary' );
		$pdf->Cell( 6.4, 5.2, '4', 0, 0, 'C' );
		$pdf->SetXY( 28, $y );
		$pdf->font( 11 );
		$pdf->ink( 'white' );
		$pdf->Cell( 160, 7, $pdf->t( $perk ), 0, 2 );
		$pdf->SetY( $y + 9 );
	}

	$buttons = array();
	if ( $wa ) {
		$buttons[] = array( 'Chat on WhatsApp', $wa, true );
	}
	if ( $s['booking_url'] ) {
		$buttons[] = array( 'Book a free call', $s['booking_url'], ! $wa );
	}
	if ( $s['email'] ) {
		$buttons[] = array( 'Email me', 'mailto:' . $s['email'] . '?subject=' . rawurlencode( 'My website audit for ' . $pdf->domain ), empty( $buttons ) );
	}
	$buttons[] = array( 'Visit ' . wp_parse_url( $s['website'], PHP_URL_HOST ), $s['website'], false );
	if ( $s['contact_url'] && count( $buttons ) < 4 ) {
		$buttons[] = array( 'Get a quote', $s['contact_url'], false );
	}
	$by = $pdf->GetY() + 6;
	foreach ( array_slice( $buttons, 0, 4 ) as $n => $button ) {
		$x = 0 === $n % 2 ? 18 : 108;
		$y = $by + floor( $n / 2 ) * 17;
		if ( $button[2] ) {
			$pdf->fill( 'dark' );
			$pdf->ink( 'white' );
		} else {
			$pdf->fill( 'white' );
			$pdf->ink( 'dark' );
		}
		$pdf->box( $x, $y, 84, 13, 6.5 );
		$pdf->SetXY( $x, $y );
		$pdf->font( 11, 'B' );
		$pdf->Cell( 84, 13, $pdf->t( $button[0] ), 0, 0, 'C', false, $button[1] );
	}
	$y = $by + ceil( min( 4, count( $buttons ) ) / 2 ) * 17 + 4;

	if ( $s['fiverr'] || $s['upwork'] ) {
		$pdf->SetXY( 18, $y );
		$pdf->font( 10 );
		$pdf->ink( 'white' );
		$lead_in = 'Prefer a platform? Hire me on ';
		$pdf->Cell( $pdf->GetStringWidth( $lead_in ) + 1, 7, $pdf->t( $lead_in ), 0, 0 );
		$pdf->font( 10, 'B' );
		if ( $s['fiverr'] ) {
			$pdf->Cell( $pdf->GetStringWidth( 'Fiverr' ) + 1, 7, 'Fiverr', 0, 0, 'L', false, $s['fiverr'] );
		}
		if ( $s['fiverr'] && $s['upwork'] ) {
			$pdf->font( 10 );
			$pdf->Cell( $pdf->GetStringWidth( ' or ' ) + 1, 7, ' or ', 0, 0, 'L' );
			$pdf->font( 10, 'B' );
		}
		if ( $s['upwork'] ) {
			$pdf->Cell( $pdf->GetStringWidth( 'Upwork' ) + 1, 7, 'Upwork', 0, 0, 'L', false, $s['upwork'] );
		}
	}

	$pdf->fill( 'dark' );
	$pdf->box( 18, 218, 174, 52, 6 );
	$pdf->SetXY( 26, 226 );
	$pdf->font( 15, 'B' );
	$pdf->ink( 'white' );
	$pdf->Cell( 100, 8, $pdf->t( $s['brand_name'] ), 0, 2, 'L', false, $s['website'] );
	$pdf->font( 9.5 );
	$pdf->ink( 'lilac' );
	$pdf->Cell( 100, 5.5, $pdf->t( $s['brand_role'] ), 0, 2 );
	$pdf->SetY( $pdf->GetY() + 3 );
	$pdf->SetX( 26 );
	$pdf->font( 9.5 );
	$pdf->ink( 'white' );
	$lines = array();
	if ( $s['email'] ) {
		$lines[] = array( $s['email'], 'mailto:' . $s['email'] );
	}
	$lines[] = array( wp_parse_url( $s['website'], PHP_URL_HOST ), $s['website'] );
	if ( $s['whatsapp'] ) {
		$lines[] = array( 'WhatsApp ' . $s['whatsapp'], $wa );
	}
	foreach ( $lines as $line ) {
		$pdf->SetX( 26 );
		$pdf->Cell( 110, 6, $pdf->t( $line[0] ), 0, 2, 'L', false, $line[1] );
	}
	$pdf->ring( 172, 244, 12, 3, $score, $state[ nwa_state( $score ) ], 'white', 0.15 );
	$pdf->SetXY( 159, 239 );
	$pdf->font( 14, 'B' );
	$pdf->ink( 'white' );
	$pdf->Cell( 26, 7, (string) $score, 0, 2, 'C' );
	$pdf->font( 6.5, 'B' );
	$pdf->ink( 'lilac' );
	$pdf->Cell( 26, 3, 'YOUR SCORE', 0, 0, 'C' );

	$pdf->SetXY( 18, 278 );
	$pdf->font( 7.5 );
	$pdf->ink( 'white' );
	$pdf->alpha( 0.75 );
	$pdf->MultiCell( 174, 4, $pdf->t( 'This report was generated automatically on ' . $date . ' by scanning the homepage of ' . $pdf->domain . '. Scores are an indication based on common best practices and can change as your website changes.' ), 0, 'C' );
	$pdf->alpha( 1 );

	return $pdf->Output( 'S' );
}
