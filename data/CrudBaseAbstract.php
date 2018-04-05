<?php

/**
* Abstrair operações básicas do crud com automatização de alguns processos.
*/
abstract class CrudBaseAbstract
{
    protected $primaryKey = 'id';

    protected $connection;

    protected $table;

    protected $attributes = [];

    protected $fillable = [];

    final public function __construct()
    {
        $config = require('../config.php');

        try {
            $this->connection = new PDO(
                $config['database']['dsn'],
                $config['database']['username'],
                $config['database']['password']
            );
        } catch (PDOException $e) {
            throw new Exception("Erro ao conectar com o banco de dados", 1);
        }
    }

    public function all()
    {
        $conn = $this->connection;

        $sql = sprintf('SELECT * FROM %s', $this->table);
        $result = $conn->query($sql);

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($attributes)
    {
        if (isset($this->attributes[$this->primaryKey])) {
            return $this->attributes;
        }

        $this->fill($attributes);

        $conn = $this->connection;
        $stmt = $conn->prepare($this->prepareCreateQuery());
        $stmt->execute(array_values($this->attributes));

        $this->attributes[$this->primaryKey] = $conn->lastInsertId();

        return $this->attributes;
    }

    protected function prepareCreateQuery()
    {
        $fields = array_keys($this->attributes);
        $params = array_map(function($el) { return "?"; }, $fields);

        $query = sprintf(
            'INSERT INTO %s(%s) VALUES(%s);',
            $this->table,
            implode(', ', $fields),
            implode(', ', $params)
        );

        return $query;
    }

    public function read($primaryKey)
    {
        $conn = $this->connection;

        $sql = sprintf('SELECT * FROM %s WHERE %s = ?', $this->table, $this->primaryKey);
        $stmt = $conn->prepare($sql);
        $stmt->execute(array($primaryKey));

        $this->attributes = $stmt->fetch(PDO::FETCH_ASSOC);

        return $this->attributes;
    }

    public function readOrFail($primaryKey)
    {
        if (null === ($data = $this->read($primaryKey))) {
            throw new Exception("Registro não encontrado", 1);
        }

        return $data;
    }

    public function update($attributes)
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            throw new Exception("Erro: Chave primária não esta presente.", 1);
        }

        $this->fill($attributes);

        $conn = $this->connection;
        $stmt = $conn->prepare($this->prepareUpdateQuery());
        $stmt->execute($this->prepareUpdateValues());

        return $this->attributes;
    }

    protected function prepareUpdateQuery()
    {
        $filterFields = function($el) { 
            return $el !== $this->primaryKey;
        };

        $fields = array_filter(array_keys($this->attributes), $filterFields);
        $params = array_map(function($el) { return "$el = :$el"; }, $fields);

        $query = sprintf(
            'UPDATE %1$s SET %2$s WHERE %3$s = :%3$s;',
            $this->table,
            implode(', ', $params),
            $this->primaryKey
        );

        return $query;
    }

    protected function prepareUpdateValues()
    {
        $values = [];
        foreach ($this->attributes as $key => $value) {
            $values[":$key"] = $value;
        }

        return $values;
    }

    public function delete($primaryKey)
    {
        $sql = sprintf('DELETE FROM %s WHERE %s = ?;', $this->table, $this->primaryKey);

        $conn = $this->connection;
        $stmt = $conn->prepare($sql);
        $stmt->execute([$primaryKey]);
    }

    public function fill($attributes)
    {
        if (!is_array($attributes)) {
            throw new \Exception("Attributes not a array.");
        }

        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }

        return $this;
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function getAttribute($key)
    {
        if (! $key) {
            return;
        }

        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return;
    }
}
