<?php
declare(strict_types=1);

namespace JambageCom\TtBoard\Domain;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */



/**
 * Query parameters to be used as function parameters
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 */


class QueryParameter
{
    public const CLAUSE_AND_WHERE = 1;
    public const COMP_EQUAL = 1;

    public $clause = null;
    public $tablename = null;
    public $field = null;
    public $value = null;
    public $type = null;
    public $comparator = null;

    public function __construct($clause, $tablename, $field, $value, $type, $comparator)
    {
        $this->clause = $clause;
        $this->tablename = $tablename;
        $this->field = $field;
        $this->value = $value;
        $this->type = $type;
        $this->comparator = $comparator;
    }
}
