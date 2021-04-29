<?php

namespace Formwork\Utils\Interpolator\Nodes;

class IdentifierNode extends AbstractNode
{
    /**
     * @inheritdoc
     */
    public const TYPE = 'identifier';

    /**
     * Node used to traverse
     *
     * @var AbstractNode|null
     */
    protected $traverse;

    /**
     * Node arguments
     *
     * @var ArgumentsNode|null
     */
    protected $arguments;

    public function __construct(string $value, ?AbstractNode $traverse, ?ArgumentsNode $arguments)
    {
        $this->value = $value;
        $this->traverse = $traverse;
        $this->arguments = $arguments;
    }

    /**
     * Return the node used to traverse
     */
    public function traverse(): ?AbstractNode
    {
        return $this->traverse;
    }

    /**
     * Return node arguments
     */
    public function arguments(): ?ArgumentsNode
    {
        return $this->arguments;
    }
}
