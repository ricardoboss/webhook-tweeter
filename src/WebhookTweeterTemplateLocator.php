<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTweeter;

interface WebhookTweeterTemplateLocator
{
	public function getMatchingTemplate(array $data): ?WebhookTweeterTemplate;

	public function getDefaultTemplate(): WebhookTweeterTemplate;
}
