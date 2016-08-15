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

namespace Hoa\Json\Test\Integration;

use Hoa\Compiler as LUT;
use Hoa\File;
use Hoa\Iterator;
use Hoa\Math;
use Hoa\Regex;
use Hoa\Test;

/**
 * Class \Hoa\Json\Test\Integration\Soundness.
 *
 * Check soundness of the LL(k) compiler.
 *
 * @copyright  Copyright © 2007-2016 Hoa community
 * @license    New BSD License
 */
class Soundness extends Test\Integration\Suite
{
    public function case_exaustive_json()
    {
        $this->with_json(
            new LUT\Llk\Sampler\BoundedExhaustive(
                $this->getJSONCompiler(),
                $this->getRegexSampler(),
                12
            )
        );
    }

    public function case_coverage_json()
    {
        $this->with_json(
            new LUT\Llk\Sampler\Coverage(
                $this->getJSONCompiler(),
                $this->getRegexSampler()
            )
        );
    }

    public function case_uniform_random_json()
    {
        $this
            ->given(
                $sampler = new LUT\Llk\Sampler\Uniform(
                    $this->getJSONCompiler(),
                    $this->getRegexSampler(),
                    5
                )
            )
            ->with_json(
                new Iterator\Limit(
                    new Iterator\CallbackGenerator(function () use ($sampler) {
                        return $sampler->uniform();
                    }),
                    0,
                    1000
                ),
                $sampler->getCompiler()
            );
    }

    protected function with_json($sampler, $compiler = null)
    {
        if (null === $compiler) {
            $compiler = $sampler->getCompiler();
        }

        $this
            ->when(function () use ($compiler, $sampler) {
                foreach ($sampler as $datum) {
                    $this
                        ->given(json_decode($datum))
                        ->executeOnFailure(function () use ($datum) {
                            if (true === function_exists('json_last_error_msg')) {
                                echo
                                    'Data:  ' . $datum, "\n",
                                    'Error: ' . json_last_error_msg(), "\n";
                            }
                        })
                        ->when($error = json_last_error())
                        ->then
                            ->integer($error)
                                ->isEqualTo(JSON_ERROR_NONE)

                        ->when($result = $compiler->parse($datum, null, false))
                        ->then
                            ->boolean($result)
                                ->isTrue();
                }
            });
    }

    protected function getJSONCompiler()
    {
        return LUT\Llk::load(
            new File\Read('hoa://Library/Json/Grammar.pp')
        );
    }

    protected function getRegexSampler()
    {
        return new Regex\Visitor\Isotropic(new Math\Sampler\Random());
    }
}
