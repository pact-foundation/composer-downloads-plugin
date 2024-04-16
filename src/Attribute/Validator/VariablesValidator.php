<?php

namespace LastCall\DownloadsPlugin\Attribute\Validator;

use Composer\Package\PackageInterface;
use LastCall\DownloadsPlugin\Attribute\AttributeManagerInterface;
use LastCall\DownloadsPlugin\Enum\Attribute;
use Le\SMPLang\Exception;
use Le\SMPLang\SMPLang;

class VariablesValidator extends AbstractValidator
{
    private SMPLang $smpl;

    public function __construct(
        string $id,
        PackageInterface $parent,
        private AttributeManagerInterface $attributeManager,
        ?SMPLang $smpl = null
    ) {
        parent::__construct($id, $parent);
        $this->smpl = $smpl ?? new SMPLang([
            'range' => range(...),
            'strtolower' => strtolower(...),
            'php_uname' => php_uname(...),
            'in_array' => in_array(...),
            'str_contains' => str_contains(...),
            'str_starts_with' => str_starts_with(...),
            'str_ends_with' => str_ends_with(...),
            'matches' => fn (string $pattern, string $subject) => 1 === preg_match($pattern, $subject),
            'PHP_OS' => \PHP_OS,
            'PHP_OS_FAMILY' => \PHP_OS_FAMILY,
            'PHP_SHLIB_SUFFIX' => \PHP_SHLIB_SUFFIX,
            'DIRECTORY_SEPARATOR' => \DIRECTORY_SEPARATOR,
        ]);
    }

    public function validate(mixed $values): array
    {
        $variables = [
            '{$id}' => $this->id,
            '{$version}' => $this->attributeManager->get(Attribute::VERSION) ?? '',
        ];

        if (null === $values) {
            return $variables;
        }

        if (!\is_array($values)) {
            $this->throwException($this->getAttribute(), sprintf('must be array, "%s" given', get_debug_type($values)));
        }

        if (empty($values)) {
            return $variables;
        }

        foreach ($values as $key => $value) {
            if (!preg_match('/^{\$[^}]+}$/', $key)) {
                $this->throwException($this->getAttribute(), sprintf('is invalid: Variable key "%s" should be this format "{$variable-name}"', $key));
            }
            try {
                $result = $this->smpl->evaluate($value);
            } catch (Exception $exception) {
                $this->throwException($this->getAttribute(), sprintf('is invalid. There is an error while evaluating expression "%s": %s', $value, $exception->getMessage()));
            }
            if (!\is_string($result)) {
                $this->throwException($this->getAttribute(), sprintf('is invalid: Expression "%s" should be evaluated to string, "%s" given', $value, get_debug_type($result)));
            }
            $variables[$key] = $result;
        }

        return $variables;
    }

    public function getAttribute(): string
    {
        return Attribute::VARIABLES->value;
    }
}
