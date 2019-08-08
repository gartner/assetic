<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2014 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Exception\FilterException;
use Assetic\Util\FilesystemUtils;

/**
 * Runs assets through jpegtran.
 *
 * @link http://jpegclub.org/jpegtran/
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
class JpegtranFilter extends BaseProcessFilter
{
    const COPY_NONE = 'none';
    const COPY_COMMENTS = 'comments';
    const COPY_ALL = 'all';

    private $jpegtranBin;
    private $optimize;
    private $copy;
    private $progressive;
    private $restart;

    /**
     * Constructor.
     *
     * @param string $jpegtranBin Path to the jpegtran binary
     */
    public function __construct($jpegtranBin = '/usr/bin/jpegtran')
    {
        $this->jpegtranBin = $jpegtranBin;
    }

    public function setOptimize($optimize)
    {
        $this->optimize = $optimize;
    }

    public function setCopy($copy)
    {
        $this->copy = $copy;
    }

    public function setProgressive($progressive)
    {
        $this->progressive = $progressive;
    }

    public function setRestart($restart)
    {
        $this->restart = $restart;
    }

    public function filterLoad(AssetInterface $asset)
    {
    }

    public function filterDump(AssetInterface $asset)
    {
        $args[] = $this->jpegtranBin;

        if ($this->optimize) {
            $args[] = '-optimize';
        }

        if ($this->copy) {
            $args[] = '-copy';
            $args[] = $this->copy;
        }

        if ($this->progressive) {
            $args[] = '-progressive';
        }

        if (null !== $this->restart) {
            $args[] = '-restart';
            $args[] = $this->restart;
        }

        $args[] = $input = FilesystemUtils::createTemporaryFile('jpegtran');
        file_put_contents($input, $asset->getContent());

        $process = $this->createProcessBuilder($args);
        $code = $process->run();
        unlink($input);

        if (0 !== $code) {
            throw FilterException::fromProcess($process)->setInput($asset->getContent());
        }

        $asset->setContent($process->getOutput());
    }
}
