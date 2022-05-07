<?php
declare(strict_types=1);

namespace Ricardoboss\WebhookTweeter;

interface WebhookTweeterRenderer {
	public function render(WebhookTweeterTemplate $template, array $data): string;
}
