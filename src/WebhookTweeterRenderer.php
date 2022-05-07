<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTweeter;

interface WebhookTweeterRenderer {
	public function render(WebhookTweeterTemplate $template, array $data): string;
}
