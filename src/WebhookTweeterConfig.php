<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTweeter;

use Stringable;

class WebhookTweeterConfig {
	public function __construct(
		public readonly string|Stringable|null $webhookPath = null,
		public readonly string|Stringable|null $webhookSecret = null,
	) {}
}
