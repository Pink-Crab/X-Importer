<?php

declare(strict_types=1);

/**
 * Tweet model
 *
 * @package PinkCrab\X_Importer
 *
 * @since 0.1.0
 */

namespace PinkCrab\X_Importer\Tweet;

use PinkCrab\X_Importer\Tweet\Entity\Link;
use PinkCrab\X_Importer\Tweet\Entity\Media;
use PinkCrab\X_Importer\Tweet\Entity\Mention;

/**
 * Tweet Model.
 */
class Tweet {

	/**
	 * Holds the tweet ID.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Reply to tweet ID.
	 *
	 * @var string
	 */
	protected $reply_to;

	/**
	 * Reply to user name.
	 *
	 * @var string
	 */
	protected $reply_to_user;

	/**
	 * Holds the tweet content.
	 *
	 * @var string
	 */
	protected $content;

	/**
	 * Holds the tweet date.
	 *
	 * @var string
	 */
	protected $date;

	/**
	 * Holds the tweet favorites.
	 *
	 * @var int
	 */
	protected $favorites;

	/**
	 * Holds the tweet retweets.
	 *
	 * @var int
	 */
	protected $retweets;

	/**
	 * All hashtags.
	 *
	 * @var string[]
	 */
	protected $hashtags;

	/**
	 * Holds the links.
	 *
	 * @var Link[]
	 */
	protected $links;

	/**
	 * Media attached to the tweet.
	 *
	 * @var Media[]
	 */
	protected $media;

	/**
	 * Mentions in the tweet.
	 *
	 * @var Mention[]
	 */
	protected $mentions;

	/**
	 * Create a new instance of the Tweet.
	 *
	 * @param string    $id            The tweet ID.
	 * @param string    $reply_to      The reply to tweet ID.
	 * @param string    $reply_to_user The reply to user ame.
	 * @param string    $content       The tweet content.
	 * @param string    $date          The tweet date.
	 * @param integer   $favorites     The tweet favorites.
	 * @param integer   $retweets      The tweet retweets.
	 * @param string[]  $hashtags      All hashtags.
	 * @param Link[]    $links         The links.
	 * @param Media[]   $media         The media.
	 * @param Mention[] $mentions      Mentions in the tweet.
	 */
	public function __construct(
		string $id,
		string $reply_to,
		string $reply_to_user,
		string $content,
		string $date,
		int $favorites,
		int $retweets,
		array $hashtags,
		array $links,
		array $media,
		array $mentions
	) {
		$this->id            = esc_attr( $id );
		$this->reply_to      = esc_attr( $reply_to );
		$this->reply_to_user = esc_attr( $reply_to_user );
		$this->content       = $content;
		$this->date          = esc_attr( $date );
		$this->favorites     = absint( $favorites );
		$this->retweets      = absint( $retweets );
		$this->hashtags      = array_values( array_filter( array_map( 'esc_html', $hashtags ) ) );
		$this->links         = array_values( array_filter( $links, fn( $e ) => $e instanceof Link ) );       // @phpstan-ignore-line ,This might not be an array of Link objects.
		$this->media         = array_values( array_filter( $media, fn( $e ) => $e instanceof Media ) );      // @phpstan-ignore-line ,This might not be an array of Media objects.
		$this->mentions      = array_values( array_filter( $mentions, fn( $e ) => $e instanceof Mention ) ); // @phpstan-ignore-line ,This might not be an array of Mention objects.
	}

	/**
	 * The tweet id.
	 *
	 * @return string
	 */
	public function id(): string {
		return $this->id;
	}

	/**
	 * The reply to tweet id.
	 *
	 * @return string
	 */
	public function reply_to(): string {
		return $this->reply_to;
	}

	/**
	 * The reply to user name.
	 *
	 * @return string
	 */
	public function reply_to_user(): string {
		return $this->reply_to_user;
	}

	/**
	 * The tweet content.
	 *
	 * @return string
	 */
	public function content(): string {
		return $this->content;
	}

	/**
	 * The tweet date.
	 *
	 * @return string
	 */
	public function date(): string {
		return $this->date;
	}

	/**
	 * The tweet favorites.
	 *
	 * @return integer
	 */
	public function favorites(): int {
		return $this->favorites;
	}

	/**
	 * The tweet retweets.
	 *
	 * @return integer
	 */
	public function retweets(): int {
		return $this->retweets;
	}

	/**
	 * The Links.
	 *
	 * @return Link[]
	 */
	public function links(): array {
		return $this->links;
	}

	/**
	 * The media.
	 *
	 * @return Media[]
	 */
	public function media(): array {
		return $this->media;
	}

	/**
	 * All hashtags.
	 *
	 * @return string[]
	 */
	public function hashtags(): array {
		return $this->hashtags;
	}

	/**
	 * Mentions in the tweet.
	 *
	 * @return Mention[]
	 */
	public function mentions(): array {
		return $this->mentions;
	}
}
