<?php
/**
 * @link      https://github.com/bit55/templater
 * @copyright Copyright (c) 2017 Eugene Dementyev.
 * @license   https://opensource.org/licenses/BSD-3-Clause
 */

namespace App\Template;

use InvalidArgumentException;
use Traversable;
use Psr\Container\ContainerInterface;
use Bit55\Templater\TemplateRendererInterface;
use Twig_Loader_Filesystem;
use Twig_Environment;

class TwigTemplate implements TemplateRendererInterface
{
    protected $twigLoader;
    protected $twig;
    
    /**
     * Constuctor
     *
     * @param TemplateManager $manager
     */
    public function __construct(TemplateManager $manager)
    {
        $this->manager = $manager;
        
        $this->twigLoader = new Twig_Loader_Filesystem($this->manager->defaultDirectory);
        $this->twig = new Twig_Environment(
            $this->twigLoader, array(
            'cache' => 'data/cache/twig', //@todo move to config
            )
        );
    }
    
    /**
     * Render template.
     *
     * @param  string $name
     * @param  array  $data
     * @return string
     */
    public function render($name, array $data = [])
    {
        
        //$templatePath = $this->manager->pathByName($name);
        $template = $this->twig->load($name);
        return $template->render($data);
    }
}
