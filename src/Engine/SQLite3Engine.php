<?php

namespace Brick\Geo\Engine;

use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Geometry;

/**
 * Database engine based on a SQLite3 driver.
 *
 * The spatialite extension must be loaded in this driver.
 */
class SQLite3Engine extends DatabaseEngine
{
    /**
     * @var \SQLite3
     */
    private $sqlite3;

    /**
     * @param \SQLite3 $sqlite3
     */
    public function __construct(\SQLite3 $sqlite3)
    {
        $this->sqlite3 = $sqlite3;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeQuery($query, array $parameters)
    {
        $statement = $this->sqlite3->prepare($query);

        $index = 1;

        foreach ($parameters as $parameter) {
            if ($parameter instanceof Geometry) {
                $statement->bindValue($index++, $parameter->asBinary(), SQLITE3_BLOB);
                $statement->bindValue($index++, $parameter->SRID(), SQLITE3_INTEGER);
            } else {
                if ($parameter === null) {
                    $type = SQLITE3_NULL;
                } elseif (is_int($parameter)) {
                    $type = SQLITE3_INTEGER;
                } elseif (is_float($parameter)) {
                    $type = SQLITE3_FLOAT;
                } else {
                    $type = SQLITE3_TEXT;
                }

                $statement->bindValue($index++, $parameter, $type);
            }
        }

        $result = $statement->execute();

        return $result->fetchArray(SQLITE3_NUM)[0];
    }
}
