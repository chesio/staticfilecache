<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Generator;

use SFC\Staticfilecache\Event\GeneratorContentManipulationEvent;
use SFC\Staticfilecache\Event\GeneratorCreate;
use SFC\Staticfilecache\Event\GeneratorRemove;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\DateTimeService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PhpGenerator extends HtaccessGenerator
{
    public function generate(GeneratorCreate $generatorCreateEvent): void
    {
        if (!$this->getConfigurationService()->get('enableGeneratorPhp')) {
            return;
        }

        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        $accessTimeout = (int) $configuration->get('htaccessTimeout');
        $lifetime = $accessTimeout ?: $generatorCreateEvent->getLifetime();

        $headers = $configuration->getValidHeaders($generatorCreateEvent->getResponse()->getHeaders(), 'validHtaccessHeaders');
        if ($configuration->isBool('debugHeaders')) {
            $headers['X-SFC-State'] = 'StaticFileCache - via PhpGenerator';
        }
        $headers = array_map(fn($item) => str_replace("'", "\'", $item), $headers);
        $requestUri = GeneralUtility::getIndpEnv('REQUEST_URI');

        /** @var GeneratorContentManipulationEvent  $contentManipulationEvent */
        $contentManipulationEvent = $this->eventDispatcher->dispatch(new GeneratorContentManipulationEvent((string) $generatorCreateEvent->getResponse()->getBody()));

        $variables = [
            'expires' => (new DateTimeService())->getCurrentTime() + $lifetime,
            'sendCacheControlHeaderRedirectAfterCacheTimeout' => $configuration->isBool('sendCacheControlHeaderRedirectAfterCacheTimeout'),
            'headers' => $headers,
            'requestUri' => $requestUri,
            'body' => $contentManipulationEvent->getContent(),
        ];

        $this->renderTemplateToFile($this->getTemplateName(), $variables, $generatorCreateEvent->getFileName() . '.php');
    }

    public function remove(GeneratorRemove $generatorRemoveEvent): void
    {
        if (!$this->getConfigurationService()->get('enableGeneratorPhp')) {
            return;
        }
        $this->removeFile($generatorRemoveEvent->getFileName() . '.php');
    }

    /**
     * Get the template name.
     */
    protected function getTemplateName(): string
    {
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        $templateName = trim((string) $configuration->get('phpTemplateName'));
        if ('' === $templateName) {
            return 'EXT:staticfilecache/Resources/Private/Templates/Php.html';
        }

        return $templateName;
    }
}
