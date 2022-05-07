<?php
declare(strict_types=1);

namespace Ricardoboss\WebhookTweeter\Example;

use Ricardoboss\WebhookTweeter\WebhookTweeterTemplate;
use Ricardoboss\WebhookTweeter\WebhookTweeterTemplateLocator;

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
		return new SimpleWebhookTweeterTemplate($this->templatesDirectory . '/default' . $this->templateExtension);
	}
}
