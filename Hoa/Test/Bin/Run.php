<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2016, Hoa community. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Hoa nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS AND CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Hoa\Test\Bin;

use Hoa\Consistency;
use Hoa\Console;
use Hoa\File;

/**
 * Class Hoa\Test\Bin\Run.
 *
 * Run tests.
 *
 * @copyright  Copyright © 2007-2016 Hoa community
 * @license    New BSD License
 */
class Run extends Console\Dispatcher\Kit
{
    /**
     * Options description.
     *
     * @var array
     */
    protected $options = [
        ['all',                  Console\GetOption::NO_ARGUMENT,       'a'],
        ['libraries',            Console\GetOption::REQUIRED_ARGUMENT, 'l'],
        ['namespaces',           Console\GetOption::REQUIRED_ARGUMENT, 'n'],
        ['directories',          Console\GetOption::REQUIRED_ARGUMENT, 'd'],
        ['files',                Console\GetOption::REQUIRED_ARGUMENT, 'f'],
        ['filter',               Console\GetOption::REQUIRED_ARGUMENT, 'F'],
        ['debug',                Console\GetOption::NO_ARGUMENT,       'D'],
        ['php-binary',           Console\GetOption::REQUIRED_ARGUMENT, 'p'],
        ['concurrent-processes', Console\GetOption::REQUIRED_ARGUMENT, 'P'],
        ['help',                 Console\GetOption::NO_ARGUMENT,       'h'],
        ['help',                 Console\GetOption::NO_ARGUMENT,       '?']
    ];



    /**
     * The entry method.
     *
     * @return  int
     */
    public function main()
    {
        $directories         = [];
        $files               = [];
        $namespaces          = [];
        $filter              = [];
        $debug               = false;
        $php                 = null;
        $concurrentProcesses = 2;

        while (false !== $c = $this->getOption($v)) {
            switch ($c) {
                case 'a':
                    $iterator = new File\Finder();
                    $iterator->in(resolve('hoa://Library/', true, true))
                             ->directories()
                             ->maxDepth(1);

                    foreach ($iterator as $fileinfo) {
                        $libraryName = $fileinfo->getBasename();

                        if (true === Consistency::isKeyword($libraryName)) {
                            continue;
                        }

                        $pathname       = resolve('hoa://Library/' . $libraryName);
                        $tests          = $pathname . DS . 'Test' . DS;
                        $manualTests    = $tests . 'Unit';
                        $automaticTests = $tests . 'Praspel' . DS . 'Unit';

                        if (is_dir($manualTests)) {
                            $directories[] = $manualTests;
                        }

                        if (is_dir($automaticTests)) {
                            $directories[] = $automaticTests;
                        }
                    }

                    break;

                case 'l':
                    foreach ($this->parser->parseSpecialValue($v) as $library) {
                        $libraryName = ucfirst(strtolower($library));
                        $pathname    = resolve('hoa://Library/' . $libraryName);
                        $tests       = $pathname . DS . 'Test';

                        if (!is_dir($tests)) {
                            throw new Console\Exception(
                                'Library %s does not exist or has no test.',
                                0,
                                $libraryName
                            );
                        }

                        $directories[] = $tests;
                        $namespaces[]  = 'Hoa\\' . $libraryName;
                    }

                    break;

                case 'n':
                    foreach ($this->parser->parseSpecialValue($v) as $namespace) {
                        $namespace = str_replace('.', '\\', $namespace);
                        $parts     = explode('\\', $namespace);

                        if (2 > count($parts)) {
                            throw new Console\Exception(
                                'Namespace %s is too short.',
                                1,
                                $namespace
                            );
                        }

                        $head               = resolve('hoa://Library/' . $parts[1]);
                        $tail               = implode(DS, array_slice($parts, 2));
                        $namespaceDirectory = $head . DS . $tail;

                        if (!is_dir($namespaceDirectory)) {
                            throw new Console\Exception(
                                'Namespace %s does not exist.',
                                2,
                                $namespace
                            );
                        }

                        $tests          = $head . DS . 'Test' . DS;
                        $manualTests    = $tests . 'Unit' . DS . $tail;
                        $automaticTests = $tests . 'Praspel' . DS . 'Unit' . DS . $tail;

                        if (is_dir($manualTests)) {
                            $directories[] = $manualTests;
                        }

                        if (is_dir($automaticTests)) {
                            $directories[] = $automaticTests;
                        }

                        $namespaces[] = $namespace;
                    }

                    break;

                case 'd':
                    foreach ($this->parser->parseSpecialValue($v) as $directory) {
                        if (!is_dir($directory)) {
                            throw new Console\Exception(
                                'Directory %s does not exist.',
                                3,
                                $directory
                            );
                        }

                        $directories[] = $directory;
                    }

                    break;

                case 'f':
                    foreach ($this->parser->parseSpecialValue($v) as $file) {
                        if (!file_exists($file)) {
                            throw new Console\Exception(
                                'File %s does not exist.',
                                4,
                                $file
                            );
                        }

                        $files[] = $file;
                    }

                    break;

                case 'F':
                    $filter = $v;

                    break;

                case 'D':
                    $debug = $v;

                    break;

                case 'p':
                    $php = $v;

                    break;

                case 'P':
                    $concurrentProcesses = intval($v);

                    break;

                case '__ambiguous':
                    $this->resolveOptionAmbiguity($v);

                    break;

                case 'h':
                case '?':
                default:
                    return $this->usage();

                    break;
            }
        }

        $atoum = 'atoum';

        if (WITH_COMPOSER) {
            $atoum =
                __DIR__ . DS .
                '..' . DS .
                '..' . DS .
                '..' . DS .
                'bin' . DS .
                'atoum';
        } elseif (isset($_SERVER['HOA_ATOUM_BIN'])) {
            $atoum = $_SERVER['HOA_ATOUM_BIN'];
        }

        $command =
            $atoum .
            ' --configurations ' .
                resolve('hoa://Library/Test/.atoum.php') .
            ' --bootstrap-file ' .
                resolve('hoa://Library/Test/.bootstrap.atoum.php') .
            ' --force-terminal' .
            ' --max-children-number ' . $concurrentProcesses;

        if (true === $debug) {
            $command .= ' --debug';
        }

        if (null !== $php) {
            $command .= ' --php ' . $php;
        }

        if (empty($directories) && empty($files) && empty($namespaces) && is_dir('Test')) {
            $directories[] = 'Test';
        }

        if (!empty($directories)) {
            $command .= ' --directories ' . implode(' ', $directories);
        } elseif (!empty($files)) {
            $command .= ' --files ' . implode(' ', $files);
        } else {
            return $this->usage();
        }

        if (!empty($namespaces)) {
            $command .= ' --namespaces ' . implode(' ', $namespaces);
        }

        if (!empty($filter)) {
            $command .= ' --filter \'' . str_replace('\'', '\'"\'"\'', $filter) . '\'';
        }


        $_server                     = $_SERVER;
        $_server['HOA_PREVIOUS_CWD'] = getcwd();

        $processus = new Processus(
            $command,
            null,
            null,
            resolve('hoa://Library/Test/'),
            $_server
        );
        $processus->on('input', function ($bucket) {
            return false;
        });
        $processus->on('output', function ($bucket) {
            $data = $bucket->getData();

            echo $data['line'], "\n";

            return;
        });
        $processus->run();

        return;
    }

    /**
     * The command usage.
     *
     * @return  int
     */
    public function usage()
    {
        echo
            'Usage   : test:run <options>', "\n",
            'Options :', "\n",
            $this->makeUsageOptionsList([
                'a'    => 'Run tests of all libraries.',
                'l'    => 'Run tests of some libraries.',
                'n'    => 'Run tests of some namespaces.',
                'd'    => 'Run tests of some directories.',
                'f'    => 'Run tests of some files.',
                'F'    => 'Filter tests with a ruler expression (see ' .
                          'Hoa\Ruler).',
                'D'    => 'Activate the debugging mode.',
                'p'    => 'Path to a specific PHP binary.',
                'P'    => 'Maximum concurrent processes that can run.',
                'help' => 'This help.'
            ]), "\n\n",
            'Available variables for filter expressions:', "\n",
            '    * method,', "\n",
            '    * class,', "\n",
            '    * namespace,', "\n",
            '    * tags.', "\n";

        return;
    }
}

class Processus extends Console\Processus
{
    /**
     * Avoid to escape the command.
     *
     * @param   string  $command    Command name.
     * @return  string
     */
    protected function setCommand($command)
    {
        $old            = $this->getCommand();
        $this->_command = $command;

        return $old;
    }
}

__halt_compiler();
Run tests.
