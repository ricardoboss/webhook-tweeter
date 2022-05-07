<?php
declare(strict_types=1);

namespace Ricardoboss\WebhookTweeter;

interface WebhookTweeterTemplateLocator
{
	public function getMatchingTemplate(string $type): ?WebhookTweeterTemplate;

	public function getDefaultTemplate(): WebhookTweeterTemplate;
}
