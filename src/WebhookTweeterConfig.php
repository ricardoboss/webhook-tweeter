<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTweeter;

use Stringable;

class WebhookTweeterConfig
{
	public function __construct(
		public string|Stringable $bearerToken,
		public string|Stringable $consumerKey,
		public string|Stringable $consumerSecret,
		public string|Stringable $webhookPath,
		public string|Stringable|null $webhookSecret = null,
	)
	{
	}
}
