<?php

namespace Formwork\Utils\Interpolator\Nodes;

class ArgumentsNode extends AbstractNode
{
    /**
     * @inheritdoc
     */
    public const TYPE = 'arguments';

    public function __construct(array $value)
    {
        $this->value = $value;
    }
}
