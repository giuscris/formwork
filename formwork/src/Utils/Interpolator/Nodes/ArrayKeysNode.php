<?php

namespace Formwork\Utils\Interpolator\Nodes;

class ArrayKeysNode extends AbstractNode
{
    /**
     * @inheritdoc
     */
    public const TYPE = 'array keys';

    public function __construct(array $value)
    {
        $this->value = $value;
    }
}
