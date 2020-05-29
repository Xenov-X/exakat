<?php declare(strict_types = 1);
/*
 * Copyright 2012-2019 Damien Seguy – Exakat SAS <contact(at)exakat.io>
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


namespace Exakat\Query\DSL;

use Exakat\Exceptions\DSLException;
use Exakat\Tasks\Helpers\Atom;
use Exakat\GraphElements;
use Exakat\Analyzer\Analyzer;

abstract class DSL {
    const VARIABLE_WRITE = true;
    const VARIABLE_READ  = false;

    const LABEL_SET  = true;
    const LABEL_GO   = false;

    const LEVELS_TO_ANALYSE = 4;

    const PROPERTIES = array('id',
                             'atom',
                             'code',
                             'lccode',
                             'fullcode',
                             'line',
                             'token',
                             'rank',
                             'alternative',
                             'reference',
                             'heredoc',
                             'delimiter',
                             'noDelimiter',
                             'variadic',
                             'count',
                             'fullnspath',
                             'absolute',
                             'alias',
                             'origin',
                             'encoding',
                             'block',
                             'intval',
                             'strval',
                             'boolean',
                             'enclosing',
                             'args_max',
                             'args_min',
                             'bracket',
                             'flexible',
                             'close_tag',
                             'aliased',
                             'propertyname',
                             'constant',
                             'root',
                             'globalvar',
                             'binaryString',
                             'isNull',
                             'visibility',
                             'final',
                             'abstract',
                             'static',
                             'noscream',
                             'trailing',
                             );

    protected const BOOLEAN_PROPERTY = array('abstract',
                                             'absolute',
                                             'aliased',
                                             'alternative',
                                             'bracket',
                                             'constant',
                                             'enclosing',
                                             'final',
                                             'heredoc',
                                             'isModified',
                                             'isRead',
                                             'noscream',
                                             'reference',
                                             'static',
                                             'trailing',
                                             'variadic',
                                             );

    protected const INTEGER_PROPERTY = array('line',
                                             'rank',
                                             'propertyname',
                                             'boolean',
                                             'count',
                                             'code',
                                             'lccode',
                                             );

    protected $dslfactory             = null;
    protected $availableAtoms         = array();
    protected $availableLinks         = array();
    protected $availableFunctioncalls = array();
    protected $availableVariables     = array(); // This one is per query
    protected $availableLabels        = array(); // This one is per query
    protected $dictCode               = null;
    protected $ignoredcit             = null;
    protected $ignoredfunctions       = null;
    protected $ignoredconstants       = null;
    protected $dependsOn              = array();
    protected $analyzerQuoted         = '';

    protected static $linksDown     = '';
    protected static $MAX_LOOPING   = Analyzer::MAX_LOOPING;
    protected static $TIME_LIMIT    = Analyzer::TIME_LIMIT;

    public function __construct(DSLfactory $dslfactory,
                                array $availableAtoms = array(),
                                array $availableLinks,
                                array $availableFunctioncalls,
                                array &$availableVariables,
                                array &$availableLabels,
                                $ignoredcit,
                                $ignoredfunctions,
                                $ignoredconstants,
                                $dependsOn,
                                $analyzerQuoted) {
        $this->dslfactory             = $dslfactory;
        $this->dictCode               = exakat('dictionary');
        $this->availableAtoms         = $availableAtoms;
        $this->availableLinks         = $availableLinks;
        $this->availableFunctioncalls = $availableFunctioncalls;
        $this->availableVariables     = &$availableVariables;
        $this->availableLabels        = &$availableLabels;
        $this->ignoredcit             = $ignoredcit;
        $this->ignoredfunctions       = $ignoredfunctions;
        $this->ignoredconstants       = $ignoredconstants;
        $this->dependsOn              = $dependsOn;
        $this->analyzerQuoted         = $analyzerQuoted;

        if (empty(self::$linksDown)) {
            self::$linksDown = GraphElements::linksAsList();
        }
    }

    abstract public function run();

    protected function normalizeAtoms($atoms): array {
        $atoms = makeArray($atoms);
        return array_values(array_intersect($atoms, $this->availableAtoms));
    }

    protected function normalizeLinks($links): array {
        $links = makeArray($links);
        return array_values(array_intersect($links, $this->availableLinks));
    }

    protected function normalizeFunctioncalls($fullnspaths): array {
        $fullnspaths = makeArray($fullnspaths);
        return array_values(array_intersect($fullnspaths, $this->availableFunctioncalls));
    }

    protected function SorA($value) {
        if (is_array($value)) {
            return makeList($value);
        } elseif (is_string($value)) {
            return '"' . $value . '"';
        } else {
            assert(false, '$v is not a string or an array');
        }
    }

    protected function assertLabel($name, $read = self::LABEL_GO): bool {
        if (is_array($name)) {
            foreach($name as $n) {
                $this->assertLabel($n, $read);
            }
            return true;
        }

        if ($read === self::LABEL_SET) {
            assert(!in_array($name, $this->availableLabels), "Label '$name' is already set : " . join(', ', $this->availableLabels));
            $this->availableLabels[] = $name;
        } else {
            assert(in_array($name, $this->availableLabels), "Label '$name' is not set");
        }
        return true;
    }

    protected function isVariable(string $name): bool {
        return in_array($name, $this->availableVariables);
    }

    protected function assertVariable(string $name, bool $write = self::VARIABLE_READ): bool {
        if ($write === self::VARIABLE_WRITE) {
            assert(!$this->isVariable($name), "Variable '$name' is already taken : " . print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), true) . PHP_EOL . print_r($this, true));
            assert(!in_array($name, self::PROPERTIES), "Don't use a property name as a variable ($name)");
            $this->availableVariables[] = $name;
        } else {
            assert($this->isVariable($name), "Variable '$name' is not defined");
        }
        return true;
    }

    protected function assertLink($link): bool {
        if (is_string($link)) {
            if(in_array($link, array('KEY', 'ELEMENT', 'PROPERTY')) ) {
                throw new DSLException("$link is no more", self::LEVELS_TO_ANALYSE);
            }
            if($link !== strtoupper($link)) {
                throw new DSLException("Wrong format for LINK name : $link", self::LEVELS_TO_ANALYSE);
            }
            if(preg_match('/[^A-Z]/', $link)) {
                throw new DSLException("Not a link : $link", self::LEVELS_TO_ANALYSE);
            }
        } elseif (is_array($link)) {
            foreach($link as $l) {
                $this->assertLink($l);
            }
        } else {
            assert(false, 'Unsupported type for link : ' . gettype($link));
        }
        return true;
    }

    protected function assertTokens($token): bool {
        if (is_string($token)) {
            assert(substr($token, 0, 2) === 'T_', "Wrong prefix for TOKEN name : $token");
            assert($token === strtoupper($token), "Wrong format for TOKEN name : $token");
        } elseif (is_array($token)) {
            foreach($token as $t) {
                assert(substr($t, 0, 2) === 'T_', "Wrong prefix for TOKEN name : $t");
                assert($t === strtoupper($t), "Wrong format for TOKEN name : $t");
            }
        } else {
            assert(false, 'Unsupported type for token : ' . gettype($token));
        }
        return true;
    }

    protected function assertAtom($atom) : bool {
        if (is_string($atom)) {
            assert($atom === ucfirst(strtolower($atom)), "Wrong format for Atom name : $atom");
        } elseif (is_array($atom)) {
            foreach($atom as $a) {
                assert($a === ucfirst(strtolower($a)), "Wrong format for Atom name : $a");
            }
        } else {
            assert(false, 'Unsupported type for atom : ' . gettype($atom));
        }

        return true;
    }

    protected function assertAnalyzer($analyzer) : bool {
        if (is_string($analyzer)) {
            assert(preg_match('#^[A-Z]\w+/[A-Z]\w+$#', $analyzer) !== false, "Wrong format for Analyzer : $analyzer");
            assert(class_exists('\\Exakat\\Analyzer\\' . str_replace('/', '\\', $analyzer)), "No such analyzer as $analyzer");
        } elseif (is_array($analyzer)) {
            foreach($analyzer as $a) {
                assert(preg_match('#^[A-Z]\W\w+/[A-Z]\W\w+$#', $a) !== false, "Wrong format for Analyzer : $a");
                assert(class_exists('\\Exakat\\Analyzer\\' . str_replace('/', '\\', $a)), "No such analyzer as $a");
            }
        } else {
            assert(false, 'Unsupported type for analyzer : ' . gettype($analyzer));
        }

        return true;
    }

    protected function isProperty($property) : bool {
        return property_exists(Atom::class, $property) || in_array($property, array('label', 'self', 'ignored_dir', 'virtual', 'analyzer', 'propagated'));
    }

    protected function assertProperty($property) {
        if (is_string($property)) {
            assert( ($property === mb_strtolower($property)) || in_array($property, array('noDelimiter', 'isRead', 'isModified')) , 'Wrong format for property name : "' . $property . '"');
            assert($this->isProperty($property), 'No such property in Atom : "' . $property . '"');
        } elseif (is_array($property)) {
            $properties = $property;
            foreach($properties as $property) {
                assert( ($property === mb_strtolower($property)) || ($property === 'noDelimiter'), "Wrong format for property name : '$property'");
                assert($this->isProperty($property), "No such property in Atom : '$property'");
            }
        } else {
            assert(false, 'Unsupported type for property : ' . gettype($property));
        }
        return true;
    }

    protected function cleanAnalyzerName(string $gremlin, array $dependencies = array()): string {
        $fullNames = array_map(array($this, 'makeBaseName'), $dependencies);

        return str_replace($dependencies, $fullNames, $gremlin);
    }

    public static function makeBaseName(string $className): string {
        // No Exakat, no Analyzer, using / instead of \
        return $className;
    }

    protected function tolowercase(&$code) {
        if (is_array($code)) {
            $code = array_map('mb_strtolower', $code);
        } elseif (is_scalar($code)) {
            $code = mb_strtolower($code);
        } else {
            assert(false, __METHOD__ . ' received an unprocessable object ' . gettype($code));
        }
    }

    protected function makeLinks($links, $direction = 'in') {
        if (empty($links)) {
            return '.out( )';
        }

        $return = array();

        $links = makeArray($links);
        foreach($links as $l) {
            if (empty($l)) {
                $return[] = ".$direction( )";
            } elseif (is_array($l)) {
                $list = implode('", "', $l);
                $return[] = ".$direction(\"$list\")";
            } elseif (is_string($l)) {
                $return[] = ".$direction(\"$l\")";
            } else {
                assert(false, __METHOD__ . ' received an unprocessable object ' . gettype($l));
            }
        }

        return implode('', $return);
    }
}

?>
