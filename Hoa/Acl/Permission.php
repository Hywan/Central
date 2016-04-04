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

namespace Hoa\Acl;

/**
 * Class \Hoa\Acl\Permission.
 *
 * A permission is a right. A group holds zero or more permissions that can be
 * used to allow or disallow access to something.
 *
 * @copyright  Copyright © 2007-2016 Hoa community
 * @license    New BSD License
 */
class Permission
{
    /**
     * Permission ID.
     *
     * @var mixed
     */
    protected $_id    = null;

    /**
     * Permission label.
     *
     * @var string
     */
    protected $_label = null;



    /**
     * Built a new permission.
     *
     * @param   mixed   $id       Permission ID.
     * @param   string  $label    Permission label.
     * @return  void
     */
    public function __construct($id, $label = null)
    {
        $this->setId($id);
        $this->setLabel($label);

        return;
    }

    /**
     * Set permission ID.
     *
     * @param   mixed  $id    Permission ID.
     * @return  mixed
     */
    protected function setId($id)
    {
        $old       = $this->_id;
        $this->_id = $id;

        return $old;
    }

    /**
     * Get permission ID.
     *
     * @return  mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Set permission label.
     *
     * @param   string  $label    Permission label.
     * @return  string
     */
    public function setLabel($label)
    {
        $old          = $this->_label;
        $this->_label = $label;

        return $old;
    }

    /**
     * Get permission label.
     *
     * @return  mixed
     */
    public function getLabel()
    {
        return $this->_label;
    }
}
