<?php

declare(strict_types=1);

/**
 * The Tweet Post Repository.
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Post_Type;

use PinkCrab\Perique\Application\App_Config;

/**
 * Post Repository.
 */
class Post_Repository {

	/**
	 * Access to App Config.
	 *
	 * @var App_Config
	 */
	protected $app_config;

	/**
	 * The post type.
	 *
	 * @var string
	 */
	protected $post_type;

	/**
	 * Create a new instance of the Post_Importer.
	 *
	 * @param string $post_type The post type.
	 */
	public function __construct( string $post_type ) {
		$this->post_type = $post_type;
	}

	/**
	 * Get the post type.
	 *
	 * @return string
	 */
	public function get_post_type(): string {
		return $this->post_type;
	}

	/**
	 * Finds a post based on its slug.
	 *
	 * @param array<string, mixed> $args The post args.
	 *
	 * @return \WP_Post|null
	*/
	public function get_post( array $args ): ?\WP_Post {

		// Set the post type.
		$args['post_type'] = $this->get_post_type();

		$found = get_posts( $args );
		return ! empty( $found ) && $found[0] instanceof \WP_Post
			? $found[0] : null;
	}

	/**
	 * Create a new post.
	 *
	 * @param string             $title     The post title.
	 * @param string             $content   The post content.
	 * @param integer            $author_id The author ID.
	 * @param \DateTimeImmutable $date      The post date.
	 * @param string             $status    The post status.
	 *
	 * @return \WP_Post
	 *
	 * @throws \Exception If the post cannot be created.
	 */
	public function create_post( string $title, string $content, int $author_id, \DateTimeImmutable $date, string $status = 'publish' ): \WP_Post {
		$post_id = wp_insert_post(
			array(
				'post_title'   => esc_html( $title ),
				'post_content' => $content,
				'post_author'  => absint( $author_id ),
				'post_date'    => $date->format( 'Y-m-d H:i:s' ),
				'post_type'    => esc_attr( $this->get_post_type() ),
				'post_status'  => esc_attr( $status ),
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			throw new \Exception( 'Failed to create post: ' . esc_html( $post_id->get_error_message() ) );
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof \WP_Post ) {
			throw new \Exception( 'Failed to create post: Post not found' );
		}

		return $post;
	}

	/**
	 * Update a post.
	 *
	 * @param integer            $post_id   The post ID.
	 * @param string             $title     The post title.
	 * @param string             $content   The post content.
	 * @param integer            $author_id The author ID.
	 * @param \DateTimeImmutable $date      The post date.
	 * @param string             $status    The post status.
	 *
	 * @return \WP_Post
	 */
	public function update_post( int $post_id, string $title, string $content, int $author_id, \DateTimeImmutable $date, string $status = 'publish' ): \WP_Post {
		$result = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_title'   => esc_html( $title ),
				'post_content' => $content,
				'post_author'  => absint( $author_id ),
				'post_date'    => $date->format( 'Y-m-d H:i:s' ),
				'post_type'    => esc_attr( $this->get_post_type() ),
				'post_status'  => esc_attr( $status ),
			)
		);

		/** // phpcs:ignore
		 * PHPStan only sees as int, not WP_Error.
		 *
		 * @var integer|\WP_Error $result
		 */
		if ( is_wp_error( $result ) || 0 === $result ) {
			$message = is_wp_error( $result ) ? $result->get_error_message() : 'Post not updated';
			throw new \Exception( 'Failed to update post: ' . esc_html( $message ) );
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof \WP_Post ) {
			throw new \Exception( 'Failed to update post: Post not found' );
		}

		return $post;
	}

	/**
	 * Add post meta.
	 *
	 * @param integer $post_id The post ID.
	 * @param string  $key     The meta key.
	 * @param mixed   $value   The meta value.
	 *
	 * @return void
	 */
	public function add_post_meta( int $post_id, string $key, $value ): void {
		add_post_meta( $post_id, $key, $value );
	}
}
