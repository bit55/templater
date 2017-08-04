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

class TemplateRenderer implements TemplateRendererInterface
{
    protected $filesExtension = '.php';
    protected $defaultDirectory = 'templates';
    protected $namespaces = [];
    
    protected $container  = null;
    
    protected $sections   = [];
    protected $layout     = null;
    protected $layoutData = [];
    
    protected $sectionName = null; // current section capturing;
    protected $appendSection  = false;
    protected $prependSection = false;
    
    /**
     * Constuctor with setting options.
     *
     * @param  array|Traversable $options
     */
    public function __construct($options = null, $container = null)
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
            throw new InvalidArgumentException(sprintf(
                '"%s" expects an array or Traversable; received "%s"',
                __METHOD__,
                (is_object($options) ? get_class($options) : gettype($options))
            ));
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
     *
     * @param string $ext
     */
    public function setFilesExtension(string $ext = '.php')
    {
        $this->filesExtension = $ext;
    }
    
    
    public function setDefaultDirectory(string $dir = '')
    {
        $this->defaultDirectory = $dir;
    }
    
    public function setNamespaces(array $namespaces)
    {
        $this->namespaces = $namespaces; ////
    }

    /**
     * Getting template path by template name.
     *
     * @param string $name
     * @return string
     * @throws LogicException
     */
    private function getTemplatePath($name)
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
     * @param string $view
     * @param array $data
     * @return string
     */
    public function render($name, array $data = [])
    {
        $content = $this->renderPartial($name, $data);

        if (isset($this->layout)) {
            $this->sections['content'] = $content;
            $content = $this->renderPartial($this->layout, $this->layoutData);
        }

        return $content;
    }

    /**
     * Render single template (without layout).
     *
     * @param string $view
     * @param array $data
     * @return string
     * @throws Exception
     */
    public function renderPartial($name, array $data = [])
    {
        $templatePath = $this->getTemplatePath($name);

        // Use special variable names here to avoid conflict when extracting data
        if (is_array($data)) {
            extract($data, EXTR_PREFIX_SAME, 'data_');
        }

        ob_start();
        require $templatePath;
        return ob_get_clean();
    }
    
    /**
     * Output a rendered template. Alias of self::renderPartial() method.
     * @param  string $name
     * @param  array  $data
     * @return null
     */
    public function insert($name, array $data = [])
    {
        echo $this->renderPartial($name, $data);
    }

    /**
     * Set layout template.
     *
     * @param  string $name
     * @param  array  $data
     * @return null
     */
    public function layout($name, array $data = [])
    {
        $this->layout = $name;
        $this->layoutData = $data;
    }

    /**
     * Returns the content for a section block.
     *
     * @param  string      $name    Section name
     * @param  string      $default Default section content
     * @return string|null
     */
    public function section($name, $default = null)
    {
        if (!isset($this->sections[$name])) {
            return $default;
        }

        return $this->sections[$name];
    }

    /**
     * Start a new section block.
     * @param  string  $name
     * @return null
     */
    public function start($name)
    {
        if ($name === 'content') {
            throw new LogicException(
            'The section name "content" is reserved.'
            );
        }

        if ($this->sectionName) {
            throw new LogicException('You cannot nest sections within other sections.');
        }

        $this->sectionName = $name;

        ob_start();
    }

    /**
     * Start a new append section block.
     * @param  string $name
     * @return null
     */
    public function append($name)
    {
        $this->appendSection = true;

        $this->start($name);
    }
    
    /**
     * Start a new prepend section block.
     * @param  string $name
     * @return null
     */
    public function prepend($name)
    {
        $this->prependSection = true;

        $this->start($name);
    }

    /**
     * Stop the current section block.
     * @return null
     */
    public function stop()
    {
        if (is_null($this->sectionName)) {
            throw new LogicException(
                'You must start a section before you can stop it.'
            );
        }

        $content = ob_get_clean();

        if ($this->appendSection && isset($this->sections[$this->sectionName])) {
            $this->sections[$this->sectionName] = $this->sections[$this->sectionName] .ob_get_clean();
        } elseif ($this->prependSection && isset($this->sections[$this->sectionName])) {
            $this->sections[$this->sectionName] = ob_get_clean() . $this->sections[$this->sectionName];
        } else {
            $this->sections[$this->sectionName] = $content;
        }

        $this->sectionName = null;
        $this->appendSection  = false;
        $this->prependSection = false;
    }

    /**
     * Apply multiple functions to variable.
     * @param  mixed  $var
     * @param  string $functions
     * @return mixed
     */
    public function batch($var, $functions)
    {
        foreach (explode('|', $functions) as $function) {
            if (method_exists($this, $function)) {
                $var = call_user_func(array($this, $function), $var);
            } elseif (is_callable($function)) {
                $var = call_user_func($function, $var);
            } else {
                throw new Exception(
                'The batch function could not find the "' . $function . '" function.'
                );
            }
        }

        return $var;
    }

    /**
     * Escape string.
     * @param  string      $string
     * @param  null|string $functions
     * @return string
     */
    public function escape($string, $functions = null)
    {
        static $flags;

        if (!isset($flags)) {
            $flags = ENT_QUOTES | ENT_SUBSTITUTE;
        }

        if ($functions) {
            $string = $this->batch($string, $functions);
        }

        return htmlspecialchars($string, $flags, 'UTF-8');
    }

    /**
     * Alias to escape function.
     * @param  string      $string
     * @param  null|string $functions
     * @return string
     */
    public function e($string, $functions = null)
    {
        return $this->escape($string, $functions);
    }
    

    /**
     *
     * @param string $widgetClass
     * @param array $options
     * @return string
     */
    protected function widget($widgetClass, $options = [])
    {
        return (new $widgetClass($this->container, $options))->run();
    }
}
