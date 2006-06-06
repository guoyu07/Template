<?php
/**
 * File containing the ezcTemplateReferenceOperatorAstNode class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @access private
 */
/**
 * Represents the PHP reference eperator '->'
 *
 * @package Template
 * @version //autogen//
 * @access private
 */
class ezcTemplateReferenceOperatorAstNode extends ezcTemplateBinaryOperatorAstNode
{

    public function __construct( $parameter1 = null, $parameter2 = null)
    {
        parent::__construct( $parameter1, $parameter2 );

        // Everything is possible. Member variables can be a property (changing the type).
        // Functions can return anything.
        $this->typeHint = self::TYPE_ARRAY | self::TYPE_VALUE;
    }
    

    /**
     * Returns a text string representing the PHP operator.
     * @return string
     */
    public function getOperatorPHPSymbol()
    {
        return '->';
    }
}
?>
