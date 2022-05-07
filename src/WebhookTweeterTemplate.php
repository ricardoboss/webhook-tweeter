<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTweeter;

interface WebhookTweeterTemplate
{
	public function getContents(): string;
}
