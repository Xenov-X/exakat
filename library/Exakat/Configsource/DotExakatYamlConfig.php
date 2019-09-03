<?php
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

namespace Exakat\Configsource;

use Exakat\Phpexec;
use Exakat\Project;
use Exakat\Config as Configuration;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class DotExakatYamlConfig extends Config {
    const YAML_FILE = '.exakat.yml';
    private $dotExakatYaml = '';
    private $rulesets = array();

    public function __construct() {
        $this->dotExakatYaml = getcwd() . '/' . self::YAML_FILE;
        
        if (!file_exists($this->dotExakatYaml)) {
            $secondary = substr($this->dotExakatYaml, 0, -3).'yaml';
            if (file_exists($secondary)) {
                $this->dotExakatYaml = $secondary;
            }
        }
    }

    public function loadConfig($project) {
        if (!file_exists($this->dotExakatYaml)) {
            $this->config['inside_code'] = Configuration::WITH_PROJECTS;
            return self::NOT_LOADED;
        }

        try {
            $tmp_config = Yaml::parseFile($this->dotExakatYaml);
        } catch (ParseException $exception) {
            // Empty on purppose 
        }

        if (!is_array($tmp_config)) {
            // Can't use display while in config phase
            display("Failed to parse YAML file. Please, check its syntax.\n");
            return self::NOT_LOADED;
        }

        // removing empty values in the INI file
        foreach($tmp_config as &$value) {
            if (is_array($value) && empty($value[0])) {
                unset($value[0]);
            }
        }
        unset($value);

        $other_php_versions = array();
        foreach(Configuration::PHP_VERSIONS as $version) {
            $phpVersion = "php$version";
            if (empty($this->config->{$phpVersion})) {
                continue;
            }
            $php = new Phpexec($version[0] . '.' . $version[1], $this->config->{$phpVersion});
            if ($php->isValid()) {
                $other_php_versions[] = $version;
            }
        }
    
        // check and default values
        $defaults = array( 'other_php_versions' => $other_php_versions,
                           'phpversion'         => substr(PHP_VERSION, 0, 3),
                           'file_extensions'    => array('php', 'php3', 'inc', 'tpl', 'phtml', 'tmpl', 'phps', 'ctp', 'module'),
                           'project_rulesets'   => 'CompatibilityPHP53,CompatibilityPHP54,CompatibilityPHP55,CompatibilityPHP56,CompatibilityPHP70,CompatibilityPHP71,CompatibilityPHP72,CompatibilityPHP73,CompatibilityPHP74,Dead code,Security,Analyze,Preferences,Appinfo,Appcontent',
                           'project_reports'    => array('Text'),
                           'ignore_dirs'        => array('/assets',
                                                         '/cache',
                                                         '/css',
                                                         '/data',
                                                         '/doc',
                                                         '/docker',
                                                         '/docs',
                                                         '/example',
                                                         '/examples',
                                                         '/images',
                                                         '/js',
                                                         '/lang',
                                                         '/spec',
                                                         '/sql',
                                                         '/test',
                                                         '/tests',
                                                         '/tmp',
                                                         '/version',
                                                         '/var',
                                                        ),
                           'include_dirs'        => array(),
                           'rulesets'            => array(),
                           'project'             => new Project(),
                           'project_name'        => '',
                        );

        $this->config['inside_code'] = Configuration::INSIDE_CODE;

        foreach($defaults as $name => $default_value) {
            $this->config[$name] = empty($tmp_config[$name]) ? $default_value : $tmp_config[$name];
            unset($tmp_config[$name]);
        }

        if (isset($tmp_config['project_themes'])) {
            display("please, rename project_themes into project_rulesets in your .exakat.yaml file\n");
            
            if (empty($this->config['project_rulesets'])) {
                $this->config['project_rulesets'] = $this->config['project_themes'];
            }
        }

        if (is_string($this->config['other_php_versions'])) {
            $this->config['other_php_versions'] = explode(',', $this->config['other_php_versions']);
            foreach($this->config['other_php_versions'] as &$version) {
                $version = str_replace('.', '', trim($version));
            }
            unset($version);
        }

        if (is_string($this->config['file_extensions'])) {
            $this->config['file_extensions'] = explode(',', $this->config['file_extensions']);
            foreach($this->config['file_extensions'] as &$ext) {
                $ext = trim($ext, '. ');
            }
            unset($ext);
        }

        if (is_string($this->config['project_reports'])) {
            $this->config['project_reports'] = explode(',', $this->config['project_reports']);
            foreach($this->config['project_reports'] as &$ext) {
                $ext = trim($ext);
            }
            unset($ext);
        }

        if (is_string($this->config['project_rulesets'])) {
            $this->config['project_rulesets'] = explode(',', $this->config['project_rulesets']);
            foreach($this->config['project_rulesets'] as &$ext) {
                $ext = trim($ext);
            }
            unset($ext);
        }

        if (isset($this->config['project'])) {
            $this->config['project'] = new Project($this->config['project']);
        } 

        if (isset($this->config['rulesets'])) {
            $this->rulesets = array_map('array_values', $this->config['rulesets']);
            unset($this->config['rulesets']);
        }

        if (!empty($tmp_config)) {
            display('Ignoring ' . count($tmp_config) . ' unkown directives : ' . implode(', ', array_keys($tmp_config)));
        }

        return self::YAML_FILE;
    }
    
    public function getRulesets() {
        return $this->rulesets;
    }
}

?>