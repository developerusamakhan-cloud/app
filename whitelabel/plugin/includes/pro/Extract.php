<?php
/**
 * Text extraction from PDF, DOCX, TXT and HTML.
 *
 * The PDF reader handles text-based PDFs (most exports from Word, Google Docs
 * and site builders). Scanned PDFs are images and need OCR first.
 *
 * @package AI_Site_Assistant
 */

namespace AisaPro;

defined( 'ABSPATH' ) || exit;

/**
 * Extractors. Pure PHP, unit tested.
 */
final class Extract {

	/**
	 * Extract text from a file by extension.
	 *
	 * @param string $path File path.
	 * @return string
	 * @throws \Exception When the type is not supported.
	 */
	public static function file( string $path ): string {
		$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
		$raw = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		switch ( $ext ) {
			case 'txt':
			case 'md':
			case 'csv':
				return self::plain( $raw );
			case 'docx':
				return self::docx( $path );
			case 'pdf':
				return self::pdf( $raw );
		}
		throw new \Exception( esc_html( 'Unsupported file type: ' . $ext ) );
	}

	/**
	 * Plain text in UTF-8.
	 *
	 * @param string $raw Raw.
	 */
	public static function plain( string $raw ): string {
		if ( 0 === strpos( $raw, "\xEF\xBB\xBF" ) ) {
			$raw = substr( $raw, 3 );
		}
		if ( function_exists( 'mb_check_encoding' ) && ! mb_check_encoding( $raw, 'UTF-8' ) ) {
			$raw = (string) mb_convert_encoding( $raw, 'UTF-8', 'Windows-1252' );
		}
		return trim( str_replace( array( "\r\n", "\r" ), "\n", $raw ) );
	}

	/**
	 * DOCX: paragraphs from word/document.xml.
	 *
	 * @param string $path Path.
	 * @throws \Exception When unreadable.
	 */
	public static function docx( string $path ): string {
		if ( ! class_exists( '\ZipArchive' ) ) {
			throw new \Exception( 'The PHP zip extension is needed to read DOCX files.' );
		}
		$zip = new \ZipArchive();
		if ( true !== $zip->open( $path ) ) {
			throw new \Exception( 'Could not open the DOCX file.' );
		}
		// A small DOCX can unpack to gigabytes: refuse a document body over 20 MB.
		$stat = $zip->statName( 'word/document.xml' );
		if ( is_array( $stat ) && (int) $stat['size'] > 20 * MB_IN_BYTES ) {
			$zip->close();
			throw new \Exception( 'The DOCX file is too large to read.' );
		}
		$xml = (string) $zip->getFromName( 'word/document.xml' );
		$zip->close();
		if ( '' === $xml ) {
			throw new \Exception( 'The DOCX file has no text.' );
		}
		return self::docx_xml( $xml );
	}

	/**
	 * DOCX XML to text.
	 *
	 * @param string $xml document.xml.
	 */
	public static function docx_xml( string $xml ): string {
		$xml  = preg_replace( '#<w:tab\s*/>#', "\t", $xml );
		$xml  = preg_replace( '#<w:(br|cr)\s*/>#', "\n", $xml );
		$xml  = preg_replace( '#</w:p>#', "\n", $xml );
		$text = html_entity_decode( (string) preg_replace( '#<[^>]+>#', '', (string) $xml ), ENT_QUOTES | ENT_XML1, 'UTF-8' );
		return trim( (string) preg_replace( "/\n{3,}/", "\n\n", $text ) );
	}

	/**
	 * PDF: decode content streams and read text operators.
	 *
	 * @param string $raw PDF bytes.
	 */
	public static function pdf( string $raw ): string {
		// Page by page, decoding each font with its own character map. This reads PDFs
		// saved from Word, Google Docs and LibreOffice, whose fonts store glyph numbers.
		$text = self::pdf_pages( $raw );
		if ( '' === trim( $text ) ) {
			$text = self::pdf_streams( $raw );
		}
		$text = preg_replace( '/[ \t]+/', ' ', $text );
		return trim( (string) preg_replace( "/\n{3,}/", "\n\n", (string) $text ) );
	}

	/**
	 * Fallback: read text from every content stream, without fonts.
	 *
	 * @param string $raw PDF bytes.
	 */
	private static function pdf_streams( string $raw ): string {
		$out = array();
		if ( preg_match_all( '#<<(.*?)>>\s*stream\r?\n(.*?)\r?\n?endstream#s', $raw, $m, PREG_SET_ORDER ) ) {
			foreach ( $m as $stream ) {
				$dict = $stream[1];
				if ( preg_match( '#/Subtype\s*/(Image|XML)|/Type\s*/(XObject|Metadata|XRef|ObjStm)#', $dict ) && ! preg_match( '#/Subtype\s*/Form#', $dict ) ) {
					continue;
				}
				$data = self::pdf_decode( $dict, $stream[2] );
				if ( null === $data ) {
					continue;
				}
				$text = self::pdf_text( $data );
				if ( '' !== trim( $text ) ) {
					$out[] = $text;
				}
			}
		}
		return implode( "\n", $out );
	}

	/**
	 * Decode a stream body, or null when its filter is not supported.
	 *
	 * @param string $dict Stream dictionary.
	 * @param string $data Raw stream bytes.
	 */
	private static function pdf_decode( string $dict, string $data ): ?string {
		if ( preg_match( '#/(ASCII85Decode|A85)\b#', $dict ) ) {
			$data = self::ascii85( $data );
			if ( ! preg_match( '#/(FlateDecode|Fl)\b#', $dict ) ) {
				return $data;
			}
		}
		if ( preg_match( '#/(FlateDecode|Fl)\b#', $dict ) ) {
			// Cap each stream at 20 MB so a crafted PDF cannot exhaust memory.
			$decoded = @gzuncompress( $data, 20 * MB_IN_BYTES ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			if ( false === $decoded ) {
				$decoded = @gzinflate( substr( $data, 2 ), 20 * MB_IN_BYTES ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			}
			return false === $decoded ? null : $decoded;
		}
		return preg_match( '#/Filter#', $dict ) ? null : $data;
	}

	/**
	 * ASCII85 decode.
	 *
	 * @param string $data Encoded data.
	 */
	private static function ascii85( string $data ): string {
		$data = (string) preg_replace( '/\s+/', '', $data );
		if ( 0 === strpos( $data, '<~' ) ) {
			$data = substr( $data, 2 );
		}
		$end = strpos( $data, '~>' );
		if ( false !== $end ) {
			$data = substr( $data, 0, $end );
		}
		$out   = '';
		$group = array();
		$len   = strlen( $data );
		for ( $i = 0; $i < $len; $i++ ) {
			$c = $data[ $i ];
			if ( 'z' === $c && ! $group ) {
				$out .= "\0\0\0\0";
				continue;
			}
			$v = ord( $c ) - 33;
			if ( $v < 0 || $v > 84 ) {
				continue;
			}
			$group[] = $v;
			if ( 5 === count( $group ) ) {
				$n = 0;
				foreach ( $group as $g ) {
					$n = $n * 85 + $g;
				}
				$out  .= pack( 'N', $n & 0xFFFFFFFF );
				$group = array();
			}
		}
		$rest = count( $group );
		if ( $rest > 1 ) {
			$group = array_pad( $group, 5, 84 );
			$n     = 0;
			foreach ( $group as $g ) {
				$n = $n * 85 + $g;
			}
			$out .= substr( pack( 'N', $n & 0xFFFFFFFF ), 0, $rest - 1 );
		}
		return $out;
	}

	/**
	 * All objects in the file, including those packed in object streams.
	 *
	 * @param string $raw PDF bytes.
	 * @return array<int, array{dict:string, stream:?string}>
	 */
	private static function pdf_objects( string $raw ): array {
		$objects = array();
		if ( ! preg_match_all( '#(\d+)\s+\d+\s+obj\b(.*?)\bendobj#s', $raw, $m, PREG_SET_ORDER ) ) {
			return $objects;
		}
		foreach ( $m as $obj ) {
			$body   = $obj[2];
			$stream = null;
			if ( preg_match( '#^(.*?)stream\r?\n(.*?)\r?\n?endstream#s', $body, $sm ) ) {
				$body   = $sm[1];
				$stream = self::pdf_decode( $body, $sm[2] );
			}
			$objects[ (int) $obj[1] ] = array(
				'dict'   => $body,
				'stream' => $stream,
			);
		}
		// Objects packed in object streams (PDF 1.5 and later).
		foreach ( $objects as $obj ) {
			if ( null === $obj['stream'] || ! preg_match( '#/Type\s*/ObjStm#', $obj['dict'] ) || ! preg_match( '#/First\s+(\d+)#', $obj['dict'], $first ) ) {
				continue;
			}
			$head = substr( $obj['stream'], 0, (int) $first[1] );
			preg_match_all( '#(\d+)\s+(\d+)#', $head, $pairs, PREG_SET_ORDER );
			$count = count( $pairs );
			for ( $i = 0; $i < $count; $i++ ) {
				$start = (int) $first[1] + (int) $pairs[ $i ][2];
				$end   = isset( $pairs[ $i + 1 ] ) ? (int) $first[1] + (int) $pairs[ $i + 1 ][2] : strlen( $obj['stream'] );
				$num   = (int) $pairs[ $i ][1];
				if ( ! isset( $objects[ $num ] ) ) {
					$objects[ $num ] = array(
						'dict'   => substr( $obj['stream'], $start, $end - $start ),
						'stream' => null,
					);
				}
			}
		}
		return $objects;
	}

	/**
	 * Text of every page, in page order, with each font's character map applied.
	 *
	 * @param string $raw PDF bytes.
	 */
	private static function pdf_pages( string $raw ): string {
		$objects = self::pdf_objects( $raw );
		if ( ! $objects ) {
			return '';
		}
		$pages = array();
		foreach ( $objects as $num => $obj ) {
			if ( preg_match( '#/Type\s*/Pages\b#', $obj['dict'] ) && ! preg_match( '#/Parent\s+\d+\s+\d+\s+R#', $obj['dict'] ) ) {
				self::pdf_walk( $objects, $num, $pages, 0 );
				break;
			}
		}
		if ( ! $pages ) {
			foreach ( $objects as $num => $obj ) {
				if ( preg_match( '#/Type\s*/Page\b#', $obj['dict'] ) ) {
					$pages[] = $num;
				}
			}
		}
		$cmaps = array();
		$out   = array();
		foreach ( $pages as $num ) {
			$fonts = array();
			foreach ( self::pdf_page_fonts( $objects, $num ) as $name => $font_num ) {
				if ( ! isset( $cmaps[ $font_num ] ) ) {
					$cmaps[ $font_num ] = self::pdf_font_cmap( $objects, $font_num );
				}
				$fonts[ $name ] = $cmaps[ $font_num ];
			}
			$content = '';
			$dict    = $objects[ $num ]['dict'];
			if ( preg_match( '#/Contents\s*\[(.*?)\]#s', $dict, $cm ) ) {
				preg_match_all( '#(\d+)\s+\d+\s+R#', $cm[1], $refs );
				foreach ( $refs[1] as $ref ) {
					$content .= ( $objects[ (int) $ref ]['stream'] ?? '' ) . "\n";
				}
			} elseif ( preg_match( '#/Contents\s+(\d+)\s+\d+\s+R#', $dict, $cm ) ) {
				$content = (string) ( $objects[ (int) $cm[1] ]['stream'] ?? '' );
			}
			$text = self::pdf_text( $content, $fonts );
			if ( '' !== trim( $text ) ) {
				$out[] = $text;
			}
		}
		return implode( "\n\n", $out );
	}

	/**
	 * Collect page objects in order from a page tree node.
	 *
	 * @param array $objects Objects.
	 * @param int   $num     Node object number.
	 * @param array $pages   Collected page numbers.
	 * @param int   $depth   Recursion depth.
	 */
	private static function pdf_walk( array $objects, int $num, array &$pages, int $depth ): void {
		if ( $depth > 32 || ! isset( $objects[ $num ] ) || count( $pages ) > 2000 ) {
			return;
		}
		$dict = $objects[ $num ]['dict'];
		if ( preg_match( '#/Type\s*/Page\b#', $dict ) ) {
			$pages[] = $num;
			return;
		}
		if ( preg_match( '#/Kids\s*\[(.*?)\]#s', $dict, $kids ) ) {
			preg_match_all( '#(\d+)\s+\d+\s+R#', $kids[1], $refs );
			foreach ( $refs[1] as $ref ) {
				self::pdf_walk( $objects, (int) $ref, $pages, $depth + 1 );
			}
		}
	}

	/**
	 * Font resource names on a page, mapped to font object numbers.
	 *
	 * @param array $objects Objects.
	 * @param int   $num     Page object number.
	 * @return array<string, int>
	 */
	private static function pdf_page_fonts( array $objects, int $num ): array {
		for ( $depth = 0; $depth < 32 && isset( $objects[ $num ] ); $depth++ ) {
			$dict = $objects[ $num ]['dict'];
			if ( preg_match( '#/Resources\s+(\d+)\s+\d+\s+R#', $dict, $r ) ) {
				$dict = (string) ( $objects[ (int) $r[1] ]['dict'] ?? '' );
			}
			$fonts = '';
			if ( preg_match( '#/Font\s*<<(.*?)>>#s', $dict, $f ) ) {
				$fonts = $f[1];
			} elseif ( preg_match( '#/Font\s+(\d+)\s+\d+\s+R#', $dict, $f ) ) {
				$fonts = (string) ( $objects[ (int) $f[1] ]['dict'] ?? '' );
			}
			if ( '' !== $fonts ) {
				preg_match_all( '#/([^\s/<>\[\]()]+)\s+(\d+)\s+\d+\s+R#', $fonts, $pairs, PREG_SET_ORDER );
				$map = array();
				foreach ( $pairs as $pair ) {
					$map[ $pair[1] ] = (int) $pair[2];
				}
				return $map;
			}
			if ( ! preg_match( '#/Parent\s+(\d+)\s+\d+\s+R#', $objects[ $num ]['dict'], $parent ) ) {
				break;
			}
			$num = (int) $parent[1];
		}
		return array();
	}

	/**
	 * A font's ToUnicode map: code length in bytes and hex code to text.
	 *
	 * @param array $objects  Objects.
	 * @param int   $font_num Font object number.
	 * @return array{bytes:int, map:array<string,string>}|null Null when the font has no map.
	 */
	private static function pdf_font_cmap( array $objects, int $font_num ): ?array {
		$dict = (string) ( $objects[ $font_num ]['dict'] ?? '' );
		if ( ! preg_match( '#/ToUnicode\s+(\d+)\s+\d+\s+R#', $dict, $t ) ) {
			return null;
		}
		$cmap = (string) ( $objects[ (int) $t[1] ]['stream'] ?? '' );
		if ( '' === $cmap ) {
			return null;
		}
		$bytes = 2;
		if ( preg_match( '#begincodespacerange\s*<([0-9A-Fa-f]+)>#', $cmap, $cs ) ) {
			$bytes = max( 1, (int) ( strlen( $cs[1] ) / 2 ) );
		}
		$map = array();
		if ( preg_match_all( '#beginbfchar(.*?)endbfchar#s', $cmap, $blocks ) ) {
			foreach ( $blocks[1] as $block ) {
				preg_match_all( '#<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]*)>#', $block, $pairs, PREG_SET_ORDER );
				foreach ( $pairs as $pair ) {
					$map[ strtoupper( $pair[1] ) ] = self::utf16_hex( $pair[2] );
				}
			}
		}
		if ( preg_match_all( '#beginbfrange(.*?)endbfrange#s', $cmap, $blocks ) ) {
			foreach ( $blocks[1] as $block ) {
				preg_match_all( '#<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>\s*(<[0-9A-Fa-f]*>|\[[^\]]*\])#', $block, $ranges, PREG_SET_ORDER );
				foreach ( $ranges as $range ) {
					$lo  = hexdec( $range[1] );
					$hi  = min( hexdec( $range[2] ), $lo + 65535 );
					$len = strlen( $range[1] );
					if ( '[' === $range[3][0] ) {
						preg_match_all( '#<([0-9A-Fa-f]*)>#', $range[3], $dsts );
						foreach ( $dsts[1] as $i => $dst ) {
							$map[ strtoupper( str_pad( dechex( $lo + $i ), $len, '0', STR_PAD_LEFT ) ) ] = self::utf16_hex( $dst );
						}
						continue;
					}
					$dst  = substr( $range[3], 1, -1 );
					$base = hexdec( substr( $dst, -4 ) );
					$pre  = substr( $dst, 0, -4 );
					for ( $code = $lo; $code <= $hi; $code++ ) {
						$map[ strtoupper( str_pad( dechex( $code ), $len, '0', STR_PAD_LEFT ) ) ] = self::utf16_hex( $pre . str_pad( dechex( $base + $code - $lo ), 4, '0', STR_PAD_LEFT ) );
					}
				}
			}
		}
		return $map ? array(
			'bytes' => $bytes,
			'map'   => $map,
		) : null;
	}

	/**
	 * UTF-16BE hex to UTF-8.
	 *
	 * @param string $hex Hex.
	 */
	private static function utf16_hex( string $hex ): string {
		if ( '' === $hex || strlen( $hex ) % 2 ) {
			return '';
		}
		$bin = (string) hex2bin( $hex );
		return function_exists( 'mb_convert_encoding' ) ? (string) mb_convert_encoding( $bin, 'UTF-8', 'UTF-16BE' ) : $bin;
	}

	/**
	 * Bytes of a shown string to text with a font's map.
	 *
	 * @param string     $bin  Raw bytes.
	 * @param array|null $cmap Font map from pdf_font_cmap().
	 */
	private static function pdf_map( string $bin, ?array $cmap ): string {
		if ( null === $cmap ) {
			return self::to_utf8( $bin );
		}
		$out = '';
		$len = strlen( $bin );
		for ( $i = 0; $i + $cmap['bytes'] <= $len; $i += $cmap['bytes'] ) {
			$out .= $cmap['map'][ strtoupper( bin2hex( substr( $bin, $i, $cmap['bytes'] ) ) ) ] ?? '';
		}
		return $out;
	}

	/**
	 * Read text-showing operators from a content stream.
	 *
	 * @param string $content Stream content.
	 * @param array  $fonts   Font name to character map (from pdf_font_cmap), when known.
	 */
	public static function pdf_text( string $content, array $fonts = array() ): string {
		if ( ! preg_match_all( '#BT(.*?)ET#s', $content, $blocks ) ) {
			return '';
		}
		$lines = array();
		$cmap  = null;
		foreach ( $blocks[1] as $block ) {
			$line = '';
			preg_match_all( '#(/[^\s/<>\[\]()]+\s+-?[\d.]+\s+Tf|\[(?:[^\]\\\\]|\\\\.)*\]\s*TJ|\((?:[^()\\\\]|\\\\.|\((?:[^()\\\\]|\\\\.)*\))*\)\s*(?:Tj|\'|")|<[0-9A-Fa-f\s]+>\s*Tj|T\*|-?[\d.]+\s+-?[\d.]+\s+T[dD]|(?:-?[\d.]+\s+){6}Tm)#s', $block, $ops );
			foreach ( $ops[0] as $op ) {
				if ( '/' === $op[0] ) {
					$name = substr( (string) strtok( $op, " \t\r\n" ), 1 );
					$cmap = $fonts[ $name ] ?? null;
					continue;
				}
				if ( preg_match( '#T[dD*]$|Tm$#', $op ) ) {
					if ( preg_match( '#^-?[\d.]+\s+(-?[\d.]+)\s+T[dD]$#', $op, $td ) && 0.0 === (float) $td[1] ) {
						continue;
					}
					$line .= "\n";
					continue;
				}
				if ( '[' === $op[0] ) {
					preg_match_all( '#\((?:[^()\\\\]|\\\\.)*\)|<[0-9A-Fa-f\s]*>|-?[\d.]+#s', $op, $parts );
					foreach ( $parts[0] as $part ) {
						if ( '(' === $part[0] ) {
							$line .= self::pdf_map( self::pdf_bytes( substr( $part, 1, -1 ) ), $cmap );
						} elseif ( '<' === $part[0] ) {
							$line .= self::pdf_map( self::hex_bytes( substr( $part, 1, -1 ) ), $cmap );
						} elseif ( (float) $part < -200 ) {
							$line .= ' ';
						}
					}
				} elseif ( '<' === $op[0] ) {
					$line .= self::pdf_map( self::hex_bytes( substr( $op, 1, strpos( $op, '>' ) - 1 ) ), $cmap );
				} else {
					$end   = strrpos( $op, ')' );
					$line .= self::pdf_map( self::pdf_bytes( substr( $op, 1, $end - 1 ) ), $cmap );
				}
			}
			$lines[] = trim( $line );
		}
		return implode( "\n", array_filter( $lines, 'strlen' ) );
	}

	/**
	 * Decode a PDF literal string.
	 *
	 * @param string $s Literal without the outer parentheses.
	 */
	public static function pdf_string( string $s ): string {
		return self::to_utf8( self::pdf_bytes( $s ) );
	}

	/**
	 * Raw bytes of a PDF literal string (escapes resolved).
	 *
	 * @param string $s Literal without the outer parentheses.
	 */
	private static function pdf_bytes( string $s ): string {
		$s = preg_replace_callback(
			'#\\\\([0-7]{1,3}|.)#s',
			static function ( $m ) {
				$map = array(
					'n'  => "\n",
					'r'  => '',
					't'  => "\t",
					'b'  => '',
					'f'  => '',
					'('  => '(',
					')'  => ')',
					'\\' => '\\',
				);
				if ( ctype_digit( $m[1] ) ) {
					return chr( octdec( $m[1] ) & 0xFF );
				}
				return $map[ $m[1] ] ?? $m[1];
			},
			$s
		);
		return (string) $s;
	}

	/**
	 * Decode a hex string (UTF-16 with BOM, or single bytes).
	 *
	 * @param string $hex Hex.
	 */
	public static function pdf_hex( string $hex ): string {
		return self::to_utf8( self::hex_bytes( $hex ) );
	}

	/**
	 * Raw bytes of a PDF hex string.
	 *
	 * @param string $hex Hex.
	 */
	private static function hex_bytes( string $hex ): string {
		$hex = (string) preg_replace( '/[^0-9A-Fa-f]/', '', $hex );
		if ( strlen( $hex ) % 2 ) {
			$hex .= '0';
		}
		return (string) hex2bin( $hex );
	}

	/**
	 * Bytes to UTF-8 (UTF-16BE with BOM, or Latin-1).
	 *
	 * @param string $bin Bytes.
	 */
	private static function to_utf8( string $bin ): string {
		if ( 0 === strpos( $bin, "\xFE\xFF" ) && function_exists( 'mb_convert_encoding' ) ) {
			return (string) mb_convert_encoding( substr( $bin, 2 ), 'UTF-8', 'UTF-16BE' );
		}
		if ( function_exists( 'mb_check_encoding' ) && mb_check_encoding( $bin, 'UTF-8' ) ) {
			return $bin;
		}
		$out = function_exists( 'mb_convert_encoding' ) ? (string) mb_convert_encoding( $bin, 'UTF-8', 'ISO-8859-1' ) : $bin;
		// Drop control characters that come from glyph IDs without a text map.
		return (string) preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $out );
	}

	/**
	 * Readable text from an HTML page (main content preferred).
	 *
	 * @param string $html HTML.
	 * @return array{title:string,text:string}
	 */
	public static function html( string $html ): array {
		$title = preg_match( '#<title[^>]*>(.*?)</title>#is', $html, $m ) ? trim( html_entity_decode( wp_strip_all_tags( $m[1] ), ENT_QUOTES, 'UTF-8' ) ) : '';
		$html  = preg_replace( '#<(script|style|noscript|svg|nav|header|footer|form|iframe|template)\b[^>]*>.*?</\1>#is', ' ', $html );
		if ( preg_match( '#<main\b[^>]*>(.*?)</main>#is', (string) $html, $m ) || preg_match( '#<article\b[^>]*>(.*?)</article>#is', (string) $html, $m ) ) {
			$html = $m[1];
		} elseif ( preg_match( '#<body\b[^>]*>(.*?)</body>#is', (string) $html, $m ) ) {
			$html = $m[1];
		}
		$html = preg_replace( '#</(p|div|li|h[1-6]|tr|section|br)>#i', "$0\n", (string) $html );
		$text = html_entity_decode( wp_strip_all_tags( (string) $html ), ENT_QUOTES, 'UTF-8' );
		$text = preg_replace( '/[ \t]+/', ' ', $text );
		$text = preg_replace( '/ *\n */', "\n", (string) $text );
		return array(
			'title' => $title,
			'text'  => trim( (string) preg_replace( "/\n{3,}/", "\n\n", (string) $text ) ),
		);
	}

	/**
	 * URLs from a sitemap or sitemap index.
	 *
	 * @param string $xml XML.
	 * @return array{urls:string[],sitemaps:string[]}
	 */
	public static function sitemap( string $xml ): array {
		$urls     = array();
		$sitemaps = array();
		$is_index = false !== stripos( $xml, '<sitemapindex' );
		if ( preg_match_all( '#<loc>\s*(.*?)\s*</loc>#is', $xml, $m ) ) {
			foreach ( $m[1] as $loc ) {
				$loc = html_entity_decode( trim( preg_replace( '#^<!\[CDATA\[(.*)\]\]>$#s', '$1', $loc ) ), ENT_QUOTES | ENT_XML1, 'UTF-8' );
				if ( preg_match( '#^https?://#i', $loc ) ) {
					if ( $is_index ) {
						$sitemaps[] = $loc;
					} else {
						$urls[] = $loc;
					}
				}
			}
		}
		return array(
			'urls'     => array_values( array_unique( $urls ) ),
			'sitemaps' => array_values( array_unique( $sitemaps ) ),
		);
	}
}
