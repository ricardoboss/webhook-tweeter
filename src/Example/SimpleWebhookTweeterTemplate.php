<?php
declare(strict_types=1);

namespace Ricardoboss\WebhookTweeter\Example;

use Ricardoboss\WebhookTweeter\WebhookTweeterTemplate;

class SimpleWebhookTweeterTemplate implements WebhookTweeterTemplate
{
	public function __construct(private readonly string $templateFile)
	{
	}

	public function getContents(): string
	{
		return file_get_contents($this->templateFile);
	}
}
