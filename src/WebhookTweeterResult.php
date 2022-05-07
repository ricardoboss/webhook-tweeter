<?php
declare(strict_types=1);

namespace Ricardoboss\WebhookTweeter;

class WebhookTweeterResult
{
	public function __construct(
		public readonly bool $success,
		public readonly ?string $message,
		public readonly ?string $url,
		public readonly ?object $tweet,
	)
	{
	}
}
