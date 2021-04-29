<?php

namespace Formwork\Utils\Interpolator;

use Formwork\Utils\Interpolator\Errors\SyntaxError;

class Tokenizer
{
    /**
     * Regex matching identifier tokens
     *
     * @var string
     */
    protected const IDENTIFIER_REGEX = '/[A-Za-z_][A-Za-z0-9_]*/A';

    /**
     * Regex matching number tokens
     *
     * @var string
     */
    protected const NUMBER_REGEX = '/[+-]?[0-9]+(.[0-9]+)?([Ee][+-]?[0-9]+)?/A';

    /**
     * Regex matching single quote string tokens
     *
     * @var string
     */
    protected const SINGLE_QUOTE_STRING_REGEX = '/\'(?:[^\'\\\\]|\\\\.)*\'/A';

    /**
     * Regex matching double quote string tokens
     *
     * @var string
     */
    protected const DOUBLE_QUOTE_STRING_REGEX = '/"(?:[^"\\\\]|\\\\.)*"/A';

    /**
     * Punctuation characters
     *
     * @var string
     */
    protected const PUNCTUATION_CHARACTERS = '.,()[]';

    /**
     * Arrow sequence
     *
     * @var string
     */
    protected const ARROW_SEQUENCE = '=>';

    /**
     * Tokenizer input string
     *
     * @var string
     */
    protected $input;

    /**
     * Tokenizer input length
     *
     * @var int
     */
    protected $length = 0;

    /**
     * Current position within the input string
     *
     * @var int
     */
    protected $position = 0;

    public function __construct(string $input)
    {
        $this->input = $input;
        $this->length = strlen($input);
    }

    /**
     * Tokenize input
     */
    public function tokenize(): TokenStream
    {
        $tokens = [];

        while ($this->position < $this->length) {
            switch (true) {
                case preg_match(self::IDENTIFIER_REGEX, $this->input, $matches, null, $this->position):
                    $value = array_shift($matches);
                    $tokens[] = new Token(Token::TYPE_IDENTIFIER, $value, $this->position);
                    $this->position += strlen($value);
                    break;

                case preg_match(self::NUMBER_REGEX, $this->input, $matches, null, $this->position):
                    $value = array_shift($matches);
                    $tokens[] = new Token(Token::TYPE_NUMBER, $value, $this->position);
                    $this->position += strlen($value);
                    break;

                case preg_match(self::SINGLE_QUOTE_STRING_REGEX, $this->input, $matches, null, $this->position):
                case preg_match(self::DOUBLE_QUOTE_STRING_REGEX, $this->input, $matches, null, $this->position):
                    $value = array_shift($matches);
                    $tokens[] = new Token(Token::TYPE_STRING, $value, $this->position);
                    $this->position += strlen($value);
                    break;

                case self::ARROW_SEQUENCE === substr($this->input, $this->position, 2):
                    $tokens[] = new Token(Token::TYPE_ARROW, self::ARROW_SEQUENCE, $this->position);
                    $this->position += strlen(self::ARROW_SEQUENCE);
                    break;

                case strpos(self::PUNCTUATION_CHARACTERS, substr($this->input, $this->position, 1)) !== false:
                    $tokens[] = new Token(Token::TYPE_PUNCTUATION, $this->input[$this->position], $this->position);
                    $this->position++;
                    break;

                case ctype_space(substr($this->input, $this->position, 1)):
                    $this->position++;
                    break;

                default:
                    throw new SyntaxError(sprintf('Unexpected character "%s" at position %d', $this->input[$this->position], $this->position));
            }
        }

        $tokens[] = new Token(Token::TYPE_END, null, $this->position);

        return new TokenStream($tokens);
    }

    /**
     * Tokenize a string
     */
    public static function tokenizeString(string $string): TokenStream
    {
        $tokenizer = new static($string);
        return $tokenizer->tokenize();
    }
}
