<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTweeter;

class WebhookTweeterResult
{
	public static function success(?string $url, ?object $tweet): self {
		return new WebhookTweeterResult(true, null, $url, $tweet);
	}

	public static function failure(?string $message): self {
		return new WebhookTweeterResult(false, $message, null, null);
	}

	public function __construct(
		public readonly bool $success,
		public readonly ?string $message,
		public readonly ?string $url,
		public readonly ?object $tweet,
	)
	{
	}
}
