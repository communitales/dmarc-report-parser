<?php

/**
 * @copyright Copyright (c) 2025 Communitales GmbH (https://www.communitales.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Enum;

/**
 * @see https://dmarc.org/dmarc-xml/0.1/rua.xsd
 */
enum RecordSpfResultEnum: string
{
    case None = 'none';
    case Pass = 'pass';
    case Fail = 'fail';
    case Softfail = 'softfail';
    case Neutral = 'neutral';
    case TempError = 'temperror';
    case PermError = 'permerror';
    case Unknown = 'unknown';
}
