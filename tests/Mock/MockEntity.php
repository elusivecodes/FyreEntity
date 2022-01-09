<?php
declare(strict_types=1);

namespace Tests\Mock;

use
    Fyre\Entity\Entity;

use function
    floor,
    number_format;

class MockEntity extends Entity
{

    protected function _getDecimal($value)
    {
        return number_format($value ?? 0, 2);
    }

    protected function _getNumber()
    {
        return $this->get('integer');
    }

    protected function _setInteger($value)
    {
        return floor($value);
    }

}
