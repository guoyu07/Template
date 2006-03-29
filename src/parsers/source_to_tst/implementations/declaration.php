<?php
/**
 * File containing the ezcTemplateDeclarationSourceToTstParser class
 *
 * @package Template
 * @version //autogen//
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 *
 * @package Template
 * @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 */
class ezcTemplateDeclarationBlockSourceToTstParser extends ezcTemplateSourceToTstParser
{
    /**
     * No known type found.
     */
    const MSG_VARIABLE_EXPECTED    = "A variable is expected";
    const MSG_INVALID_EXPRESSION   = "The expression is not valid";
    const MSG_UNEXPECTED_TOKEN     = "Unexpected token: %s";

    const LNG_INVALID_NAMESPACE_MARKER      = "The namespace marker (:) was used in template engine in eZ publish 3.x but is no longer allowed.";

    /**
     * The value of the parsed type or null if nothing was parsed.
     * @var mixed
     */
    public $value;

    /**
     * The parsed element object which defines the type or null if nothing
     * was parsed.
     */
    public $element;

    /**
     * Passes control to parent.
     */
    function __construct( ezcTemplateParser $parser, /*ezcTemplateSourceToTstParser*/ $parentParser, /*ezcTemplateCursor*/ $startCursor )
    {
        parent::__construct( $parser, $parentParser, $startCursor );
        $this->value = null;
        $this->element = null;
    }

    /**
     * Parses the types by utilizing:
     * - ezcTemplateFloatSourceToTstParser for float types.
     * - ezcTemplateIntegerSourceToTstParser for integer types.
     * - ezcTemplateStringSourceToTstParser for string types.
     * - ezcTemplateBoolSourceToTstParser for boolean types.
     * - ezcTemplateArraySourceToTstParser for array types.
     */
    protected function parseCurrent( ezcTemplateCursor $cursor )
    {
        $this->findNextElement();
        $symbolType = null;

        if ( $this->currentCursor->match( "var" ) )       $symbolType = ezcTemplateSymbolTable::VARIABLE;
        elseif ( $this->currentCursor->match( "cycle" ) ) $symbolType = ezcTemplateSymbolTable::CYCLE;
        elseif ( $this->currentCursor->match( "use" ) )   $symbolType = ezcTemplateSymbolTable::IMPORT;

        if( $symbolType !== null )
        {
            $this->findNextElement();

            // $var
            if( $this->parseSubDefineBlock( $symbolType ) )
            {
                if( $this->currentCursor->match( "}" ) )
                {
                    $this->status = self::PARSE_SUCCESS;
                    return true;
                }
                elseif( $this->currentCursor->match( ":", false ) )
                {
                    throw new ezcTemplateSourceToTstParserException( $this, $this->currentCursor, 
                        sprintf( self::MSG_UNEXPECTED_TOKEN, $this->currentCursor->current(1) ), self::LNG_INVALID_NAMESPACE_MARKER); 
                }
                else
                {
                    throw new ezcTemplateSourceToTstParserException( $this, $this->currentCursor, 
                        sprintf( self::MSG_UNEXPECTED_TOKEN, $this->currentCursor->current(1) ) );
                }
            }
            else
            {
                throw new ezcTemplateSourceToTstParserException( $this, $this->currentCursor, self::MSG_VARIABLE_EXPECTED );
            }
        }

        return false;
    }

    // TODO, remove atEnd, nodes can determine their own 'end'.
    public function atEnd( ezcTemplateCursor $cursor, /*ezcTemplateTstNode*/ $operator, $finalize = true )
    {
        return ( $cursor->current(1) == "}"  || $cursor->current(1) == ",");
    }

    protected function parseSubDefineBlock( $symbolType )
    {
        $isFirst = true; // First Variable parse, may be invalid. Return false in that case. 

        do
        {
            $this->findNextElement();

            $declaration = $this->parser->createDeclaration( $this->startCursor, $this->currentCursor );
            $declaration->isClosingBlock = false;
            $declaration->isNestingBlock = false;

            if( ! $this->parseOptionalType( "Variable", $this->currentCursor, false ) )
            {
                if( $isFirst ) return false;

                throw new ezcTemplateSourceToTstParserException( $this, $this->currentCursor, self::MSG_VARIABLE_EXPECTED );
            }

            $isFirst = false;
            $declaration->variable = $this->lastParser->elements[0];

            // Variable name.
            if ( !$this->parser->symbolTable->enter( $declaration->variable->name, $symbolType ) )
            {
                throw new ezcTemplateSourceToTstParserException( $this, $this->startCursor, $this->parser->symbolTable->getErrorMessage() );
            }

            $this->findNextElement();

            if( $this->currentCursor->match( "=" ) )
            {
                if( !$this->parseOptionalType( "Expression", null, false ) )
                {
                    throw new ezcTemplateSourceToTstParserException( $this, $this->currentCursor, self::MSG_INVALID_EXPRESSION );
                }

                $declaration->expression = $this->lastParser->rootOperator;
            }

            $this->appendElement( $declaration );
            $this->findNextElement();

        } while ( $this->currentCursor->match(",") );

        return true;
   }

}

?>