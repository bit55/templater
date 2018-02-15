<?php
/**
 * @link      https://github.com/bit55/templater
 * @copyright Copyright (c) 2017 Eugene Dementyev.
 * @license   https://opensource.org/licenses/BSD-3-Clause
 */

namespace Bit55\Templater;

use Exception;
use LogicException;
use InvalidArgumentException;
use Traversable;
use Psr\Container\ContainerInterface;

class TemplateManager implements TemplateRendererInterface
{
    protected $filesExtension = '.php';
    protected $defaultDirectory = 'templates';
    protected $namespaces = [];
    
    protected $container  = null;
    
    /**
     * Constuctor with setting options.
     *
     * @param array|Traversable $options
     */
    public function __construct($options = null, ContainerInterface $container = null)
    {
        if ($options !== null) {
            if (is_array($options) || $options  instanceof Traversable) {
                $this->setOptions($options);
            }
        }
        
        if ($container !== null) {
            $this->container = $container;
        }
    }
    
    /**
     * @param  array|Traversable $options
     * @return self
     * @throws InvalidArgumentException
     */
    public function setOptions($options)
    {
        if (! is_array($options) && ! $options instanceof Traversable) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" expects an array or Traversable; received "%s"',
                    __METHOD__,
                    (is_object($options) ? get_class($options) : gettype($options))
                )
            );
        }

        foreach ($options as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($this, $setter)) {
                $this->{$setter}($value);
            } else {
                throw new InvalidArgumentException(
                    sprintf(
                        'The option "%s" does not have a matching %s setter method or options[%s] array key',
                        $key,
                        $setter,
                        $key
                    )
                );
            }
        }
        return $this;
    }
    
    /**
     * @param string $ext
     */
    public function setUsePackageDirs(string $ext = '.php')
    {
        $this->filesExtension = $ext;
    }
    
    /**
     * @param string $ext
     */
    public function setFilesExtension(string $ext = '.php')
    {
        $this->filesExtension = $ext;
    }
    
    /**
     * @return string
     */
    public function getFilesExtension()
    {
        return $this->filesExtension;
    }
    
    /**
     * @param string $dir
     */
    public function setDefaultDirectory(string $dir = '')
    {
        $this->defaultDirectory = $dir;
    }
    
    /**
     * @return string
     */
    public function getDefaultDirectory()
    {
        return $this->defaultDirectory;
    }
    
    /**
     * @param array $namespaces
     */
    public function setNamespaces(array $namespaces)
    {
        $this->namespaces = $namespaces;
    }
    
    /**
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }
    
    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Getting template path by template name.
     *
     * @param  string $name
     * @return string
     * @throws LogicException
     */
    public function pathByName($name)
    {
        // $this->defaultDirectory . $name . $this->fileExtension
        if (strpos($name, '::') !== false) {
            $parts = explode('::', $name);
            $ns = array_shift($parts);

            if (isset($this->namespaces[$ns])) {
                $path = $this->namespaces[$ns] . '/' . join('/', $parts) . $this->filesExtension;
            } else {
                throw new LogicException(sprintf("Template namespace '%s::' not defined", $ns));
            }
        } else {
            $path = $this->defaultDirectory . '/' . $name . $this->filesExtension;
        }

        if (file_exists($path)) {
            return $path;
        }

        throw new LogicException(sprintf("Template file '%s' not found", $path));
    }
    

    /**
     * Render template with layout if defined.
     *
     * @param  string $name
     * @param  array  $data
     * @return string
     */
    public function render($name, array $data = [])
    {
        if (strpos($name, '.php')!==false) {
            return (new PhpTemplate($this))->render($name, $data);
        } else {
            return (new TwigTemplate($this))->render($name, $data);
        }
        
        throw new LogicException(sprintf("Template type '%s' not recognized", $name));
    }
}
