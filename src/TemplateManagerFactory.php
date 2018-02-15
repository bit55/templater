<?php

namespace Bit55\Templater;

use Psr\Container\ContainerInterface;

class TemplateManagerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
    
        $templates = new TemplateManager($config['templates']?:[], $container);
        
        return $templates;
    }
}
