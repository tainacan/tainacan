<?php

namespace Tainacan\Rag;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Splits text into overlapping chunks for embedding.
 *
 * Pure string work, no database, no provider. That matters because chunking
 * decisions are the ones most likely to be wrong in subtle ways — a chunk that is
 * too large silently gets truncated by the model, and a chunk with no overlap loses
 * the sentence that straddles a boundary.
 *
 * Overlap is clamped to half the chunk size so the step forward is always positive;
 * an overlap equal to or larger than the size is an infinite loop, not a preference.
 *
 * @since 1.5.0
 */
class Chunker {

	/**
	 * Default chunk length in characters.
	 *
	 * Characters rather than tokens on purpose: token counts need the model's
	 * tokenizer, and a per-provider tokenizer would make chunk boundaries depend on
	 * which provider happens to be active. Character chunking plus a modest overlap
	 * keeps stored vectors comparable across a provider switch.
	 *
	 * @var int
	 */
	const DEFAULT_SIZE = 1000;

	/**
	 * Default overlap in characters.
	 *
	 * @var int
	 */
	const DEFAULT_OVERLAP = 150;

	/**
	 * Smallest chunk this will produce.
	 *
	 * @var int
	 */
	const MIN_SIZE = 100;

	/**
	 * Split one text into chunks.
	 *
	 * @param string   $text    Source text.
	 * @param int|null $size    Chunk length, defaults to the filterable default.
	 * @param int|null $overlap Overlap, clamped to half the size.
	 * @return string[]
	 */
	public function chunk( $text, $size = null, $overlap = null ) {
		$text = $this->normalise( $text );

		if ( '' === $text ) {
			return [];
		}

		$size = null === $size ? (int) apply_filters( 'tainacan_rag_chunk_size', self::DEFAULT_SIZE ) : (int) $size;
		$size = max( self::MIN_SIZE, $size );

		$overlap = null === $overlap ? (int) apply_filters( 'tainacan_rag_chunk_overlap', self::DEFAULT_OVERLAP ) : (int) $overlap;
		$overlap = min( max( 0, $overlap ), (int) floor( $size / 2 ) );

		$length  = mb_strlen( $text );
		$chunks  = [];
		$start   = 0;
		$step    = $size - $overlap;

		while ( $start < $length ) {
			$chunks[] = mb_substr( $text, $start, $size );

			if ( $start + $size >= $length ) {
				break;
			}

			$start += $step;
		}

		return $chunks;
	}

	/**
	 * Hash for a chunk, used to skip re-embedding unchanged content.
	 *
	 * @param string $content Chunk content.
	 * @return string
	 */
	public function hash( $content ) {
		return sha1( (string) $content );
	}

	/**
	 * Collapse whitespace and strip markup so the same text always hashes the same.
	 *
	 * Without this, a stray newline between two saves re-embeds a whole document.
	 *
	 * @param string $text Raw text.
	 * @return string
	 */
	public function normalise( $text ) {
		$text = wp_strip_all_tags( (string) $text );
		$text = preg_replace( '/\s+/u', ' ', $text );

		return trim( (string) $text );
	}
}
