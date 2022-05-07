<?php
declare(strict_types=1);

namespace Ricardoboss\WebhookTweeter;

interface WebhookTweeterTemplate
{
	public function getContents(): string;
}
