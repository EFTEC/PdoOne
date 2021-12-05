<?php

namespace eftec\ext;

use Exception;

/**
 * TODO.
 */
class PdoOne_CSV implements PdoOne_IExt
{

    public function construct($charset)
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement construct() method.
    }

    public function connect($cs, $alterSession)
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement connect() method.
    }

    /**
     * @inheritDoc
     */
    public function callProcedure($procName, &$arguments = [], $outputColumns = [])
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement callProcedure() method.
    }

    /**
     * @inheritDoc
     */
    public function truncate($tableName, $extra, $force)
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement truncate() method.
    }

    /**
     * @inheritDoc
     */
    public function resetIdentity($tableName, $newValue = 0, $column = '')
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement resetIdentity() method.
    }

    /**
     * @inheritDoc
     */
    public function getDefTableExtended($table, $onlyDescription = false)
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement getDefTableExtended() method.
    }

    /**
     * @inheritDoc
     */
    public function getDefTable($table)
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement getDefTable() method.
    }

    /**
     * @inheritDoc
     */
    public function getDefTableKeys($table, $returnSimple, $filter = null)
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement getDefTableKeys() method.
    }

    /**
     * @inheritDoc
     */
    public function getDefTableFK($table, $returnSimple, $filter = null, $assocArray = false)
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement getDefTableFK() method.
    }

    /**
     * @inheritDoc
     */
    public function typeDict($row, $default = true)
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement typeDict() method.
    }

    /**
     * @inheritDoc
     */
    public function objectExist($type = 'table')
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement objectExist() method.
    }

    /**
     * @inheritDoc
     */
    public function objectList($type = 'table', $onlyName = false)
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement objectList() method.
    }

    public function columnTable($tableName)
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement columnTable() method.
    }

    public function foreignKeyTable($tableName)
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement foreignKeyTable() method.
    }

    /**
     * @inheritDoc
     */
    public function createSequence($tableSequence = null, $method = 'snowflake')
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement createSequence() method.
    }

    /**
     * @inheritDoc
     */
    public function createProcedure($procedureName, $arguments = [], $body = '', $extra = '')
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement createProcedure() method.
    }

    public function getSequence($sequenceName)
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement getSequence() method.
    }

    /**
     * @inheritDoc
     */
    public function createTable($tableName, $definition, $primaryKey = null, $extra = '', $extraOutside = '')
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement createTable() method.
    }

    /**
     * @inheritDoc
     */
    public function createFK($tableName, $foreignKey)
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement createFK() method.
    }

    /**
     * @inheritDoc
     */
    public function limit($sql)
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement limit() method.
    }

    /**
     * @inheritDoc
     */
    public function getPK($query, $pk = null)
    {
        throw new RuntimeException("no yet implemented"); // TODO: Implement getPK() method.
    }
}