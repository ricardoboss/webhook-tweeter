<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTweeter\API;

use Coderjerk\BirdElephant\BirdElephant;
use Coderjerk\BirdElephant\Compose\Tweet;
use ricardoboss\WebhookTweeter\WebhookTweeterTwitterAPI;

class BirdElephantTwitterAPI implements WebhookTweeterTwitterAPI
{
	private array $credentials = [];

	/**
	 * @codeCoverageIgnore
	 */
	public function setCredentials(array $credentials): void
	{
		$this->credentials = $credentials;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function sendTweet(string $message): object
	{
		$twitter = new BirdElephant($this->credentials);
		$tweet = (new Tweet)->text($message);

		return $twitter->tweets()->tweet($tweet);
	}

	public function getTweetUrl(object $tweet): ?string {
		return sprintf('https://twitter.com/%s/status/%s', $tweet->user->screen_name, $tweet->data->id);
	}
}
