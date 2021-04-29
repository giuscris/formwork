<?php

namespace Formwork\Utils\Interpolator\Nodes;

abstract class AbstractNode
{
    /**
     * Node type
     *
     * @var string
     */
    public const TYPE = 'node';

    /**
     * Node value
     */
    protected $value;

    /**
     * Get node type
     */
    public function type(): string
    {
        return static::TYPE;
    }

    /**
     * Get node value
     */
    public function value()
    {
        return $this->value;
    }

    public function __toString()
    {
        return 'node of type ' . static::TYPE;
    }
}
