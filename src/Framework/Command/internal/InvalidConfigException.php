<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisBaseliner\Framework\Command\internal;

use Exception;

/**
 * Used for invalid user config provided.
 */
class InvalidConfigException extends Exception
{
    /**
     * @var string
     */
    private $option;

    /**
     * @var string
     */
    private $error;

    /**
     * InvalidConfigException constructor.
     *
     * @param string $option
     * @param string $error
     */
    public function __construct(string $option, string $error)
    {
        $this->option = $option;
        $this->error = $error;
        parent::__construct($this->getProblem());
    }

    /**
     * Return problem with configuration.
     *
     * @return string
     */
    public function getProblem(): string
    {
        return sprintf('Invalid argument %s. %s', $this->option, $this->error);
    }
}
