<?php

namespace Formwork\Utils\Interpolator\Nodes;

class IdentifierNode extends AbstractNode
{
    /**
     * @inheritdoc
     */
    public const TYPE = 'identifier';

    /**
     * Node arguments
     *
     * @var ArgumentsNode|null
     */
    protected $arguments;

    /**
     * Node used to traverse
     *
     * @var AbstractNode|null
     */
    protected $traverse;

    public function __construct(string $value, ?ArgumentsNode $arguments, ?AbstractNode $traverse)
    {
        $this->value = $value;
        $this->arguments = $arguments;
        $this->traverse = $traverse;
    }

    /**
     * Return node arguments
     */
    public function arguments(): ?ArgumentsNode
    {
        return $this->arguments;
    }

    /**
     * Return the node used to traverse
     */
    public function traverse(): ?AbstractNode
    {
        return $this->traverse;
    }
}
