<?php

namespace Formwork\Utils\Interpolator;

use Formwork\Utils\Str;

class Token
{
    /**
     * Identifier token type
     *
     * @var string
     */
    public const TYPE_IDENTIFIER = 'identifier';

    /**
     * Number token type
     *
     * @var string
     */
    public const TYPE_NUMBER = 'number';

    /**
     * String token type
     *
     * @var string
     */
    public const TYPE_STRING = 'string';

    /**
     * Punctuation token type
     *
     * @var string
     */
    public const TYPE_PUNCTUATION = 'punctuation';

    /**
     * Arrow token type
     *
     * @var string
     */
    public const TYPE_ARROW = 'arrow';

    /**
     * End token type
     *
     * @var string
     */
    public const TYPE_END = 'end';

    /**
     * Token type
     *
     * @var string
     */
    protected $type;

    /**
     * Token value
     *
     * @var string | null
     */
    protected $value;

    /**
     * Token position
     *
     * @var int
     */
    protected $position;

    public function __construct(string $type, ?string $value, int $position)
    {
        $this->type = $type;
        $this->value = $value;
        $this->position = $position;
    }

    /**
     * Get token type
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Get token value
     */
    public function value(): ?string
    {
        return $this->value;
    }

    /**
     * Get token position
     */
    public function position(): int
    {
        return $this->position;
    }

    /**
     * Test if token matches the given type and value
     */
    public function test(string $type, string $value = null): bool
    {
        if (func_num_args() < 2) {
            return $this->type === $type;
        }
        return $this->type === $type && $this->value === $value;
    }

    public function __toString()
    {
        return sprintf(
            'token%s of type %s',
            $this->value === null ? '' : ' ' . Str::wrap($this->value, '"'),
            $this->type
        );
    }
}
