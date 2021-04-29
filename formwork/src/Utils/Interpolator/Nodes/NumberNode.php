<?php

namespace Formwork\Utils\Interpolator\Nodes;

class NumberNode extends AbstractNode
{
    /**
     * @inheritdoc
     */
    public const TYPE = 'number';

    /**
     * @param float|int $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }
}
