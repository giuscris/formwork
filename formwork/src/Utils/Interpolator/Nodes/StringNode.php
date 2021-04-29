<?php

namespace Formwork\Utils\Interpolator\Nodes;

class StringNode extends AbstractNode
{
    /**
     * @inheritdoc
     */
    public const TYPE = 'string';

    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
