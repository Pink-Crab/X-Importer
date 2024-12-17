<?php

declare(strict_types=1);

/**
 * Block Processor
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Processor;

use DateTimeImmutable;
use PinkCrab\X_Importer\Tweet\Tweet;
use PinkCrab\Perique\Application\App_Config;
use PinkCrab\X_Importer\Util\Content_Helper;
use PinkCrab\X_Importer\Post_Type\Post_Repository;

/**
 * Block_Processor
 */
final class Block_Processor implements Processor {

	protected $status   = self::PENDING;
	protected $messages = array();

	/**
	 * Access to the post importer.
	 *
	 * @var Post_Repository
	 */
	protected $posts;

	/**
	 * App Config
	 *
	 * @var App_Config
	 */
	protected $app_config;

	/**
	 * Create a new instance of the Block_Processor.
	 *
	 * @param Post_Repository $posts      The post importer.
	 * @param App_Config      $app_config The app config.
	 */
	public function __construct( Post_Repository $posts, App_Config $app_config ) {
		$this->posts      = $posts;
		$this->app_config = $app_config;
	}

	/**
	 * Process a tweet and its thread.
	 *
	 * @param Tweet   $tweet        The tweet to process.
	 * @param Tweet[] $thread       The thread of tweets.
	 * @param string  $on_duplicate The action to take if the post already exists.
	 *
	 * @return void
	 */
	public function process( Tweet $tweet, array $thread, string $on_duplicate ): void {
		// Set the status to pending and clear any messages.
		$this->status   = self::PENDING;
		$this->messages = array();

		// Checks if the post exists.
		$args  = array(
			'posts_per_page' => 1,
			'meta_query'     => array(
				array(
					'key'   => $this->app_config->post_meta( 'tweet_id' ),
					'value' => $tweet->id(),
				),
			),
		);
		$found = $this->posts->get_post( $args );

		$post_exists = null !== $found;

		// If tweet exists and we are set to skip, return.
		if ( $post_exists && 'skip' === $on_duplicate ) {
			$this->skip_tweet( $tweet, $thread );
		} elseif ( ! $post_exists || ( 'new' === $on_duplicate && $post_exists ) ) {
			$this->create_tweet( $tweet, $thread );
		} elseif ( $post_exists && 'update' === $on_duplicate ) {
			$this->update_tweet( $tweet, $thread );
		}

		/**
		 * Fires after a post has been processed.
		 *
		 * @param Tweet   $tweet  The tweet.
		 * @param Tweet[] $thread The thread of tweets.
		 * @param string  $status The status of the processor.
		 * @param string[] $messages The messages from the processor.
		 */
		do_action( 'pc_x_importer_post_block_processed', $tweet, $thread, $this->status, $this->messages );
	}

	/**
	 * Compile the content for the tweet.
	 *
	 * @param Tweet   $tweet  The tweet to parse.
	 * @param Tweet[] $thread The threaded tweets.
	 *
	 * @return string
	 */
	protected function compile_content( Tweet $tweet, array $thread ): string {
		$content        = $this->parse_post_content( $tweet );
		$thread_content = $this->parse_thread_content( $thread );
		return $content . PHP_EOL . $thread_content;
	}

	/**
	 * Skip the tweet.
	 *
	 * @param Tweet   $tweet  The tweet to skip.
	 * @param Tweet[] $thread The threaded tweets.
	 *
	 * @return void
	 */
	protected function skip_tweet( Tweet $tweet, array $thread ): void {
		$this->status     = self::SUCCESS;
		$this->messages[] = sprintf( 'Post with ID: %s already exists and was skipped.', $tweet->id() );

		/**
		 * Fires after a post has been skipped.
		 *
		 * @param Tweet   $tweet  The tweet.
		 * @param Tweet[] $thread The thread of tweets.
		 */
		do_action( 'pc_x_importer_post_block_skipped', $tweet, $thread );
	}

	/**
	 * Create tweet.
	 *
	 * @param Tweet   $tweet  The tweet to parse.
	 * @param Tweet[] $thread The threaded tweets.
	 *
	 * @return void
	 *
	 * @throws \Exception If the post could not be created.
	 */
	protected function create_tweet( Tweet $tweet, array $thread ): void {
		try {
			$post = $this->posts->create_post(
				$tweet->id(),
				$this->compile_content( $tweet, $thread ),
				\apply_filters( 'pc_x_importer_post_block_author', 0 ),
				new DateTimeImmutable( $tweet->date() )
			);

			\update_post_meta( $post->ID, 'pc_x_tweet_id', $tweet->id() );
			\update_post_meta( $post->ID, 'pc_x_tweet_thread', $thread );
			\update_post_meta( $post->ID, 'pc_x_tweet', $tweet );
		} catch ( \Exception $e ) {
			$this->status = self::ERROR;

			$message = \sprintf( 'Error creating post for tweet: %s, %s', $tweet->id(), $e->getMessage() );

			$this->messages[] = esc_html( $message );

			/**
			 * Fires when an error occurs creating a post.
			 * Fires before the exception is thrown.
			 *
			 * @param \WP_Post|null $post The post created.
			 * @param Tweet $tweet The tweet.
			 * @param Tweet[] $thread The thread of tweets.
			 */
			do_action( 'pc_x_importer_post_block_error', null, $tweet, $thread );

			throw new \Exception( esc_html( $message ) );
		}

		$this->messages[] = sprintf( 'Tweet created %s (#%s)', $post->ID, $tweet->id() );
		$this->status     = self::SUCCESS;

		/**
		 * Fires after a post has been created.
		 *
		 * @param \WP_Post    $post   The post.
		 * @param Tweet  $tweet  The tweet.
		 * @param Tweet[] $thread The thread of tweets.
		 */
		do_action( 'pc_x_importer_post_block_created', $post, $tweet, $thread );
	}

	/**
	 * Update tweet.
	 *
	 * @param Tweet   $tweet  The tweet to parse.
	 * @param Tweet[] $thread The threaded tweets.
	 *
	 * @return void
	 *
	 * @throws \Exception If the post could not be updated.
	 */
	protected function update_tweet( Tweet $tweet, array $thread ): void {
		try {
			$post_id = $this->posts->get_post( $tweet->id() );
			if ( \is_null( $post_id ) ) {
				throw new \Exception( 'Post cant be found with ID: ' . $tweet->id() );
			}
			$post = $this->posts->update_post(
				$post_id,
				$tweet->id(),
				$this->compile_content( $tweet, $thread ),
				\apply_filters( 'pc_x_importer_post_block_author', 0 ),
				new DateTimeImmutable( $tweet->date() )
			);

			\update_post_meta( $post->ID, 'pc_x_tweet_id', $tweet->id() );
			\update_post_meta( $post->ID, 'pc_x_tweet_thread', $thread );
			\update_post_meta( $post->ID, 'pc_x_tweet', $tweet );
		} catch ( \Exception $e ) {
			$this->status = self::ERROR;

			$message          = \sprintf( 'Error updating post for tweet: %s, %s', $tweet->id(), $e->getMessage() );
			$this->messages[] = esc_html( $message );

			/**
			 * Fires when an error occurs updating a post.
			 * Fires before the exception is thrown.
			 *
			 * @param \WP_Post|null $post The post updated.
			 * @param Tweet $tweet The tweet.
			 * @param Tweet[] $thread The thread of tweets.
			 */
			do_action( 'pc_x_importer_post_block_error', null, $tweet, $thread );

			throw new \Exception( esc_html( $message ) );
		}

		$this->status     = self::SUCCESS;
		$this->messages[] = sprintf( 'Tweet updated %s (#%s)', $post->ID, $tweet->id() );

		/**
		 * Fires after a post has been updated.
		 *
		 * @param \WP_Post    $post   The post.
		 * @param Tweet  $tweet  The tweet.
		 * @param Tweet[] $thread The thread of tweets.
		 */
		do_action( 'pc_x_importer_post_block_updated', $post, $tweet, $thread );
	}


	/**
	 * Parse the thread content.
	 *
	 * @param Tweet[] $tweets The tweet to parse.
	 *
	 * @return string
	 */
	protected function parse_thread_content( array $tweets ): string {
		// If we have no content, return empty string.
		if ( empty( $tweets ) ) {
			return '';
		}

		/**
		 * Filter to change the reveal label for threaded tweets.
		 *
		 * @param string $reveal_label The current reveal label.
		 * @param Tweet[] $tweets The threaded tweets.
		 *
		 * @return string
		 */
		$reveal_label = apply_filters(
			'pc_x_importer_thread_reveal_label',
			// translators: %s: The number of replies.
			sprintf( _n( 'Read %s reply', 'Read %s replies', count( $tweets ), 'pc-x' ), count( $tweets ) ),
			$tweets
		);

		$content = '<!-- wp:details -->
<details class="wp-block-details pc-x--threaded-replies">
<summary>' . esc_html( $reveal_label ) . '</summary>';
		// Loop and parse each tweet.
		foreach ( $tweets as $tweet ) {
			$content .= $this->parse_post_content( $tweet );
		}
		$content .= '</details>
<!-- /wp:details -->';

		return $content;
	}

	/**
	 * Parse the main post_content from the tweet.
	 *
	 * @param Tweet $tweet The tweet to parse.
	 *
	 * @return string
	 */
	protected function parse_post_content( Tweet $tweet ): string {
		$content  = sprintf(
			'<!-- wp:paragraph -->
<p id="pc-x-tweet-%s" class="pc-x--tweet">%s',
			$tweet->id(),
			$tweet->content()
		);
		$content  = Content_Helper::populate_hashtags( $content, $tweet, array( 'class' => 'pc-x--hashtag' ) );
		$content  = Content_Helper::populate_mentions( $content, $tweet, array( 'class' => 'pc-x--mention' ) );
		$content  = Content_Helper::populate_urls( $content, $tweet, array( 'class' => 'pc-x--link' ) );
		$content .= '</p>
<!-- /wp:paragraph -->' . PHP_EOL . PHP_EOL;
		$content  = $this->add_media( $content, $tweet );
		return $content;
	}

	/**
	 * Add media to end of the post.
	 *
	 * @param string $content The current content.
	 * @param Tweet  $tweet   The tweet to parse.
	 *
	 * @return string
	 */
	protected function add_media( string $content, Tweet $tweet ): string {
		// Get the media items
		$media = $tweet->media();

		// Separate the photos and videos.
		$photos = array_filter( $media, fn( $item ) => $item->type() === 'photo' );
		$videos = array_filter( $media, fn( $item ) => $item->type() === 'video' );
		// If we have photos, add it to the end of the content.
		if ( ! empty( $photos ) ) {
			$content = $this->parse_gallery( $photos, $content );
		}

		// @todo videos
		return $content;
	}

	/**
	 * Parse image gallery from media item.
	 *
	 * @param Media[] $media   The media items to parse.
	 * @param string  $content The content to parse.
	 *
	 * @return string
	 */
	protected function parse_gallery( array $media, string $content ): string {
		// If we have no media, return the content.
		if ( empty( $media ) ) {
			return $content;
		}

		$media_content = '<!-- wp:gallery {"linkTo":"none"} -->
<figure class="wp-block-gallery has-nested-images columns-default is-cropped pc-x--gallery">';
		foreach ( $media as $media_item ) {
			try {
				$file = Content_Helper::upload_media( $media_item );
			} catch ( \Exception $e ) {
				$this->messages[] = $e->getMessage();
				continue;
			}
			// remove the media url from the content.
			$content = str_replace( $media_item->display_url(), '', $content );
			// Add the media to the gallery.
			$media_content .= $this->create_gallery_item( $file );
		}
		return $content . $media_content . '</figure>
<!-- /wp:gallery -->';
	}

	/**
	 * Get the status of the processor.
	 *
	 * @return string
	 */
	final public function get_status(): string {
		return $this->status;
	}

	/**
	 * Get the messages.
	 *
	 * @return string[]
	 */
	final public function get_messages(): array {
		return $this->messages;
	}

	/**
	 * Creates gallery image item from an attachment ID.
	 *
	 * @param array{
	 *   'attachment_id' => string,
	 *   'full_path'     => string,
	 *   'full_url'      => string,
	 *   'sizes'         => <string, array{name:string, url:string, path:string, width:integer, height:integer, filesize:integer, mime-type:string}>
	 * } $attachment The attachment to create the gallery item for.
	 *
	 * @return string
	 */
	protected function create_gallery_item( array $attachment ): string {
		$attachment_id = $attachment['attachment_id'];
		$image_size    = apply_filters( 'pc_x_importer_gallery_image_size', 'full' );
		$attachment    = wp_get_attachment_image_src( $attachment_id, $image_size );

		$image_class  = \apply_filters( 'pc_x_importer_gallery_image_class', 'wp-image-' . $attachment_id );
		$use_lightbox = \boolval( \apply_filters( 'pc_x_importer_gallery_lightbox', true ) ) ? 'true' : 'false';

		// If we have no attachment, return empty string.
		if ( empty( $attachment ) ) {
			return '';
		}
		return <<<HTML
<!-- wp:image {"lightbox":{"enabled":{$use_lightbox}},"id":{$attachment_id},"sizeSlug":"{$image_size}","linkDestination":"none"} -->
<figure class="wp-block-image size-{$image_size} pc-x--image"><img src="{$attachment[0]}" alt="" class="{$image_class}"/></figure>
<!-- /wp:image -->
HTML;
	}
}
