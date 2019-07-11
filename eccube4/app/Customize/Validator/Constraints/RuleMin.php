<?php

namespace Customize\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Rulemin
 */
class RuleMin extends Constraint
{
    public $message = 'The string "{{ string }}" contains an illegal character: it can only contain letters or numbers.';
}
