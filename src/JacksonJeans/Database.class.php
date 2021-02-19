<?php

namespace JacksonJeans;

/**
 * Database - Klasse
 * 
 * @category    Class
 * @package     GIDUTEX
 * @author      Julian Tietz <julian.tietz@gidutex.de>
 * @license     Julian Tietz <julian.tietz@gidutex.de>
 * @link        https://gidutex.de
 * @version     1.0
 */
class Database implements DatabaseInterface
{
    /**
     * @var \PDO $dbh
     * - Datenbank Host
     */
    private $dbh = null,

        /**
         * @var string $table 
         * - Tabelle
         */
        $table,

        /**
         * @var string $columns 
         * - Spalten
         */
        $columns,

        /**
         * @var string $sql 
         * - SQL-Ausdruck in der Verarbeitung
         */
        $sql,

        /**
         * @var array $bindValues
         * - Argumente für den SQL-Ausdruck
         */
        $bindValues,

        /**
         * @var string $getSQL 
         * - Endgültiger SQL-Ausdruck
         */
        $getSQL,

        /**
         * @var string $where 
         * - Where-Ausdruck
         */
        $where,

        /**
         * @var string $whereIn
         * -Where $column IN - Ausdruck
         */
        $whereIn,

        /**
         * @var string $orWhere 
         * - Or-Where-Ausdruck
         */
        $orWhere,

        /**
         * @var int $whereCount 
         * - Where-Ausdruck-Anzahl
         */
        $whereCount = 0,

        /**
         * @var bool $isOrWhere 
         * - Or-Where-Ausdruck vorhanden ?
         */
        $isOrWhere = false,

        /**
         * @var string $join 
         * - Join-Ausdruck
         */
        $join,

        /**
         * @var bool $isJoin 
         * - Join-Ausdruck vorhanden ?
         */
        $isJoin = false,

        /**
         * @var int $rowCount 
         * - Anzahl betroffener Datensätze
         */
        $rowCount = 0,

        /**
         * @var string $limit 
         * - Limitiert die Abfrage
         */
        $limit,

        /**
         * @var string $orderBy 
         * - OrderBy Ausdruck
         */
        $orderBy,

        /**
         * @var int $lastIDInserted 
         * - ID letzter hinzugefügter Datensatz
         */
        $lastIDInserted = 0,

        /**
         * @var float $processingTime 
         * - Verarbeitungszeit der letzten Anfrage in Sekunden
         */
        $processingTime = 0.0000,

        /**
         * @var float $totalTime
         * - Verarbeitungszeit aller Anfragen inklusive Lebzeit des Objektes Database
         */
        $totalTime = 0.0000;

    /**
     * Konstanten
     * Join
     */
    const LEFT = 'LEFT';
    const RIGHT = 'RIGHT';
    const INNER = 'INNER';
    const OUTER = 'OUTER';

    /**
     * Konstanten
     * OrderBy
     */
    const ASCENDING = 'ASC';
    const DESCENDING = 'DESC';

    /**
     * Konstanten
     * Datenbank Treiber
     */
    const MSSQL = 'sqlsrv';
    const MYSQL = 'mysql';
    const ODBC = 'odbc';
    const SQLITE = 'sqlite';

    /**
     * @var array $pagination 
     * - Initialisiere Werte für den Seitenumbruch
     */
    private $pagination = array(
        'previousPage' => null,
        'currentPage' => 1,
        'nextPage' => null,
        'lastPage' => null,
        'totalRows' => null
    );

    /**
     * Konstruktor
     * @param array $config 
     * - Konfigurations Array
     *  [
     *      # current development environment
     *       "env" => "development",
     *       # Localhost
     *       "development" => [
     *           "host" => "localhost",
     *           "database" => "test",
     *           "username" => "root",
     *           "password" => ""
     *       ],
     *       # Server
     *       "production"  => [
     *       "host" => "",
     *       "database" => "",
     *       "username" => "",
     *       "password" => ""
     *       ]
     *   ]
     * @param string $driver 
     * - Datenbank Treiber mit dem gearbeitet werden soll.
     * @return Database $this
     * @throws DatabaseException
     * - Im Fehlerfall wird eine DatabaseException geworfen.
     */
    public function __construct(array $config, $driver = 'mysql')
    {
        if ($config['env'] == "development") {
            $config = $config['development'];
        } elseif ($config['env'] == "production") {
            $config = $config['production'];
        } else {
            throw new DatabaseException(DatabaseException::CODE_NO_ENVIRONMENT);
            die;
        }

        try {
            switch ($driver) {
                    # mysql
                case 'mysql':
                    $params = array('host' => true, 'database' => true, 'username' => true, 'password' => true);
                    foreach ($params as $param => $required) {
                        if ((!isset($config[$param])) && ($required)) {
                            throw new DatabaseException(DatabaseException::CODE_INVALID_ARGUMENT, $param);
                        }
                    }
                    $conn = "mysql:host=" . $config['host'] . ";dbname=" . $config['database'] . ";charset=utf8";
                    $this->dbh = new \PDO($conn, $config['username'], $config['password']);
                    break;
                    # mssql
                case 'sqlsrv':
                    $params = array('host' => true, 'database' => true, 'username' => true, 'password' => true);
                    foreach ($params as $param => $required) {
                        if ((!isset($config[$param])) && ($required)) {
                            throw new DatabaseException(DatabaseException::CODE_INVALID_ARGUMENT, $param);
                        }
                    }
                    $conn = "sqlsrv:Server=" . $config['host'] . ";Database=" . $config['database'] . ";charset=utf8";
                    $this->dbh = new \PDO($conn, $config['username'], $config['password']);
                    break;
                    # sqlite
                case 'sqlite':
                    $params = array('file' => true);
                    foreach ($params as $param => $required) {
                        if ((!isset($config[$param])) && ($required)) {
                            throw new DatabaseException(DatabaseException::CODE_INVALID_ARGUMENT, $param);
                        }
                    }
                    $conn = "sqlite:" . $config['file'];
                    $this->dbh = new \PDO($conn, $config['username'], $config['password']);
                    break;
                    # odbc
                case 'odbc':

                    break;
                default:
                    throw new DatabaseException(DatabaseException::CODE_UNSPPORTED_DRIVER, $driver);
                    break;
            }

            $this->dbh->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
            return $this;
        } catch (\Exception $e) {
            throw new DatabaseException(DatabaseException::CODE_CONNECTION_FAIL, $e->getMessage());
            die;
        }
    }

    /**
     * Query-Methode, Überladung von \PDO
     * @param string $query 
     * - Abfragezeichenfolge als SQL Ausdruck
     * @param array $args
     * - Argumente die in der vorbereiteten Abfragezeichenfolge ($query) einfließen sollen
     * @param bool $object 
     * - Ausgabe als Objekt (Collection) mit Array Access
     * @return array|Collection|int 
     * - Gibt je nach Ausdruck array,Collection oder int zurück.
     * -- Gibt array zurück wenn $object = false und es SELECT-Ausdruck ist.
     * -- Gibt Collection zurück wenn $object = true ist und SELECT-Ausdruck ist.
     * -- Gibt Int zurück wenn es kein SELECT Ausdruck ist.
     * -- INT zeigt auf, wie viele Datensätze von der Abfrage betroffen sind.
     */
    public function query(string $query, $args = [], $object = false)
    {
        $begin = microtime(true);
        $this->resetQuery();
        $query = trim($query);
        $this->getSQL = $query;
        $this->bindValues = $args;

        if ($object == true) {
            $stmt = $this->dbh->prepare($query);
            $stmt->execute($this->bindValues);
            $this->rowCount = $stmt->rowCount();
            $this->processingTime = $t = floatval(sprintf('%.4f', microtime(true) - $begin));
            $this->totalTime += $t;
            return $stmt->fetchAll();
        } else {
            if (strpos(strtoupper($query), "SELECT") === 0) {
                $stmt = $this->dbh->prepare($query);
                $stmt->execute($this->bindValues);
                $this->rowCount = $stmt->rowCount();
                $rows = $stmt->fetchAll(\PDO::FETCH_CLASS, 'JacksonJeans\Item');
                $collection = [];
                $collection = new Collection;
                $x = 0;
                foreach ($rows as $row) {
                    $collection->offsetSet($x++, $row);
                }
                $this->processingTime = $t = floatval(sprintf('%.4f', microtime(true) - $begin));
                $this->totalTime += $t;
                return $collection;
            } else {
                $this->getSQL = $query;
                $stmt = $this->dbh->prepare($query);
                $stmt->execute($this->bindValues);
                $this->processingTime = $t = floatval(sprintf('%.4f', microtime(true) - $begin));
                $this->totalTime += $t;
                return $stmt->rowCount();
            }
        }
    }

    /**
     * Assimiliere die Anforderung und führe sie aus.
     * @return int $stmt->rowCount() 
     * - Anzahl betroffener Datensätze
     */
    public function exec()
    {
        $begin = microtime(true);
        # Abfrage assimilieren
        $this->sql .= $this->where;
        $this->getSQL = $this->sql;
        $stmt = $this->dbh->prepare($this->sql);
        $stmt->execute($this->bindValues);
        $this->processingTime = $t = floatval(sprintf('%.4f', microtime(true) - $begin));
        $this->totalTime += $t;
        return $stmt->rowCount();
    }

    /**
     * Simuliert die Anforderung
     * @return int $stmt->rowCount() 
     * - Anzahl betroffener Datensätze
     */
    public function simulate()
    {
        $begin = microtime(true);
        # Abfrage assimilieren
        $this->sql .= $this->where;
        $this->getSQL = $this->sql;
        $this->dbh->query('START TRANSACTION;');
        $stmt = $this->dbh->prepare($this->sql);
        $stmt->execute($this->bindValues);
        $this->dbh->query('ROLLBACK;');
        $this->processingTime = $t = floatval(sprintf('%.4f', microtime(true) - $begin));
        $this->totalTime += $t;
        return $stmt->rowCount();
    }

    /**
     * Setzt den Query Builder zurück
     */
    private function resetQuery()
    {
        $this->table = null;
        $this->columns = null;
        $this->sql = null;
        $this->bindValues = null;
        $this->limit = null;
        $this->orderBy = null;
        $this->getSQL = null;
        $this->where = null;
        $this->orWhere = null;
        $this->whereCount = 0;
        $this->isOrWhere = false;
        $this->rowCount = 0;
        $this->isJoin = false;
        $this->join = null;
        $this->lastIDInserted = 0;
    }

    /**
     * DELETE Abfragezeichenfolge
     * @param string $table_name 
     * - Betroffene Tabelle 
     * @param int|array $id 
     * - [[spalte,operator,wert],[spalte,wert],[wert (wenn nummeric, ist spalte = 'id')]]
     * - Betroffener Datensätze als Where Klausel:
     * @return int $stmt->rowCount() 
     * - Anzahl betroffener Datensätze
     */
    public function delete(string $table_name, $id = null)
    {
        $begin = microtime(true);
        $this->resetQuery();

        $this->sql = "DELETE FROM {$table_name}";

        if (isset($id)) {
            # falls id
            if (is_numeric($id)) {
                $this->sql .= " WHERE id = ?";
                $this->bindValues[] = $id;
                # falls id ein Array ist
            } elseif (is_array($id)) {
                $arr = $id;
                $x = 0;

                foreach ($arr as  $param) {
                    if ($x == 0) {
                        $this->where .= " WHERE ";
                        $x++;
                    } else {
                        if ($this->isOrWhere) {
                            $this->where .= " Or ";
                        } else {
                            $this->where .= " AND ";
                        }

                        $x++;
                    }

                    $count_param = count($param);

                    if ($count_param == 1) {
                        $this->where .= "id = ?";
                        $this->bindValues[] =  $param[0];
                    } elseif ($count_param == 2) {
                        $operators = explode(',', "=,>,<,>=,>=,<>");
                        $operatorFound = false;
                        foreach ($operators as $operator) {
                            if (strpos($param[0], $operator) !== false) {
                                $operatorFound = true;
                                break;
                            }
                        }

                        if ($operatorFound) {
                            $this->where .= $param[0] . " ?";
                        } else {
                            $this->where .= "" . trim($param[0]) . " = ?";
                        }

                        $this->bindValues[] =  $param[1];
                    } elseif ($count_param == 3) {
                        $this->where .= "" . trim($param[0]) . " " . $param[1] . " ?";
                        $this->bindValues[] =  $param[2];
                    }
                }
                # ende foreach
            }
            # ende falls array
            $this->sql .= $this->where;

            $this->getSQL = $this->sql;
            $stmt = $this->dbh->prepare($this->sql);
            $stmt->execute($this->bindValues);
            $this->processingTime = $t = floatval(sprintf('%.4f', microtime(true) - $begin));
            $this->totalTime += $t;
            return $stmt->rowCount();
        } # ende falls id oder array
        $this->processingTime = $t = floatval(sprintf('%.4f', microtime(true) - $begin));
        $this->totalTime += $t;
        return $this;
    }

    /**
     * UPDATE Abfragezeichenfolge
     * @param string $table_name 
     * - Betroffene Tabelle.
     * @param array $fields 
     * - Felder mit Spalten und Werten die aktualisiert werden sollen.
     * @param int|array $id [optional]
     * - [[spalte,operator,wert],[spalte,wert],[wert (wenn nummeric, ist spalte = 'id')]]
     * - Betroffener Datensätze als Where Klausel
     * @return Database|int $stmt->rowCount() 
     * - Anzahl betroffener Datensätze
     */
    public function update(string $table_name, $fields = [], $id = null)
    {
        $begin = microtime(true);
        $this->resetQuery();
        $set = '';
        $x = 1;

        foreach ($fields as $column => $field) {
            $set .= "$column = ?";
            $this->bindValues[] = $field;
            if ($x < count($fields)) {
                $set .= ", ";
            }
            $x++;
        }

        $this->sql = "UPDATE {$table_name} SET $set";

        if (isset($id)) {
            # falls id
            if (is_numeric($id)) {
                $this->sql .= " WHERE id = ?";
                $this->bindValues[] = $id;
                # falls id ein array ist
            } elseif (is_array($id)) {
                $arr = $id;
                $count_arr = count($arr);
                $x = 0;

                foreach ($arr as  $param) {
                    if ($x == 0) {
                        $this->where .= " WHERE ";
                        $x++;
                    } else {
                        if ($this->isOrWhere) {
                            $this->where .= " Or ";
                        } else {
                            $this->where .= " AND ";
                        }

                        $x++;
                    }
                    $count_param = count($param);

                    if ($count_param == 1) {
                        $this->where .= "id = ?";
                        $this->bindValues[] =  $param[0];
                    } elseif ($count_param == 2) {
                        $operators = explode(',', "=,>,<,>=,>=,<>");
                        $operatorFound = false;

                        foreach ($operators as $operator) {
                            if (strpos($param[0], $operator) !== false) {
                                $operatorFound = true;
                                break;
                            }
                        }

                        if ($operatorFound) {
                            $this->where .= $param[0] . " ?";
                        } else {
                            $this->where .= "" . trim($param[0]) . " = ?";
                        }

                        $this->bindValues[] =  $param[1];
                    } elseif ($count_param == 3) {
                        $this->where .= "" . trim($param[0]) . " " . $param[1] . " ?";
                        $this->bindValues[] =  $param[2];
                    }
                }
                # ende foreach
            }
            # ende falls array
            $this->sql .= $this->where;

            $this->getSQL = $this->sql;
            $stmt = $this->dbh->prepare($this->sql);
            $stmt->execute($this->bindValues);
            $this->processingTime = $t = floatval(sprintf('%.4f', microtime(true) - $begin));
            $this->totalTime += $t;
            return $stmt->rowCount();
        } # ende falls id oder array
        $this->processingTime = $t = floatval(sprintf('%.4f', microtime(true) - $begin));
        $this->totalTime += $t;
        return $this;
    }

    /**
     * INSERT Abfragezeichenfolge
     * @param string $table_name 
     * - Betroffene Tabelle.
     * @param array $fields 
     * - Felder mit Spalten und Werten die aktualisiert werden sollen.
     * @return int $this->dbh->lastInsertId()
     * - Gibt bei Erfolg die letzte InsertId zurück.
     */
    public function insert(string $table_name, $fields = [])
    {
        $begin = microtime(true);
        $this->resetQuery();

        $keys = implode(', ', array_keys($fields));
        $values = '';
        $x = 1;
        foreach ($fields as $field => $value) {
            $values .= '?';
            $this->bindValues[] =  $value;
            if ($x < count($fields)) {
                $values .= ', ';
            }
            $x++;
        }

        $this->sql = "INSERT INTO {$table_name} ({$keys}) VALUES ({$values})";
        $this->getSQL = $this->sql;
        $stmt = $this->dbh->prepare($this->sql);
        $stmt->execute($this->bindValues);
        $this->lastIDInserted = $this->dbh->lastInsertId();

        $this->processingTime = $t = floatval(sprintf('%.4f', microtime(true) - $begin));
        $this->totalTime += $t;
        return $this->lastIDInserted;
    }

    /**
     * Gibt die letzte InsertID zurück
     * @return int $this->lastIDInserted
     */
    public function lastId()
    {
        return $this->lastIDInserted;
    }

    /**
     * Setzt die betroffene Tabelle. E.g für eine SELECT-Ausführung (Siehe Doku)
     * @param string $table_name
     * - Betroffene Tabelle 
     * @return Database $this
     */
    public function table(string $table_name)
    {
        $this->resetQuery();
        $this->table = trim($table_name);
        return $this;
    }

    /**
     * Fügt dem SQL Ausdruck SELECT Anweisungen hinzu.
     * @param string $columns [string:'column1, column2, column3'] 
     * - Betroffene Spalten die mit SELECT ausgeführt werden sollen.
     * @return Database $this
     */
    public function select(string $columns)
    {
        $columns = explode(',', $columns);

        foreach ($columns as $key => $column) {
            $column = trim($column);
            $columns[$key] = trim("{$this->table}.{$column}");
        }

        $columns = implode(', ', $columns);


        $this->columns = "{$columns}";
        return $this;
    }

    /**
     * Fügt dem SQL Ausdruck eine DISTINCT Anweisung hinzu.
     * @param string $columns [string:'column1, column2, column3']
     * - Betroffene Spalten die mit DISTINCT ausgeführt werden sollen.
     * @return Database $this
     */
    public function distinct(string $columns)
    {

        $columns = explode(',', $columns);
        $distinct = [];
        foreach ($columns as $column) {
            $distinct[] = trim($column);
        }

        $columns = implode(', ', $distinct);

        if (!is_null($this->columns)) {
            $this->columns = "DISTINCT({$columns}), " . $this->columns;
        } else {
            $this->columns = "DISTINCT({$columns})";
        }

        return $this;
    }

    /**
     * Fügt dem SQL Ausdruck eine COUNT Anweisung hinzu.
     * @param string $column 
     * - Spalte auf die COUNT angewendet werden soll.
     * @param string $key 
     * - Schlüssel des Counts
     */
    public function countColumn(string $column, $key = null)
    {
        $column = trim($column);
        if (!is_string($key)) {
            $key = "TotalCount";
        }

        if (!is_null($this->columns)) {
            $this->columns = "COUNT({$column}) AS {$key}, " . $this->columns;
        } else {
            $this->columns = "COUNT({$column}) AS {$key}";
        }
        return $this;
    }

    /**
     * Fügt dem SQL Ausdruck eine WHERE BETWEEEN Anweisung hinzu
     * @param string $column 
     * - Spalte auf die BETWEEN angewendet werden soll.
     * @param string|int|float $value1 
     * - Der Wert zwischen 1 und
     * @param string|int|float $value2 
     * - Der Wert zwischen 2
     * @return Database $this
     */
    public function between(string $column, $value1, $value2)
    {
        if ($this->whereCount == 0) {
            $this->where .= " WHERE ";
            $this->whereCount += 1;
        } else {
            $this->where .= " AND ";
        }

        $this->where .= "{$column} BETWEEN ? AND ?";
        $this->bindValues[] = $value1;
        $this->bindValues[] = $value2;
        return $this;
    }

    /**
     * Fügt dem SQL Ausdruck eine JOIN Anweisung hinzu
     * @param string $joinType 
     * - Der Typ des Joins der verwendet werden soll
     * @param string $table 
     * - Die Tabelle die angefügt werden soll
     * @return Database $this
     */
    public function join(
        string $joinType,
        string $table
    ) {
        $this->isJoin = true;
        if ($this->join !== null) {
            $this->join .= " $joinType JOIN ";
        } else {
            $this->join = "\n $joinType JOIN ";
        }

        $this->join .= "{$table}";
        return $this;
    }

    /**
     * Fügt dem SQL Ausdruck SELECT Anweisungen hinzu in Beziehung zum join
     * @param string $columns [string:'column1, column2, column3'] 
     * - Betroffene Spalten die mit SELECT ausgeführt werden sollen.
     * @return Database $this
     */
    public function selectFromJoin(string $columns, string $joinTable)
    {
        $columns = explode(',', $columns);

        foreach ($columns as $key => $column) {
            $column = trim($column);
            $columns[$key] = trim("{$joinTable}.{$column}");
        }

        $columns = implode(', ', $columns);

        if ($this->columns !== null) {
            $this->columns .= ", {$columns}";
        } else {
            $this->columns = "{$columns}";
        }
        return $this;
    }

    /**
     * Fügt dem SQL Ausdruck eine ON Anweisung hinzu
     * @param string $joinSourceTable
     * - Der Tabellenname von dem $joinTable Verknüpft werden soll.
     * @param string $joinSourceColumn 
     * - Die Tabellenspalte die mit $joinColumn Verknüpft werden soll.
     * @param string $joinTable 
     * - Die Tabelle die verknüpft wird
     * @param string $joinColumn 
     * - Die tabellenspalte die verknüpft werden soll.
     * @return Database $this
     * @throws \Exception 
     * - Wenn ->on() vor join() aufgerufen wurde.
     */
    public function on(
        string $joinSourceTable,
        string $joinSourceColumn,
        string $joinTable,
        string $joinColumn
    ) {
        if ($this->isJoin) {
            $this->join .= " ON ({$joinSourceTable}.{$joinSourceColumn} = {$joinTable}.{$joinColumn}) \n";
            return $this;
        } else {
            throw new DatabaseException(DatabaseException::CODE_JOIN_FAIL);
        }
    }

    /**
     * Füge eine Where Anweisungen AND- Verknüpft hinzu
     * @param mixed $args 
     * - Der Funktion können bis zu 3 Parameter übergeben werden.
     * -- Übergebe Array e.g ['first_name', 'Julian'], ['age','>=', 20], ['id',1]
     * @return Database $this
     */
    public function where()
    {
        $num_args = func_num_args();
        $args = func_get_args();

        if (is_null($args[0])) {
            return $this;
        }

        if ($this->whereCount == 0) {
            $this->where .= " WHERE ";
            $this->whereCount += 1;
        } else {
            $this->where .= " AND ";
        }

        $this->isOrWhere = false;

        if ($num_args == 1) {
            if (is_numeric($args[0])) {
                $this->where .= "id = ?";
                $this->bindValues[] =  $args[0];
            } elseif (is_array($args[0])) {
                $arr = $args[0];
                $count_arr = count($arr);
                $x = 0;

                foreach ($arr as  $param) {
                    if ($x == 0) {
                        $x++;
                    } else {
                        if ($this->isOrWhere) {
                            $this->where .= " Or ";
                        } else {
                            $this->where .= " AND ";
                        }

                        $x++;
                    }
                    $count_param = count($param);
                    if ($count_param == 1) {
                        $this->where .= "id = ?";
                        $this->bindValues[] =  $param[0];
                    } elseif ($count_param == 2) {
                        $operators = explode(',', "=,>,<,>=,>=,<>");
                        $operatorFound = false;

                        foreach ($operators as $operator) {
                            if (strpos($param[0], $operator) !== false) {
                                $operatorFound = true;
                                break;
                            }
                        }

                        if ($operatorFound) {
                            $this->where .= $param[0] . " ?";
                        } else {
                            $this->where .= "" . trim($param[0]) . " = ?";
                        }

                        $this->bindValues[] =  $param[1];
                    } elseif ($count_param == 3) {
                        $this->where .= "" . trim($param[0]) . " " . $param[1] . " ?";
                        $this->bindValues[] =  $param[2];
                    }
                }
            }
            # ende falls es array ist
        } elseif ($num_args == 2) {
            $operators = explode(',', "=,>,<,>=,>=,<>");
            $operatorFound = false;
            foreach ($operators as $operator) {
                if (strpos($args[0], $operator) !== false) {
                    $operatorFound = true;
                    break;
                }
            }

            if ($operatorFound) {
                $this->where .= $args[0] . " ?";
            } else {
                $this->where .= "" . trim($args[0]) . " = ?";
            }

            $this->bindValues[] =  $args[1];
        } elseif ($num_args == 3) {

            $this->where .= "" . trim($args[0]) . " " . $args[1] . " ?";
            $this->bindValues[] =  $args[2];
        }

        return $this;
    }

    /**
     * Füge eine Where Anweisungen OR- Verknüpft hinzu
     * @param mixed $args 
     * - Der Funktion können bis zu 3 Parameter übergeben werden.
     * -- Übergebe Array e.g ['first_name', 'Julian'], ['age','>=', 20], ['id',1]
     * @return Database $this
     */
    public function orWhere()
    {
        $num_args = func_num_args();
        $args = func_get_args();

        if (is_null($args[0])) {
            return $this;
        }

        if ($this->whereCount == 0) {
            $this->where .= " WHERE ";
            $this->whereCount += 1;
        } else {
            $this->where .= " OR ";
        }
        $this->isOrWhere = true;

        if ($num_args == 1) {
            if (is_numeric($args[0])) {
                $this->where .= "id = ?";
                $this->bindValues[] =  $args[0];
            } elseif (is_array($args[0])) {
                $arr = $args[0];
                $count_arr = count($arr);
                $x = 0;

                foreach ($arr as  $param) {
                    if ($x == 0) {
                        $x++;
                    } else {
                        if ($this->isOrWhere) {
                            $this->where .= " Or ";
                        } else {
                            $this->where .= " AND ";
                        }

                        $x++;
                    }
                    $count_param = count($param);
                    if ($count_param == 1) {
                        $this->where .= "id = ?";
                        $this->bindValues[] =  $param[0];
                    } elseif ($count_param == 2) {
                        $operators = explode(',', "=,>,<,>=,>=,<>");
                        $operatorFound = false;

                        foreach ($operators as $operator) {
                            if (strpos($param[0], $operator) !== false) {
                                $operatorFound = true;
                                break;
                            }
                        }

                        if ($operatorFound) {
                            $this->where .= $param[0] . " ?";
                        } else {
                            $this->where .= "" . trim($param[0]) . " = ?";
                        }

                        $this->bindValues[] =  $param[1];
                    } elseif ($count_param == 3) {
                        $this->where .= "" . trim($param[0]) . " " . $param[1] . " ?";
                        $this->bindValues[] =  $param[2];
                    }
                }
            }
            # ende falls es array ist
        } elseif ($num_args == 2) {
            $operators = explode(',', "=,>,<,>=,>=,<>");
            $operatorFound = false;
            foreach ($operators as $operator) {
                if (strpos($args[0], $operator) !== false) {
                    $operatorFound = true;
                    break;
                }
            }

            if ($operatorFound) {
                $this->where .= $args[0] . " ?";
            } else {
                $this->where .= "" . trim($args[0]) . " = ?";
            }

            $this->bindValues[] =  $args[1];
        } elseif ($num_args == 3) {

            $this->where .= "" . trim($args[0]) . " " . $args[1] . " ?";
            $this->bindValues[] =  $args[2];
        }

        return $this;
    }

    /**
     * Füge eine Where In Anweisung hinzu
     * @param string $columns
     * - Spalten 
     * @param array $data 
     * - Daten die enthalten sein sollen
     * @param bool $and 
     * - Operator, And oder Or Verknüpfen
     */
    public function whereIn(string $columns, array $data, $and = true)
    {
        $columns = explode(',', $columns);
        $in = [];
        foreach ($columns as $column) {
            $in[] = trim($column);
        }

        $columns = implode(', ', $in);

        if ($this->whereCount == 0) {
            $this->where .= " WHERE ";
            $this->whereCount += 1;
        } else {
            $this->where .= ($and) ? " AND " : " OR ";
        }

        $data = join(',', $data);
        $this->where .= "{$columns} IN ($data)";

        return $this;
    }

    /**
     * Füge eine Where Match Anweisung hinzu
     * @param string $columns
     * - Spalten 
     * @param string|array|object $toString 
     * - Daten die enthalten sein sollen als Array, String oder Objekte mit __toString() Methode. 
     * @param string $operator 
     * - Operator, falls Where Bedinung bereits vorhanden ist. And oder Or Verknüpfen
     */
    public function whereMatch(string $columns, $toString, $and = true)
    {
        $columns = explode(',', $columns);
        $match = [];
        foreach ($columns as $column) {
            $match[] = trim($column);
        }

        $columns = implode(', ', $match);

        if (is_array($toString)) {
            $string = join(" ", $toString);
        } else {
            $string = (string) $toString;
        }

        if ($this->whereCount == 0) {
            $this->where .= " WHERE ";
            $this->whereCount += 1;
        } else {
            $this->where .= ($and) ? " AND " : " OR ";
        }

        $this->where = "MATCH ({$columns}) AGAINST ('{$string}')";

        return $this;
    }

    /**
     * Erhalte das Ergebnis als Collection Objekt
     * @return Collection $collection
     */
    public function get()
    {
        $begin = microtime(true);
        $this->assimbleQuery();
        $this->getSQL = $this->sql;

        $stmt = $this->dbh->prepare($this->sql);
        $stmt->execute($this->bindValues);
        $this->rowCount = $stmt->rowCount();

        $rows = $stmt->fetchAll(\PDO::FETCH_CLASS, 'JacksonJeans\Item');
        $collection = [];
        $collection = new Collection;
        $x = 0;
        foreach ($rows as $row) {
            $collection->offsetSet($x++, $row);
        }
        $this->processingTime = $t = floatval(sprintf('%.4f', microtime(true) - $begin));
        $this->totalTime += $t;
        return $collection;
    }

    /**
     * Erhalte das Ergebnis als Array (ist etwas schneller)
     * @return array $stmt->fetchAll()
     */
    public function QGet()
    {
        $begin = microtime(true);
        $this->assimbleQuery();
        $this->getSQL = $this->sql;

        $stmt = $this->dbh->prepare($this->sql);
        $stmt->execute($this->bindValues);
        $this->rowCount = $stmt->rowCount();
        $this->processingTime = $t = floatval(sprintf('%.4f', microtime(true) - $begin));
        $this->totalTime += $t;
        return $stmt->fetchAll();
    }

    /**
     * Assimiliere die Abfragezeichenfolge
     */
    private function assimbleQuery()
    {
        if ($this->columns !== null) {
            $select = $this->columns;
        } else {
            $select = "*";
        }

        $this->sql = "SELECT $select FROM $this->table";

        if ($this->isJoin) {
            $this->sql .= $this->join;
        }

        if ($this->where !== null) {
            $this->sql .= $this->where;
        }

        if ($this->orderBy !== null) {
            $this->sql .= $this->orderBy;
        }

        if ($this->limit !== null) {
            $this->sql .= $this->limit;
        }
    }

    /**
     * Limitiere die Anfrage durch das setzten des Limits und Offsets
     * @param int $limit 
     * @param int $offset
     * @return Database $this
     */
    public function limit($limit, $offset = null)
    {
        if ($offset == null) {
            $this->limit = " LIMIT {$limit}";
        } else {
            $this->limit = " LIMIT {$limit} OFFSET {$offset}";
        }

        return $this;
    }

    /**
     * Ergebnis in einer bestimmten Reihenfolge nach einem Spaltennamen sortieren
     * @param string $field_name 
     * - Der Spaltenname, nach dem Sie das Ergebnis sortieren wollen.
     * @param string $order 
     * - bestimmt, in welcher Reihenfolge Sie die Ergebnisse sehen wollen, ob 'ASC' oder 'DESC'.
     * @return Datanase $this 
     * - es liefert ein Database-Objekt
     */
    public function orderBy($field_name, $order = 'ASC')
    {
        $field_name = trim($field_name);

        $order =  trim(strtoupper($order));

        // validate it's not empty and have a proper valuse
        if ($field_name !== null && ($order == 'ASC' || $order == 'DESC')) {
            if ($this->orderBy == null) {
                $this->orderBy = " ORDER BY $field_name $order";
            } else {
                $this->orderBy .= ", $field_name $order";
            }
        }

        return $this;
    }

    /**
     * Blätter durch die Seitenergebnisse und gebe die Ergebnisse als Collection zurück
     * @param int $page 
     * - Seite 
     * @param int $limit
     * - Limit 
     * @return Collection $collection
     */
    public function paginate($page, $limit)
    {
        $begin = microtime(true);

        # Query assimilieren
        $countSQL = "SELECT COUNT(*) FROM $this->table";
        if ($this->where !== null) {
            $countSQL .= $this->where;
        }

        $stmt = $this->dbh->prepare($countSQL);
        $stmt->execute($this->bindValues);
        $totalRows = $stmt->fetch(\PDO::FETCH_NUM)[0];

        $offset = ($page - 1) * $limit;
        # Refresh Pagination Array
        $this->pagination['currentPage'] = $page;
        $this->pagination['lastPage'] = ceil($totalRows / $limit);
        $this->pagination['nextPage'] = $page + 1;
        $this->pagination['previousPage'] = $page - 1;
        $this->pagination['totalRows'] = $totalRows;
        # falls letzte Seite == aktuelle ist
        if ($this->pagination['lastPage'] ==  $page) {
            $this->pagination['nextPage'] = null;
        }
        if ($page == 1) {
            $this->pagination['previousPage'] = null;
        }
        if ($page > $this->pagination['lastPage']) {
            return [];
        }

        $this->assimbleQuery();

        $sql = $this->sql . " LIMIT {$limit} OFFSET {$offset}";
        $this->getSQL = $sql;

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($this->bindValues);
        $this->rowCount = $stmt->rowCount();


        $rows = $stmt->fetchAll(\PDO::FETCH_CLASS, 'JacksonJeans\Item');
        $collection = [];
        $collection = new Collection;
        $x = 0;
        foreach ($rows as $key => $row) {
            $collection->offsetSet($x++, $row);
        }
        $this->processingTime = $t = floatval(sprintf('%.4f', microtime(true) - $begin));
        $this->totalTime += $t;
        return $collection;
    }

    /**
     * Zähle die Ergebnisse einer Tabelle
     * @return int $stmt->fetch(\PDO::FETCH_NUM)[0]
     */
    public function count()
    {
        $begin = microtime(true);
        # Query assimilieren
        $countSQL = "SELECT COUNT(*) FROM $this->table";

        if ($this->where !== null) {
            $countSQL .= $this->where;
        }

        if ($this->limit !== null) {
            $countSQL .= $this->limit;
        }
        // Ende vom assimilieren

        $stmt = $this->dbh->prepare($countSQL);
        $stmt->execute($this->bindValues);

        $this->getSQL = $countSQL;
        $this->processingTime = $t = floatval(sprintf('%.4f', microtime(true) - $begin));
        $this->totalTime += $t;
        return $stmt->fetch(\PDO::FETCH_NUM)[0];
    }

    /**
     * Blätter durch die Seitenergebnisse und gebe die Ergebnisse als Array zurück
     * @param int $page 
     * - Seite 
     * @param int $limit
     * - Limit 
     * @return array $stmt->fetchAll();
     */
    public function QPaginate($page, $limit)
    {
        $begin = microtime(true);

        # Query assimilieren
        $countSQL = "SELECT COUNT(*) FROM $this->table";
        if ($this->where !== null) {
            $countSQL .= $this->where;
        }

        $stmt = $this->dbh->prepare($countSQL);
        $stmt->execute($this->bindValues);
        $totalRows = $stmt->fetch(\PDO::FETCH_NUM)[0];

        $offset = ($page - 1) * $limit;
        # Refresh Pagination Array
        $this->pagination['currentPage'] = $page;
        $this->pagination['lastPage'] = ceil($totalRows / $limit);
        $this->pagination['nextPage'] = $page + 1;
        $this->pagination['previousPage'] = $page - 1;
        $this->pagination['totalRows'] = $totalRows;
        # Falls aktuelle == letzte Seite ist
        if ($this->pagination['lastPage'] ==  $page) {
            $this->pagination['nextPage'] = null;
        }
        if ($page == 1) {
            $this->pagination['previousPage'] = null;
        }
        if ($page > $this->pagination['lastPage']) {
            return [];
        }

        $this->assimbleQuery();

        $sql = $this->sql . " LIMIT {$limit} OFFSET {$offset}";
        $this->getSQL = $sql;

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($this->bindValues);
        $this->rowCount = $stmt->rowCount();
        $this->processingTime = $t = floatval(sprintf('%.4f', microtime(true) - $begin));
        $this->totalTime += $t;
        return $stmt->fetchAll();
    }

    /**
     * Erhalte die aktuellen Seitenumbruchsinformationen
     * @return array
     */
    public function PaginationInfo()
    {
        return $this->pagination;
    }

    /**
     * Erhalte den SQL-Ausdruck
     */
    public function getSQL()
    {
        return $this->getSQL;
    }

    /**
     * Erhalte die Anzahl betroffener Datensätze. Das gleiche wie rowCount()
     * @return int
     */
    public function getCount()
    {
        return $this->rowCount;
    }

    /**
     * Erhalte die Anzahl betroffener Datensätze
     */
    public function rowCount()
    {
        return $this->rowCount;
    }

    /**
     * Gibt die Verarbeitungszeit aller Anfragen zurück
     * @return float
     */
    public function getTotalTime()
    {
        return $this->totalTime;
    }

    /**
     * Gibt die Verarbeitungszeit der letzten Anfrage zurück
     * @return float
     */
    public function getTime()
    {
        return $this->processingTime;
    }
}
