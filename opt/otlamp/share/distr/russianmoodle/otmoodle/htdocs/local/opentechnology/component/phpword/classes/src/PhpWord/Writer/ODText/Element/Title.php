<?php
/**
 * This file is part of PHPWord - A pure PHP library for reading and writing
 * word processing documents.
 *
 * PHPWord is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @see         https://github.com/PHPOffice/PHPWord
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpWord\Writer\ODText\Element;

/**
 * Title element writer
 *
 * @since 0.11.0
 */
class Title extends AbstractElement
{
    /**
     * Write element
     */
    public function write()
    {
        $xmlWriter = $this->getXmlWriter();
        $element = $this->getElement();
        if (!$element instanceof \PhpOffice\PhpWord\Element\Title) {
            return;
        }

        $xmlWriter->startElement('text:h');
        $xmlWriter->writeAttribute('text:outline-level', $element->getDepth());
        $text = $element->getText();
        if (is_string($text)) {
            $this->writeText($text);
        } elseif ($text instanceof \PhpOffice\PhpWord\Element\AbstractContainer) {
            $containerWriter = new Container($xmlWriter, $text);
            $containerWriter->write();
        }
        $xmlWriter->endElement(); // text:h
    }
}
