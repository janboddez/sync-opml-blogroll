<?php
/**
 * Dead-simple OPML parser.
 *
 * @package Sync_OPML_Blogroll
 */

namespace Sync_OPML_Blogroll;

/**
 * Dead-simple OPML parser.
 */
class OPML_Parser {
	/**
	 * Feeds found after parsing.
	 *
	 * @var array $feeds
	 */
	private $feeds = array();

	/**
	 * Parses an OPML file.
	 *
	 * @param string $opml OPML string.
	 */
	public function parse( $opml ) {
		// Create an XML parser.
		if ( ! function_exists( 'xml_parser_create' ) ) {
			trigger_error( __( "PHP's XML extension is not available. Please contact your hosting provider to enable PHP's XML extension." ) );
			wp_die( __( "PHP's XML extension is not available. Please contact your hosting provider to enable PHP's XML extension." ) );
		}

		$xml_parser = xml_parser_create();

		// Set the functions to handle opening and closing tags.
		xml_set_element_handler( $xml_parser, array( $this, 'startElement' ), array( $this, 'endElement' ) );

		if ( ! xml_parse( $xml_parser, $opml, true ) ) {
			printf(
				/* translators: 1: Error message, 2: Line number. */
				__( 'XML Error: %1$s at line %2$s' ),
				xml_error_string( xml_get_error_code( $xml_parser ) ),
				xml_get_current_line_number( $xml_parser )
			);
		}

		// Free up memory used by the XML parser.
		xml_parser_free( $xml_parser );

		return $this->feeds;
	}

	/**
	 * XML callback function for the start of a new XML tag.
	 *
	 * @access private
	 *
	 * @param mixed  $parser  XML Parser resource.
	 * @param string $tagName XML element name.
	 * @param array  $attrs   XML element attributes.
	 */
	private function startElement( $parser, $tagName, $attrs ) { // phpcs:ignore WordPress.NamingConventions
		if ( 'OUTLINE' === $tagName ) { // phpcs:ignore WordPress.NamingConventions
			$name = '';

			if ( isset( $attrs['TEXT'] ) ) {
				$name = $attrs['TEXT'];
			}

			if ( isset( $attrs['TITLE'] ) ) {
				$name = $attrs['TITLE'];
			}

			$url = '';

			if ( isset( $attrs['URL'] ) ) {
				$url = $attrs['URL'];
			}

			if ( isset( $attrs['HTMLURL'] ) ) {
				$url = $attrs['HTMLURL'];
			}

			if ( empty( $url ) || empty( $attrs['XMLURL'] ) ) {
				// Skip.
				return;
			}

			$this->feeds[] = array(
				'name'        => $name,
				'url'         => $url,
				'target'      => isset( $attrs['TARGET'] ) ? $attrs['TARGET'] : '',
				'feed'        => isset( $attrs['XMLURL'] ) ? $attrs['XMLURL'] : '',
				'description' => isset( $attrs['DESCRIPTION'] ) ? $attrs['DESCRIPTION'] : '',
			);
		}
	}

	/**
	 * XML callback function that is called at the end of a XML tag.
	 *
	 * @access private
	 *
	 * @param mixed  $parser  XML Parser resource.
	 * @param string $tagName XML tag name.
	 */
	private function endElement( $parser, $tagName ) { // phpcs:ignore WordPress.NamingConventions
		// Nothing to do.
	}
}
