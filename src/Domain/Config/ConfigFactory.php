<?php

/**
 * @copyright Copyright (c) 2025 Communitales GmbH (https://www.communitales.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Domain\Config;

use App\Domain\Config\Model\ImportConfig;

/**
 * Class ConfigFactory
 */
readonly class ConfigFactory
{
    public function __construct(
        private string $imapReadFolder,
        private string $imapMoveFolder
    ) {
    }

    public function createConfig(): ImportConfig
    {
        return new ImportConfig(
            $this->imapReadFolder,
            $this->imapMoveFolder,
        );
    }
}
