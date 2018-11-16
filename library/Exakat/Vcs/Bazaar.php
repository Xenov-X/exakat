<?php
/*
 * Copyright 2012-2018 Damien Seguy – Exakat Ltd <contact(at)exakat.io>
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

namespace Exakat\Vcs;

use Exakat\Exceptions\HelperException;

class Bazaar extends Vcs {
    public function __construct($destination, $project_root) {
        parent::__construct($destination, $project_root);
        
        $res = shell_exec('bzr --version 2>&1');
        if (strpos($res, 'Bazaar') === false) {
            throw new HelperException('Bazar');
        }
    }

    public function clone($source) {
        $source = escapeshellarg($source);
        shell_exec("cd {$this->destinationFull}; bzr branch $source code");
    }

    public function update() {
        $res = shell_exec("cd {$this->destinationFull}/code; bzr update 2>&1");
        if (preg_match('/revision (\d+)/', $res, $r)) {
            return $r[1];
        } else {
            return '';
        }
    }

    public function getBranch() {
        $res = shell_exec("cd {$this->destinationFull}/code/; bzr version-info 2>&1 | grep branch-nick");
        return trim(substr($res, 13), " *\n");
    }

    public function getRevision() {
        $res = shell_exec("cd {$this->destinationFull}/code/; bzr version-info 2>&1 | grep revno");
        return trim(substr($res, 7), " *\n");
    }

    public function getInstallationInfo() {
        $stats = array();

        $res = trim(shell_exec('bzr --version 2>&1'));
        if (preg_match('/Bazaar \(bzr\) ([0-9\.]+) /', $res, $r)) {//
            $stats['installed'] = 'Yes';
            $stats['version'] = $r[1];
        } else {
            $stats['installed'] = 'No';
            $stats['optional'] = 'Yes';
        }
        
        return $stats;
    }

    public function getStatus() {
        $status = array('vcs'       => 'bzr',
                        'branch'    => $this->getBranch(),
                        'revision'  => $this->getRevision(),
                        'updatable' => true,
                       );

        return $status;
    }
}

?>