<?php
/**
 * @link      https://github.com/bit55/templater
 * @copyright Copyright (c) 2017 Eugene Dementyev.
 * @license   https://opensource.org/licenses/BSD-3-Clause
 */

namespace Bit55\Templater;

interface WidgetInterface
{
    
    /**
     * Set widget options.
     *
     * @param  array $options
     * @return self
     */
    public function setOptions(array $options = []);
    
    /**
     * Run widget processing.
     *
     * @param  string $name
     * @param  array  $data
     * @return string
     */
    public function run();
}
