<?php

namespace Tainacan;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Basic quality gate for PDF text extracted via Smalot PdfParser.
 *
 * Rejects non-empty but corrupted output so it is not stored in document_content_index.
 */
class Pdf_Extracted_Text_Quality {

	const MIN_LENGTH = 100;

	const MIN_LETTER_RATIO = 0.38;

	const MIN_READABLE_WORD_COUNT = 12;

	const MIN_READABLE_WORD_LETTERS = 2;

	/**
	 * Whether extracted PDF text is usable for storage and search.
	 *
	 * @param string $text Extracted text.
	 *
	 * @return bool
	 */
	public static function is_usable( $text ) {
		if ( ! is_string( $text ) ) {
			return false;
		}

		$text = trim( $text );
		if ( $text === '' ) {
			return false;
		}

		if ( mb_strlen( $text, 'UTF-8' ) < self::MIN_LENGTH ) {
			return false;
		}

		$passes = self::passes_heuristics( $text );

		return (bool) apply_filters( 'tainacan_pdf_extracted_text_is_usable', $passes, $text );
	}

	/**
	 * @param string $text Trimmed extracted text.
	 *
	 * @return bool
	 */
	private static function passes_heuristics( $text ) {
		if ( preg_match( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $text ) ) {
			return false;
		}

		$non_whitespace = preg_replace( '/\s+/u', '', $text );
		if ( $non_whitespace === null || $non_whitespace === '' ) {
			return false;
		}

		$non_ws_length = mb_strlen( $non_whitespace, 'UTF-8' );
		if ( $non_ws_length < 1 ) {
			return false;
		}

		if ( ! preg_match_all( '/\p{L}/u', $text, $letter_matches ) ) {
			return false;
		}

		if ( ( count( $letter_matches[0] ) / $non_ws_length ) < self::MIN_LETTER_RATIO ) {
			return false;
		}

		if ( self::count_readable_words( $text ) < self::MIN_READABLE_WORD_COUNT ) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $text Extracted text.
	 *
	 * @return int
	 */
	private static function count_readable_words( $text ) {
		$tokens = preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY );
		if ( ! is_array( $tokens ) ) {
			return 0;
		}

		$readable = 0;

		foreach ( $tokens as $token ) {
			if ( self::is_readable_word_token( $token ) ) {
				++$readable;
			}
		}

		return $readable;
	}

	/**
	 * @param string $token Word token.
	 *
	 * @return bool
	 */
	private static function is_readable_word_token( $token ) {
		$core = preg_replace( '/[^\p{L}\p{M}\p{N}]/u', '', $token );
		if ( $core === null || $core === '' ) {
			return false;
		}

		$core_length = mb_strlen( $core, 'UTF-8' );
		if ( $core_length < self::MIN_READABLE_WORD_LETTERS ) {
			return false;
		}

		if ( ! preg_match_all( '/\p{L}/u', $core, $letter_matches ) ) {
			return false;
		}

		return ( count( $letter_matches[0] ) / $core_length ) >= 0.7;
	}
}
