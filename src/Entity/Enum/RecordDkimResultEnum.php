<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2025 - 2026 Communitales GmbH (https://www.communitales.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Enum;

enum RecordDkimResultEnum: string
{
    case None = 'none';
    case Pass = 'pass';
    case Fail = 'fail';
    case Neutral = 'neutral';
    case Policy = 'policy';
    case TempError = 'temperror';
    case PermError = 'permerror';
}
