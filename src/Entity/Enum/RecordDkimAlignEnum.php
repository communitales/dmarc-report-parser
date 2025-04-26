<?php

/**
 * @copyright Copyright (c) 2025 Communitales GmbH (https://www.communitales.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Enum;

enum RecordDkimAlignEnum: string
{
    case Fail = 'fail';
    case Pass = 'pass';
    case Unknown = 'unknown';
}
