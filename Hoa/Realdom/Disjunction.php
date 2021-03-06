<?php

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2014, Ivan Enderlin. All rights reserved.
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

namespace {

from('Hoa')

/**
 * \Hoa\Realdom\Exception
 */
-> import('Realdom.Exception')

/**
 * \Hoa\Realdom\Crate\Variable
 */
-> import('Realdom.Crate.Variable')

/**
 * \Hoa\Realdom
 */
-> import('Realdom.~')

/**
 * \Hoa\Visitor\Element
 */
-> import('Visitor.Element');

}

namespace Hoa\Realdom {

/**
 * Class \Hoa\Realdom\Disjunction.
 *
 * Represent a disjunction of realistic domains.
 *
 * @author     Ivan Enderlin <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright © 2007-2014 Ivan Enderlin.
 * @license    New BSD License
 */

class          Disjunction
    implements \ArrayAccess,
               \IteratorAggregate,
               \Countable,
               \Hoa\Visitor\Element {

    /**
     * Original disjointed realistisc domains.
     *
     * @var \Hoa\Realdom\Disjunction array
     */
    protected $_originalRealdoms = array();

    /**
     * Disjointed realistic domains.
     *
     * @var \Hoa\Realdom\Disjunction array
     */
    protected $_realdoms         = array();

    /**
     * Added realistic domains, variables etc. (as $this->_realdoms but
     * un-flattened).
     *
     * @var \Hoa\Realdom\Disjunction array
     */
    protected $__realdoms        = array();

    /**
     * $this->_realdoms to $this->__realdoms:
     *     {index from __realdoms => number of produced values in _realdoms}
     *
     * @var \Hoa\Realdom\Disjunction array
     */
    protected $__matches         = array();

    /**
     * Chosen realistic domain.
     *
     * @var \Hoa\Realdom object
     */
    protected $_chosenRealdom    = null;

    /**
     * Constraints.
     *
     * @var \Hoa\Realdom\Disjunction array
     */
    protected $_constraints      = array();

    /**
     * Holder.
     *
     * @var \Hoa\Realdom\IRealdom\Holder
     */
    protected $_holder           = null;



    /**
     * Construct.
     *
     * @access  public
     * @return  void
     */
    public function __construct ( ) {

        // Original realdoms are the same in every clone.
        $this->_originalRealdoms = &$this->_originalRealdoms;

        // In the original object (not a clone):
        $this->_realdoms         = &$this->_originalRealdoms;

        return;
    }

    /**
     * Clone.
     *
     * @access  public
     * @return  void
     */
    public function __clone ( ) {

        // Break the reference.
        unset($this->_realdoms);

        // Create a new array of references (of realdoms).
        $this->_realdoms = $this->_originalRealdoms;

        $this->_chosenRealdom = null;

        return;
    }

    /**
     * Allow to write: $disjunction->realdom1()->or->realdom2().
     *
     * @access  public
     * @param   string  $name    Must be “or”.
     * @return  \Hoa\Realdom\Disjunction
     */
    public function __get ( $name ) {

        if('or' !== $name)
            return $this->$name;

        return $this;
    }

    /**
     * Declare a realistic domain: $disjunction->realdomName(arg1, arg2…).
     * About constants: use for example: $disjunction->const(true).
     *
     * @access  public
     * @param   string  $name         Realistic domain name.
     * @param   array   $arguments    Arguments.
     * @return  \Hoa\Realdom\Disjunction
     * @throw   \Hoa\Realdom\Exception
     */
    public function __call ( $name, Array $arguments ) {

        $name = ucfirst(strtolower($name));

        if('Const' === $name) {

            $handle    = $arguments;
            Realdom::autoBoxing($handle);
            $handle    = $handle[0];
            $arguments = array();
        }
        elseif('Variable' === $name) {

            $handle = new Crate\Variable($arguments[0]);
        }
        else {

            if(\Hoa\Core\Consistency::isKeyword($name))
                $name = 'Realdom' . $name;

            try {

                $handle = dnew(
                    '(Hoathis or Hoa)\Realdom\\' . $name,
                    $arguments
                );
            }
            catch ( Exception $e ) {

                throw $e;
            }
            catch ( \Hoa\Core\Exception $e ) {

                throw new Exception(
                    'Realistic domain %s() does not exist (or something ' .
                    'wrong happened).',
                    0, strtolower($name), $e);
            }
        }

        $this->offsetSet(null, $handle);

        return $this;
    }

    /**
     * Alias of $this->__call(…) if $name is not parsed by PHP.
     *
     * @access  public
     * @param   string  $name         Realistic domain name.
     * @param   array   $arguments    Arguments.
     * @return  \Hoa\Realdom\Disjunction
     */
    public function _call ( $name, Array $arguments = array() ) {

        return $this->__call($name, $arguments);
    }

    /**
     * Check if a realistic domain exists for a specified offset.
     *
     * @access  public
     * @param   mixed  $offset    Offset.
     * @return  bool
     */
    public function offsetExists ( $offset ) {

        return array_key_exists($offset, $this->_realdoms);
    }

    /**
     * Get a specific realistic domain.
     *
     * @access  public
     * @param   mixed  $offset    Offset.
     * @return  \Hoa\Realdom
     */
    public function offsetGet ( $offset ) {

        if(false === $this->offsetExists($offset))
            return null;

        return $this->_realdoms[$offset];
    }

    /**
     * Set a specific realistic domain.
     *
     * @access  public
     * @param   mixed         $offset     Offset.
     * @param   \Hoa\Realdom  $realdom    Realistic domain.
     * @return  \Hoa\Realdom\Disjunction
     * @throw   \Hoa\Realdom\Exception
     */
    public function offsetSet ( $offset, $realdom ) {

        if($realdom instanceof self) {

            foreach($realdom as $_realdom)
                $this->_offsetSet(null, $_realdom);

            return $this;
        }

        if($realdom instanceof Crate\Variable) {

            $this->__realdoms[] = $realdom;
            $unfolded           = 1;

            foreach($realdom->getDomains() as $_realdom) {

                $this->_offsetSet(null, $_realdom, false);
                ++$unfolded;
            }

            $this->__matches[] = $unfolded;

            return $this;
        }

        return $this->_offsetSet($offset, $realdom);
    }

    /**
     * Set a specific realistic domain.
     *
     * @access  protected
     * @param   mixed         $offset       Offset.
     * @param   \Hoa\Realdom  $realdom      Realistic domain.
     * @param   bool          $backStore    Back-store in __realdoms or not.
     * @return  \Hoa\Realdom\Disjunction
     * @throw   \Hoa\Realdom\Exception
     */
    protected function _offsetSet ( $offset, $realdom, $backStore = true ) {

        if(!($realdom instanceof Realdom))
            throw new Exception(
                'A disjunction accepts only realdom; given %s.',
                1, is_object($realdom) ? get_class($realdom) : gettype($realdom));

        $realdom->setConstraints($this->_constraints);

        if(null === $offset) {

            $this->_realdoms[] = $realdom;

            if(true === $backStore) {

                $this->__realdoms[] = &$realdom;
                $this->__matches[]  = 1;
            }
        }
        else
            throw new Exception(
                'Offset %s must be null.', 2);

        return $this;
    }

    /**
     * Unset a specific realistic domain.
     * Index are re-computed.
     *
     * @access  public
     * @param   mixed  $offset    Offset.
     * @return  void
     */
    public function offsetUnset ( $offset ) {

        if(null === $this->offsetGet($offset))
            return;

        array_splice($this->__realdoms, $offset, 1);

        $acc = 0;

        for($i = 0; $i < $offset - 1; ++$i)
            $acc += $this->__matches[$i];

        array_splice($this->_realdoms, $acc, $this->__matches[$i]);

        return;
    }

    /**
     * Get realistic domains.
     *
     * @access  public
     * @return  array
     */
    public function getRealdoms ( ) {

        return $this->_realdoms;
    }

    /**
     * Get chosen realistic domain.
     *
     * @access  public
     * @return  \Hoa\Realdom
     */
    public function getChosenRealdom ( ) {

        return $this->_chosenRealdom;
    }

    /**
     * Reset all realistic domains.
     *
     * @access  public
     * @return  void
     */
    public function reset ( ) {

        foreach($this->_realdoms as $realdom)
            $realdom->reset();

        return;
    }

    /**
     * Predicate whether the sampled value belongs to the realistic domains.
     *
     * @access  public
     * @param   mixed  $q    Sampled value.
     * @return  boolean
     */
    public function predicate ( $q ) {

        foreach($this->_realdoms as $realdom)
            if(true === $realdom->predicate($q))
                return true;

        return false;
    }

    /**
     * Sample a new value.
     *
     * @access  public
     * @param   \Hoa\Math\Sampler  $sampler    Sampler.
     * @return  mixed
     * @throw   \Hoa\Realdom\Exception
     */
    public function sample ( \Hoa\Math\Sampler $sampler = null ) {

        if(empty($this->_realdoms))
            throw new Exception(
                'Cannot sample because the disjunction is empty.', 3);

        if(   null === $sampler
           && null === $sampler = Realdom::getDefaultSampler())
            throw new Exception(
                'No sampler set. Please, use the %s::setDefaultSampler() ' .
                'method.',
                4, __NAMESPACE__);

        $m                    = count($this->_realdoms) - 1;
        $i                    = $sampler->getInteger(0, $m);
        $this->_chosenRealdom = $this->_realdoms[$i];

        return $this->_chosenRealdom->sample($sampler);
    }

    /**
     * Propagate constraints (public).
     *
     * @access  protected
     * @param   string  $type     Type.
     * @param   int     $index    Index.
     * @return  void
     */
    public function propagateConstraints ( $type, $index ) {

        foreach($this->_realdoms as $realdom)
            $realdom->propagateConstraints($type, $index);

        return;
    }

    /**
     * Iterate over realistic domains.
     *
     * @access  public
     * @return  \ArrayIterator
     */
    public function getIterator ( ) {

        return new \ArrayIterator($this->_realdoms);
    }

    /**
     * Get unflattened realistic domains.
     *
     * @access  public
     * @return  array
     */
    public function getUnflattenedRealdoms ( ) {

        return $this->__realdoms;
    }

    /**
     * Count number of realistics domains.
     *
     * @access  public
     * @return  int
     */
    public function count ( ) {

        return count($this->_realdoms);
    }

    /**
     * Set holder.
     *
     * @access  public
     * @param   \Hoa\Realdom\IRealdom\Holder  $holder    Holder.
     * @return  \Hoa\Realdom\IRealdom\Holder
     */
    public function setHolder ( IRealdom\Holder $holder ) {

        $old           = $holder;
        $this->_holder = $holder;

        foreach($this->_realdoms as $realdom)
            $realdom->setHolder($holder);

        return $old;
    }

    /**
     * Get holder.
     *
     * @access  public
     * @return  \Hoa\Realdom\IRealdom\Holder
     */
    public function getHolder ( ) {

        return $this->_holder;
    }

    /**
     * Accept a visitor.
     *
     * @access  public
     * @param   \Hoa\Visitor\Visit  $visitor    Visitor.
     * @param   mixed               &$handle    Handle (reference).
     * @param   mixed               $eldnah     Handle (no reference).
     * @return  mixed
     */
    public function accept ( \Hoa\Visitor\Visit $visitor,
                             &$handle = null, $eldnah = null ) {

        return $visitor->visit($this, $handle, $eldnah);
    }
}

}

namespace {

/**
 * Alias for creating a new disjunction.
 *
 * @access  public
 * @return  \Hoa\Realdom\Disjunction
 */
if(!function_exists('realdom')) {
function realdom ( ) {

    return new Hoa\Realdom\Disjunction();
}}

}
