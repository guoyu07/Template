<?php
/**
 * File containing the ezcTemplateAstNode abstract class
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @package Template
 * @version //autogen//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @access private
 */
/**
 * Extended abstract class for representing PHP code elements which have parameters.
 *
 * The extended class adds a parameter list and has a method to return them
 * and append new ones. In addition it can control the minimum and maxium
 * number of parameters a class should have.
 *
 * @package Template
 * @version //autogen//
 * @access private
 */
abstract class ezcTemplateParameterizedAstNode extends ezcTemplateAstNode
{
    /**
     * A list of parameters for the code element.
     * The number of parameters and how they are treated is up to each specific
     * code element class.
     *
     * @var array
     */
    public $parameters;

    /**
     * Controls the minimum number of parameters the operator can handle.
     * @var int
     */
    public $minParameterCount;

    /**
     * Controls the maximum number of parameters the operator can handle.
     * @var int
     */
    public $maxParameterCount;

    /**
     * Constructs the ezcTemplateParameterizedAstNode.
     *
     * @param int $minParameterCount The minimum parameters the operator can have, set to false to remove limit.
     * @param int $maxParameterCount The maximum parameters the operator can have, set to false to remove limit.
     */
    public function __construct( $minParameterCount = 1, $maxParameterCount = 1 )
    {
        parent::__construct();
        if ( !is_int( $minParameterCount ) &&
             $minParameterCount !== false )
        {
            throw new ezcTemplateInternalException( "The parameter \$minParameterCount needs be an integer." );
        }

        if ( !is_int( $maxParameterCount ) &&
             $maxParameterCount !== false )
        {
            throw new ezcTemplateInternalException( "The parameter \$maxParameterCount needs be an integer." );
        }

        $this->minParameterCount = $minParameterCount;
        $this->maxParameterCount = $maxParameterCount;
        $this->parameters = array();
    }

    /**
     * Appends the code element $code as a parameter to the current code element.
     *
     * @param ezcTemplateAstNode $code The code element to append.
     */
    public function appendParameter( ezcTemplateAstNode $code )
    {
        if ( $this->maxParameterCount !== false &&
             count( $this->parameters ) >= $this->maxParameterCount )
            throw new ezcTemplateInternalException( "Parameter count {$this->maxParameterCount} exceeded." );

        $this->parameters[] = $code;

        if ( $this->minParameterCount !== false && $this->minParameterCount == sizeof( $this->parameters ) )
        {
            $this->checkAndSetTypeHint();
        }
    }

       
    /**
     * Checks the typehints from the children and sets the typeHint of *this* node.
     */
    public function checkAndSetTypeHint()
    {
        $first = true;
        foreach ( $this->parameters as $parameter )
        {
            if ( $parameter->typeHint == null )
            {
                throw new ezcTemplateInternalException( "The typehint of the class ". get_class( $parameter ) . " is null." );
            }

            if ( $first )
            {
                $this->typeHint = $parameter->typeHint;
                $first = false;
            }
            else
            {
                $this->typeHint &= $parameter->typeHint;

                if ( !( $this->typeHint & self::TYPE_VALUE  ) )
                {
                    throw new ezcTemplateTypeHintException();
                }
            }
        }
    }


    /**
     * Returns the parameters of the code element.
     *
     * Note: Not all code elements uses parameters.
     *
     * @return array(ezcTemplateAstNode)
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Validates the parameters of the operators against their constraints.
     *
     * @throws Exception if the constraints are not met.
     */
    public function validate()
    {
        if ( $this->maxParameterCount !== false && count( $this->parameters ) > $this->maxParameterCount )
        {
            throw new ezcTemplateInternalException( "Too many parameters for class <" . get_class( $this ) . ">, needs at most {$this->maxParameterCount} but got <" . count( $this->parameters ) . ">." );
        }

        if ( $this->minParameterCount !== false && count( $this->parameters ) < $this->minParameterCount )
        {
            throw new ezcTemplateInternalException( "Too few parameters for class <" . get_class( $this ) . ">, needs at least {$this->minParameterCount} but got <" . count( $this->parameters ) . ">." );
        }
    }
}
?>
