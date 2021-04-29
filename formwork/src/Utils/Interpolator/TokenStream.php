<?php

namespace Formwork\Utils\Interpolator;

use Formwork\Utils\Interpolator\Errors\SyntaxError;

class TokenStream
{
    /**
     * Array containing tokens
     *
     * @var array
     */
    protected $tokens;

    /**
     * Pointer to the current token
     *
     * @var int
     */
    protected $pointer = 0;

    /**
     * Token count
     *
     * @var int
     */
    protected $count = 0;

    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
        $this->count = count($tokens);
    }

    /**
     * Get the current token
     */
    public function current(): Token
    {
        return $this->tokens[$this->pointer];
    }

    /**
     * Get the current token and advance the pointer
     */
    public function consume(): Token
    {
        if (!isset($this->tokens[$this->pointer + 1])) {
            throw new SyntaxError(sprintf('Unexpected end at position %d', $this->pointer + 1));
        }
        return $this->tokens[$this->pointer++];
    }

    /**
     * Consume the current token only if it matches the given type or value. Throw a SyntaxError otherwise
     */
    public function expect(string $type, string $value = null): Token
    {
        $token = $this->current();
        $test = func_num_args() < 2 ? $token->test($type) : $token->test($type, $value);
        if (!$test) {
            $expectedToken = new Token($type, $value, $token->position());
            throw new SyntaxError(sprintf('Unexpected %s, expected %s at position %d', $token, $expectedToken, $token->position()));
        }
        return $this->consume();
    }

    /**
     * Expect the end of stream. Throw a SyntaxError otherwise
     */
    public function expectEnd(): void
    {
        $token = $this->current();
        if (!$token->test(Token::TYPE_END)) {
            throw new SyntaxError(sprintf('Unexpected %s, expected end at position %d', $token, $token->position()));
        }
    }
}
