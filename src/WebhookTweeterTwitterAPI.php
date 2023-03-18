<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTweeter;

interface WebhookTweeterTwitterAPI
{
	public function sendTweet(string $message): object;
	public function getTweetUrl(object $tweet): ?string;
}
