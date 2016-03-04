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

namespace Hoa\Graph;

use Hoa\Consistency;

/**
 * Class \Hoa\Graph.
 *
 * Get instance of different graph type.
 * When getting an instance of a type of a graph, the graph type (e.g.
 * \Hoa\Graph\AdjacencyList) extends this class. It is like an abstract
 * factory…
 *
 * @copyright  Copyright © 2007-2016 Hoa community
 * @license    New BSD License
 */
abstract class Graph
{
    /**
     * Graph type.
     *
     * @const string
     */
    const TYPE_ADJACENCYLIST = 'AdjacencyList';

    /**
     * Allow loop when building graph (when adding nodes).
     *
     * @const bool
     */
    const ALLOW_LOOP         = true;

    /**
     * Disallow loop when building graph (when adding nodes).
     *
     * @const bool
     */
    const DISALLOW_LOOP      = false;

    /**
     * Propagate delete.
     *
     * @const bool
     */
    const DELETE_CASCADE     = true;

    /**
     * Restrict delete.
     *
     * @const bool
     */
    const DELETE_RESTRICT    = false;

    /**
     * All nodes.
     *
     * @var array
     */
    protected $nodes = [];

    /**
     * If allow loop when building graph, it is set to ALLOW_LOOP (true),
     * DISALLOW_LOOP (false) else.
     *
     * @var bool
     */
    protected $loop  = self::DISALLOW_LOOP;



    /**
     * Get an empty graph.
     *
     * @param   bool       $loop    Allow or not loop.
     * @return  void
     */
    protected function __construct($loop = self::DISALLOW_LOOP)
    {
        $this->allowLoop($loop);

        return;
    }

    /**
     * Make an instance of a specific graph.
     *
     * @param   string  $type    Type of graph needed.
     * @return  \Hoa\Graph
     * @throws  \Hoa\Graph\Exception
     */
    public static function getInstance($type = self::TYPE_ADJACENCYLIST)
    {
        if ($type !== self::TYPE_ADJACENCYLIST) {
            throw new Exception(
                'Type %s is not supported. Only self:TYPE_ADJACENCYLIST is ' .
                'supported.',
                0,
                $type
            );
        }

        $arguments = func_get_args();
        array_shift($arguments);

        return Consistency\Autoloader::dnew('Hoa\Graph\\' . $type, $arguments);
    }

    /**
     * Add a node.
     *
     * @param   \Hoa\Graph\IGraph\Node  $node      Node to add.
     * @param   mixed                   $parent    Parent of node.
     * @return  void
     * @throws  \Hoa\Graph\Exception
     */
    abstract public function addNode(IGraph\Node $node, $parent = []);

    /**
     * Check if a node does already exist or not.
     *
     * @param   mixed   $nodeId    The node ID or the node instance.
     * @return  bool
     */
    abstract public function nodeExists($nodeId);

    /**
     * Get a node.
     *
     * @param   mixed   $nodeId    The node ID or the node instance.
     * @return  object
     * @throws  \Hoa\Graph\Exception
     */
    abstract public function getNode($nodeId);

    /**
     * Get all nodes.
     *
     * @return  array
     */
    protected function getNodes()
    {
        return $this->nodes;
    }

    /**
     * Get parent of a specific node.
     *
     * @param   mixed   $nodeId    The node ID or the node instance.
     * @return  object
     * @throws  \Hoa\Graph\Exception
     */
    abstract public function getParent($nodeId);

    /**
     * Get child of a specific node.
     *
     * @param   mixed   $nodeId    The node ID or the node instance.
     * @return  object
     * @throws  \Hoa\Graph\Exception
     */
    abstract public function getChild($nodeId);

    /**
     * Delete a node.
     *
     * @param   mixed   $nodeId       The node ID or the node instance.
     * @param   bool    $propagate    Propagate the erasure.
     * @return  void
     * @throws  \Hoa\Graph\Exception
     */
    abstract public function deleteNode($nodeId, $propagate = self::DELETE_RESTRICT);

    /**
     * Whether node is a leaf, i.e. does not have any child.
     *
     * @param   mixed   $nodeId    The node ID or the node instance.
     * @return  bool
     * @throws  \Hoa\Graph\Exception
     */
    abstract public function isLeaf($nodeId);

    /**
     * Whether node is a root, i.e. does not have any parent.
     *
     * @param   mixed   $nodeId    The node ID or the node instance.
     * @return  bool
     * @throws  \Hoa\Graph\Exception
     */
    abstract public function isRoot($nodeId);

    /**
     * Set the loop mode (self::ALLOW_LOOP or self::DISALLOW_LOOP).
     *
     * @param   bool    $loop    Allow or not loop.
     * @return  bool
     */
    public function allowLoop($loop = self::DISALLOW_LOOP)
    {
        $old        = $this->loop;
        $this->loop = $loop;

        return $old;
    }

    /**
     * Get the loop mode.
     *
     * @return  bool
     */
    public function isLoopAllow()
    {
        return $this->loop;
    }

    /**
     * Print the graph in the DOT language.
     *
     * @return  string
     */
    abstract public function __toString();
}

/**
 * Flex entity.
 */
Consistency::flexEntity('Hoa\Graph\Graph');
