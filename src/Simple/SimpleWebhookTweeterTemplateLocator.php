<?php
declare(strict_types=1);

namespace ricardoboss\WebhookTweeter\Simple;

use ricardoboss\WebhookTweeter\WebhookTweeterTemplate;
use ricardoboss\WebhookTweeter\WebhookTweeterTemplateLocator;
use RuntimeException;

class SimpleWebhookTweeterTemplateLocator implements WebhookTweeterTemplateLocator
{
	public function __construct(
		private readonly string $templatesDirectory,
		private readonly string $templateExtension = '.md',
	)
	{
	}

	public function getMatchingTemplate(string $type): ?WebhookTweeterTemplate
	{
		$templateFile = $this->templatesDirectory . '/' . $type . $this->templateExtension;

		if (!file_exists($templateFile)) {
			return null;
		}

		return new SimpleWebhookTweeterTemplate($templateFile);
	}

	public function getDefaultTemplate(): WebhookTweeterTemplate
	{
		throw new RuntimeException('No default template available');
	}
}
