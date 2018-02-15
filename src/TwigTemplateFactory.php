<?php

namespace App\Template;

use Psr\Container\ContainerInterface;

class TwigTemplateFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
    
        $templates = new TwigTemplate($config['templates']?:[], $container);
        
        return $templates;
    }
}
