<?php
namespace Crossjoin\Browscap\Updater;

/**
 * Local updater class
 *
 * This class loads the source data from a local file, which need to be set
 * via the options.
 *
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 Christoph Ziegenberg <christoph@ziegenberg.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @package Crossjoin\Browscap
 * @author Christoph Ziegenberg <christoph@ziegenberg.com>
 * @copyright Copyright (c) 2014-2015 Christoph Ziegenberg <christoph@ziegenberg.com>
 * @version 1.0.0
 * @license http://www.opensource.org/licenses/MIT MIT License
 * @link https://github.com/crossjoin/browscap
 */
class Local
extends AbstractUpdater
{
    /**
     * Name of the update method, used in the user agent for the request,
     * for browscap download statistics. Has to be overwritten by the
     * extending class.
     *
     * @var string
     */
    protected $updateMethod = 'local';

    public function __construct($options = null)
    {
        parent::__construct($options);

        // add additional options
        $this->options['LocalFile'] = null;
    }

    /**
     * Gets the current browscap version (time stamp)
     *
     * @return int
     * @throws \Exception
     */
    public function getBrowscapVersion()
    {
        $file = $this->getOption('LocalFile');
        if ($file === null) {
            throw new \Exception("Option 'LocalFile' not set.");
        }
        if (!is_readable($file)) {
            throw new \Exception("File '$file' set in option 'LocalFile' is not readable.");
        }
        return (int)filemtime($file);
    }

    /**
     * Gets the current browscap version number (if possible for the source)
     *
     * @return int|null
     */
    public function getBrowscapVersionNumber()
    {
        return null;
    }

    /**
     * Gets the browscap data of the used source type
     *
     * @return string|boolean
     * @throws \Exception
     */
    public function getBrowscapSource()
    {
        $file = $this->getOption('LocalFile');
        if ($file === null) {
            throw new \Exception("Option 'LocalFile' not set.");
        }
        if (!is_readable($file)) {
            throw new \Exception("File '$file' set in option 'LocalFile' is not readable.");
        }
        return file_get_contents($file);
    }
}