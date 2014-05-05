<?php
/**
 * Litus is a project by a group of students from the KU Leuven. The goal is to create
 * various applications to support the IT needs of student unions.
 *
 * @author Niels Avonds <niels.avonds@litus.cc>
 * @author Karsten Daemen <karsten.daemen@litus.cc>
 * @author Koen Certyn <koen.certyn@litus.cc>
 * @author Bram Gotink <bram.gotink@litus.cc>
 * @author Dario Incalza <dario.incalza@litus.cc>
 * @author Pieter Maene <pieter.maene@litus.cc>
 * @author Kristof Mariën <kristof.marien@litus.cc>
 * @author Lars Vierbergen <lars.vierbergen@litus.cc>
 * @author Daan Wendelen <daan.wendelen@litus.cc>
 *
 * @license http://litus.cc/LICENSE
 */

namespace BrBundle\Component\ContractParser;

use CommonBundle\Component\Util\Xml\Object as XmlObject;

/**
 * 
 *
 * @author Daan Wendelen <daan.wendelen@litus.cc>
 */
class Entry
{
    private $indent;
    private $state;
    
    private $nodes;
    
    public function __construct()
    {
        $this->indent = -1;
        $this->state = new NewState($this);
    }
    
    public function setIndent($indent)
    {
        $this->indent = $indent;
    }
    
    public function parse($text)
    {
        $indent = $this->nbSpacesLeadingLine($text);
        $rest = substr($text, $indent);
        
        if(strlen($rest) == 0)
            return;
        
        $this->handleLine($indent, $rest);
    }
    
    public function handleLine($indent, $text)
    {
        if($this->indent == -1)
            $this->indent = $indent;
        
        elseif($indent < $this->indent)
            throw new IllegalFormatException();
        
        if($indent == $this->indent)
        {
            if($text[0] == '*')
            {
                $text[0] = ' ';
                $this->state = $this->state->addEntry($text);
            }
            else
            {
                $this->state = $this->state->addText($text);
            }
        }
        else
        {
            $this->state->passOn($indent-$this->indent, $text);
        } 
    }
    
    protected function nbSpacesLeadingLine($line)
    {
        $i = 0;
        $l = strlen($line);
        while($i < $l)
        {
            if($line[$i] != ' ')
                break;
    
            $i++;
        }
    
        return $i;
    }
    
    /**
     * 
     * @param Node $node
     */
    public function addNodeToList($node)
    {
        $this->nodes[] = $node;
    }
}