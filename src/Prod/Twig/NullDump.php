<?php
namespace App\Prod\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;
use Twig\TwigFunction;

class NullDump extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('dump', [$this, 'dump']),
        ];
    }

    public static function dump()
    {
        //NoOp - should log :/
    }

    public function getTokenParsers(): array
    {
        return [
            new class extends AbstractTokenParser
            {
                public function parse(Token $token): Node
                {
                    if (!$this->parser->getStream()->test(Token::BLOCK_END_TYPE)) {
                        $this->parser->getExpressionParser()->parseMultitargetExpression();
                    }
                    $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
                    return new Node();
                }
                public function getTag(): string
                {
                    return 'dump';
                }
            }
        ];
    }
}