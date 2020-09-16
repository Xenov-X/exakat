<?php declare(strict_types = 1);
/*
 * Copyright 2012-2019 Damien Seguy � Exakat SAS <contact(at)exakat.io>
 * This file is part of Exakat.
 *
 * Exakat is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exakat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Exakat.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The latest code can be found at <http://exakat.io/>.
 *
*/

namespace Exakat\Reports;


class Stubs extends Reports {
    const FILE_EXTENSION = 'php';
    const FILE_FILENAME  = 'stubs';

    const INDENTATION = '    ';

    public function _generate(array $analyzerList): string {
        $report = new StubsJson();

        $code = json_decode( $report->_generate(array()));
        $headers = $code->headers;

        $phpHeaders = <<<HEADERS
/**
  * Generated by Exakat {$headers->exakat_version} (Build {$headers->exakat_build})
  * On {$headers->generation}
  * See https://www.exakat.io/
  */

HEADERS;

        $result = array();
        foreach($code as $key => $version) {
            if ($key === 'headers') {
                continue;
            }

            foreach($version as $name => $namespace) {
                $result[] = $this->namespace($name, (object) $namespace);
            }
        }

        $return =  "<?php\n" . $phpHeaders . "\n" . implode(PHP_EOL, $result) . "\n?>\n";

        return $return;
    }

    private function namespace(string $name, object $namespace): string {
        $result = array('namespace ' . trim($name, '\\') . ' {');

        if (!empty($namespace->constants)) {
            foreach($namespace->constants as $constantName => $constant) {
                $result[] = $this->constant($constantName, $constant);
            }
            $result[] = '';
        }

        if (!empty($namespace->functions)) {
            foreach($namespace->functions as $functionName => $function) {
                $result[] = $this->function($functionName, $function, 'function');
            }
            $result[] = '';
        }

        if (!empty($namespace->class)) {
            foreach($namespace->class as $className => $class) {
                $result[] = $this->class($className, $class);
            }
            $result[] = '';
        }

        if (!empty($namespace->interface)) {
            foreach($namespace->interface as $interfaceName => $interface) {
                $result[] = $this->interface($interfaceName, $interface);
            }
            $result[] = '';
        }

        if (!empty($namespace->trait)) {
            foreach($namespace->trait as $traitName => $trait) {
                $result[] = $this->trait($traitName, $trait);
            }
            $result[] = '';
        }

        $last = array_pop($result);
        if (empty($result)) {
            return '';
        }

        $result[] = "}\n";

        return join(PHP_EOL, $result);
    }

    private function class(string $name, object $class): string {
        $final      = empty($class->final) ? '' : 'final ';
        $abstract   = empty($class->abstract) ? '' : 'abstract ';
        $implements = empty($class->implements) ? '' : ' implements ' . implode(', ', $class->implements);
        $extends    = empty($class->extends) ? '' : ' extends ' . $class->extends;
        $phpdoc     = $this->normalizePhpdoc($class->phpdoc, 1);
        $attributes = $this->normalizeAttributes($class->attributes, 1);

        if (empty($class->use)) {
            $use = '';
        } else {
            $use        = PHP_EOL . self::INDENTATION . 'use ' . implode(', ', $class->use);
            if (empty($trait->useoptions)) {
                $use .= ';' . PHP_EOL;
            } else {
                $use .= '{' . join('; ', $class->useoptions) . '}' . PHP_EOL;
            }
        }
        $result = array(self::INDENTATION . $abstract . $final . 'class ' . $name . $extends . $implements . ' {' . $use);

        if (isset($class->constants)) {
            foreach($class->constants as $constantName => $constant) {
                $result[] = $this->constant($constantName, $constant, 'class');
            }
            $result[] = '';
        }

        if (isset($class->properties)) {
            foreach($class->properties as $propertyName => $property) {
                $result[] = $this->property($propertyName, $property);
            }
            $result[] = '';
        }

        if (isset($class->methods)) {
            foreach($class->methods as $functionName => $function) {
                $result[] = $this->function($functionName, $function);
            }
        }

        if (count($result) === 1) {
            $result[0] .= "}\n";
        } else {
            $result[] = self::INDENTATION . "}\n";
        }

        if ($class->php === true) {
            return $attributes . $phpdoc . self::INDENTATION . "if (!class_exists('\\" . $name . "')) {\n" . self::INDENTATION . str_replace("\n", "\n" . self::INDENTATION, join(PHP_EOL, $result)) . '}';
        } else {
            return $attributes . $phpdoc . join(PHP_EOL, $result);
        }
    }

    private function trait(string $name, object $trait): string {
        $phpdoc     = $this->normalizePhpdoc($trait->phpdoc, 1);
        $attributes = $this->normalizeAttributes($trait->attributes, 1);

        if (empty($trait->use)) {
            $use = '';
        } else {
            $use        = PHP_EOL . self::INDENTATION . 'use ' . implode(', ', $trait->use);
            if (empty($trait->useoptions)) {
                $use .= ';' . PHP_EOL;
            } else {
                $use .= '{' . join('; ', $trait->useoptions) . '}' . PHP_EOL;
            }
        }

        $result = array(self::INDENTATION . 'trait ' . $name . ' {' . $use);

        if (isset($trait->properties)) {
            foreach($trait->properties as $propertyName => $property) {
                $result[] = $this->property($propertyName, $property);
            }
        }

        if (isset($trait->methods)) {
            foreach($trait->methods as $functionName => $function) {
                $result[] = $this->function($functionName, $function);
            }
        }

        if (count($result) === 1) {
            $result[0] .= "}\n";
        } else {
            $result[] = self::INDENTATION . "}\n";
        }

        if ($trait->php === true) {
            return $attributes . $phpdoc . self::INDENTATION . "if (!trait_exists('\\$name')) {\n" . self::INDENTATION . str_replace("\n", "\n" . self::INDENTATION, join(PHP_EOL, $result)) . '}';
        } else {
            return $attributes . $phpdoc . join(PHP_EOL, $result);
        }
    }

    private function interface(string $name, object $interface): string {
        $phpdoc     = $this->normalizePhpdoc($interface->phpdoc, 1);
        $attributes = $this->normalizeAttributes($interface->attributes, 1);

        $extends    = empty($interface->extends) ? '' : ' extends ' . $interface->extends;
        $result = array(self::INDENTATION . "interface $name{$extends} {");

        if (isset($interface->constants)) {
            foreach($interface->constants as $constantName => $constant) {
                $result[] = $this->constant($constantName, $constant, 'interface');
            }
        }

        if (isset($interface->methods)) {
            foreach($interface->methods as $functionName => $function) {
                $result[] = $this->function($functionName, $function, 'interface');
            }
        }

        if (count($result) === 1) {
            $result[0] .= "}\n";
        } else {
            $result[] = self::INDENTATION . "}\n";
        }

        if ($interface->php === true) {
            return $attributes . $phpdoc . self::INDENTATION . "if (!interface_exists('\\$name')) {\n" . self::INDENTATION . str_replace("\n", "\n" . self::INDENTATION, join(PHP_EOL, $result)) . '}';
        } else {
            return $attributes . $phpdoc . join(PHP_EOL, $result);
        }
    }

    private function constant(string $name, object $values, $type = 'global'): string {
        $phpdoc     = $this->normalizePhpdoc($values->phpdoc, $type === 'global' ? 1 : 2);
        $attributes = $this->normalizeAttributes($values->attributes ?? array(), $type === 'global' ? 1 : 2);

        $visibility = empty($values->visibility) ? '' : $values->visibility . ' ';
        if (isset($values->type) && $values->type == 'define') {
            return $$phpdoc . self::INDENTATION . ($type === 'global' ? '' : self::INDENTATION) . "define('" . $name . "', " . $values->value . ');';
        } else {
            return $attributes . $phpdoc . self::INDENTATION . ($type === 'global' ? '' : self::INDENTATION) . $visibility . 'const ' . $name . ' = ' . $values->value . ';';
        }
    }

    private function property(string $name, object $values): string {
        $static     = empty($values->static) ? '' : 'static ';
        $typehint   = implode('|', $values->typehint);
        $phpdoc     = $this->normalizePhpdoc($values->phpdoc, 2);
        $attributes = $this->normalizeAttributes($values->attributes, 2);
        $visibility = ($values->visibility ?: 'public') . ' ';

        return $attributes . $phpdoc . self::INDENTATION . self::INDENTATION . $static . $visibility . $typehint . $name . ';';
    }

    private function function(string $name, object $values, string $type = 'class'): string {
        $reference  = empty($values->reference) ? '' : '&';
        if ($type === 'interface') {
            $visibility = '';
            $abstract   = '';
            $block      = ';';
        } else {
            $abstract   = empty($values->abstract) ? '' : 'abstract ';
            $visibility = empty($values->visibility) ? '' : $values->visibility . ' ';
            $block      = empty($values->abstract) ? '{}' : ';';
        }

        $arguments = array();
        if (isset($values->arguments)) {
            foreach($values->arguments as $argDetails) {
                $referenceArgs  = empty($values->referenceArgs) ? '' : ' &';
                $typehintArgs   = $this->formatTypehints($argDetails->typehint);
                $typehintArgs   = $typehintArgs === '' ? '' : $typehintArgs . ' ';
                $default        = $argDetails->value === '' ? '' : ' = ' . $argDetails->value;
                $phpdoc         = $this->normalizePhpdoc($argDetails->phpdoc);
                $attributes     = implode(' ', $argDetails->attributes);

                $arguments[] = $attributes . $phpdoc . $typehintArgs . $referenceArgs . $argDetails->name . $default;
            }
        }
        $arguments = implode(', ', $arguments);

        $static     = empty($values->static) ? '' : 'static ';
        $final      = empty($values->final) ? '' : 'final ';
        $typehint   = $this->formatTypehints($values->returntypes);
        $typehint   = $typehint === '' ? '' : ': ' . $typehint . ' ';
        $phpdoc     = $this->normalizePhpdoc($values->phpdoc, $type === 'function' ? 1 : 2);
        $attributes = $this->normalizeAttributes($values->attributes, $type === 'function' ? 1 : 2);

        $return = $attributes . $phpdoc . self::INDENTATION . ($type === 'function' ? '' : self::INDENTATION) . "{$final}{$abstract}{$visibility}{$static}function {$reference}$name($arguments) $typehint{$block}";

        if ($type === 'function' && $values->php === true) {
            $return = self::INDENTATION . "if (!function_exists('" . $name . "')) {\n" . self::INDENTATION . $return . "\n" . self::INDENTATION . "}\n";
        }

        return $return;
    }

    private function formatTypehints(array $typehints): string {
        if (empty($typehints)) {
            return '';
        }

        $id = array_search('?', $typehints);
        if ($id !== false) {
            unset($typehints[$id]);
            return '?' . implode('|', $typehints);
        }

        return implode('|', $typehints);
    }

    private function normalizePhpdoc(string $phpdoc, int $level = 0): string {
        if (empty($phpdoc)) {
            return '';
        }

        return str_repeat(self::INDENTATION, $level) . preg_replace("/\n\s+\*/m", "\n" . str_repeat(self::INDENTATION, $level) . ' *', $phpdoc) . PHP_EOL;
    }

    private function normalizeAttributes(array $attributes, int $level = 0): string {
        if (empty($attributes)) {
            return '';
        }

        return str_repeat(self::INDENTATION, $level) . implode( PHP_EOL . str_repeat(self::INDENTATION, $level), $attributes) . PHP_EOL;
    }
}

?>