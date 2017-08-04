<?php
/**
 * @link      https://github.com/bit55/templater
 * @copyright Copyright (c) 2017 Eugene Dementyev.
 * @license   https://opensource.org/licenses/BSD-3-Clause
 */

namespace Bit55\Templater;

interface TemplateRendererInterface
{

    /**
     * Render a template.
     *
     * Implementations MUST support the `namespace::template` naming convention,
     * and allow omitting the filename extension.
     *
     * @param string $name
     * @param array $data
     * @return string
     */
    public function render($name, array $data = []);
}
