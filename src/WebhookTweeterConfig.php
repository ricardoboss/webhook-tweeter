<?php
declare(strict_types=1);

namespace Ricardoboss\WebhookTweeter;

use Stringable;

class WebhookTweeterConfig
{
	public function __construct(
		public string|Stringable $bearerToken,
		public string|Stringable $consumerKey,
		public string|Stringable $consumerSecret,
		public string|Stringable $webhookUrl,
		public string|Stringable|null $webhookSecret = null,
	)
	{
	}
}
