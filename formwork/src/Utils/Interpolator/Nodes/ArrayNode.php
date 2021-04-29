<?php

namespace Formwork\Utils\Interpolator\Nodes;

class ArrayNode extends AbstractNode
{
    /**
     * @inheritdoc
     */
    public const TYPE = 'array';

    protected $keys;

    public function __construct(array $value, ArrayKeysNode $keys)
    {
        $this->value = $value;
        $this->keys = $keys;
    }

    public function keys(): ArrayKeysNode
    {
        return $this->keys;
    }
}
