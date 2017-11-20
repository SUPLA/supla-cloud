<?php
namespace SuplaApiBundle\DependencyInjection;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class IsCurrentUserExpressionProvider implements ExpressionFunctionProviderInterface {
    /**
     * @return ExpressionFunction[] An array of Function instances
     */
    public function getFunctions() {
        return [
            new ExpressionFunction('isCurrentUser', function ($user) {

            }),
        ];
    }
}
