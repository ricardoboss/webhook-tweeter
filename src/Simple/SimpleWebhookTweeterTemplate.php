<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTweeter\Simple;

use ricardoboss\WebhookTweeter\WebhookTweeterTemplate;

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
