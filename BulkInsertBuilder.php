<?php

namespace Softmetrix\BulkInsertBuilder;

use InvalidArgumentException;
use PDO;

class BulkInsertBuilder
{
    const STATE_NEW = 0;
    const STATE_APPENDING = 1;

    protected $dsn;
    protected $username;
    protected $password;
    protected $table;
    protected $bulkSize;
    protected $query;
    protected $count;
    protected $state;
    protected $insertFieldsQueryPart;
    protected $connection;

    public function __construct($dsn, $username, $password, $table, $bulkSize = 1000)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->table = $table;
        $this->bulkSize = $bulkSize;
        $this->count = 0;
        $this->connection = new PDO($dsn, $username, $password);
    }

    public function bulkInsert(array $data)
    {
        if (!count($data)) {
            $message = 'Input data array is empty.';
            throw new InvalidArgumentException($message);
        }
        $keys = array_keys($data[0]);
        array_walk($keys, function (&$x) {$x = "`$x`"; });
        $this->insertFieldsQueryPart = implode(', ', $keys);
        $this->buildQueryFirstPart();
        foreach ($data as $row) {
            $values = array_values($row);
            array_walk($values, function (&$x) {$x = "'$x'"; });
            $valuesQueryPart = implode(', ', $values);
            $this->insert($valuesQueryPart);
        }
        $this->flush();
    }

    protected function insert($valuesQueryPart)
    {
        $this->appendValues($valuesQueryPart);
        ++$this->count;
        if ($this->count == $this->bulkSize) {
            $this->count = 0;
            $this->removeTrailingComma();
            $this->executeQuery();
            $this->buildQueryFirstPart();
        }
    }

    protected function flush()
    {
        if ($this->state == self::STATE_APPENDING) {
            $this->removeTrailingComma();
            $this->executeQuery();
        }
    }

    protected function buildQueryFirstPart()
    {
        $this->query = 'INSERT INTO '.$this->table.' ('.$this->insertFieldsQueryPart.') VALUES';
        $this->state = self::STATE_NEW;
    }

    protected function appendValues($valuesQueryPart)
    {
        $this->query .= '('.$valuesQueryPart.'),';
        $this->state = self::STATE_APPENDING;
    }

    protected function removeTrailingComma()
    {
        $this->query = trim($this->query, ',');
    }

    protected function executeQuery()
    {
        $this->connection->exec($this->query);
    }
}
