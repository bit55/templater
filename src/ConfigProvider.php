<?php

namespace Bit55\Templater;

/**
 * The configuration provider for the App module
 */
class ConfigProvider
{
    /**
     * Returns the configuration array.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => [
                'factories' => [
                    TemplateRendererInterface::class => TemplateManagerFactory::class,
                ]
            ],
            'templates'    => [
                'defaultDirectory'  => 'templates',
                'usePackageDirs' => false, //@todo
            ],
        ];
    }
}
