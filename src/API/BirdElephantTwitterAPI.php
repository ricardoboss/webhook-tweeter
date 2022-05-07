<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTweeter\API;

use Coderjerk\BirdElephant\BirdElephant;
use Coderjerk\BirdElephant\Compose\Tweet;
use ricardoboss\WebhookTweeter\WebhookTweeterTwitterAPI;

class BirdElephantTwitterAPI implements WebhookTweeterTwitterAPI
{
	private array $credentials = [];

	public function setCredentials(array $credentials): void
	{
		$this->credentials = $credentials;
	}

	public function sendTweet(string $message): object
	{
		$twitter = new BirdElephant($this->credentials);
		$tweet = (new Tweet)->text($message);

		return $twitter->tweets()->tweet($tweet);
	}
}
