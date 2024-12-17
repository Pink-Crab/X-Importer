<?php

declare(strict_types=1);

/**
 * Collection of helper methods used for working with Tweet content.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Util;

use PinkCrab\X_Importer\Tweet\Tweet;
use PinkCrab\X_Importer\Tweet\Entity\Media;
use PinkCrab\X_Importer\File_System\File_Manager;
use PinkCrab\X_Importer\Media\Media_Upload;

/**
 * Content_Helper
 */
class Content_Helper {

	/**
	 * Holds access to a remote media url.
	 *
	 * @var string|null
	 */
	private static $remote_media_url = null;

	/**
	 * Sets the remote media url.
	 *
	 * @param string $url The url to set.
	 *
	 * @return void
	 */
	public static function set_remote_media_url( string $url ): void {
		self::$remote_media_url = \trailingslashit( \untrailingslashit( \sanitize_url( $url ) ) );
	}

	/**
	 * Populate Hashtags.
	 *
	 * @param string                                                                                    $content The content to search for hashtags.
	 * @param Tweet                                                                                     $tweet   The tweet to populate.
	 * @param array{class:string, target:string, callable(link:string, url:string, text:string):string} $args
	 *
	 * @return string
	 */
	public static function populate_hashtags( string $content, Tweet $tweet, array $args = array() ): string {
		$default_args = array(
			'class'    => 'hashtag',
			'target'   => '_blank',
			'callable' => function ( string $link, string $url, string $text ): string {
				return $link;
			},
		);

		// Merge the default and passed args.
		$args = wp_parse_args( $args, $default_args );

		// Find all hashtags in the content.
		preg_match_all( '/#(\w+)/', $content, $matches );

		// If we have matches, loop and replace.
		if ( ! empty( $matches[0] ) ) {
			foreach ( $matches[0] as $match ) {
				$url     = 'https://x.com/hashtag/' . str_replace( '#', '', $match );
				$link    = sprintf( '<a href="%s" class="%s" target="%s">%s</a>', $url, $args['class'], $args['target'], $match );
				$content = str_replace( $match, $args['callable']( $link, $url, $match ), $content );
			}
		}

		return $content;
	}

	/**
	 * Populate links
	 *
	 * @param string                                                                                    $content The content to search for links.
	 * @param Tweet                                                                                     $tweet   The tweet to populate.
	 * @param array{class:string, target:string, callable(link:string, url:string, text:string):string} $args    The args to pass to the method.
	 *
	 * @return string
	 */
	public static function populate_urls( string $content, Tweet $tweet, array $args = array() ): string {
		$default_args = array(
			'class'    => 'link',
			'target'   => '_blank',
			'callable' => function ( string $link, string $url, string $text ): string {
				return $link;
			},
		);

		// Merge the default and passed args.
		$args = wp_parse_args( $args, $default_args );

		// Get the links.
		$links = $tweet->links();

		// Iterate over the links and look to replace teh display urls.
		foreach ( $links as $link ) {
			$url      = $link->url();
			$display  = $link->display_url();
			$expanded = $link->expanded_url();

			// Create the link.
			$link    = sprintf( '<a href="%s" class="%s" target="%s">%s</a>', $expanded, $args['class'], $args['target'], $display );
			$content = str_replace( $url, $args['callable']( $link, $expanded, $display ), $content );
		}

		return $content;
	}

	/**
	 * Populate mentions
	 *
	 * @param string                                                                                    $content The content to search for mentions.
	 * @param Tweet                                                                                     $tweet   The tweet to populate.
	 * @param array{class:string, target:string, callable(link:string, url:string, text:string):string} $args    The args to pass to the method.
	 *
	 * @return string
	 */
	public static function populate_mentions( string $content, Tweet $tweet, array $args = array() ): string {
		$default_args = array(
			'class'    => 'mention',
			'target'   => '_blank',
			'callable' => function ( string $link, string $url, string $text ): string {
				return $link;
			},
		);

		// Merge the default and passed args.
		$args = wp_parse_args( $args, $default_args );

		// Get the mentions.
		$mentions = $tweet->mentions();

		// Iterate over the mentions and look to replace the display urls.
		foreach ( $mentions as $mention ) {
			$url     = sprintf( 'https://x.com/%s', $mention->screen_name() );
			$display = '@' . $mention->screen_name();

			// Create the link.
			$link    = sprintf( '<a href="%s" class="%s" target="%s">%s</a>', $url, $args['class'], $args['target'], $display );
			$content = str_replace( $display, $args['callable']( $link, $url, $display ), $content );
		}
		return $content;
	}

	/**
	 * Uploads a Media model to the media library.
	 *
	 * @param Media $media The media to upload.
	 *
	 * @return @return array{
	 *   'attachment_id' => string,
	 *   'full_path'     => string,
	 *   'full_url'      => string,
	 *   'sizes'         => <string, array{name:string, url:string, path:string, width:integer, height:integer, filesize:integer, mime-type:string}>
	 * }
	 *
	 * @throws Exception If the remote file could not be downloaded.
	 * @throws Exception If the file could not be created.
	 */
	public static function upload_media( Media $media ): array {
		$media_uploader = new Media_Upload();
		// Attempt to upload the media from remote url if set.
		if ( ! is_null( self::$remote_media_url ) ) {
			$path = self::$remote_media_url . dirname( $media->url() );

			return $media_uploader->create_from_remote_path( $path, basename( $media->url() ) );
		}

		// If not, get from the media model.
		return $media_uploader->create_from_remote_path( $media->url(), basename( $media->url() ) );
	}
}
