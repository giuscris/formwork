<?php

namespace Formwork\Utils\Interpolator\Nodes;

class ConstantNode extends AbstractNode
{
    /**
     * @inheritdoc
     */
    public const TYPE = 'constant';

    public function __construct($value)
    {
        $this->value = $value;
    }
}
