<?php

namespace JacksonJeans;

/**
 * DatabaseInterface - Interface
 * 
 * @category    Class
 * @package     GIDUTEX
 * @author      Julian Tietz <julian.tietz@gidutex.de>
 * @license     Julian Tietz <julian.tietz@gidutex.de>
 * @link        https://gidutex.de
 * @version     1.0
 */
interface DatabaseInterface
{
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
     * @return Database $this
     */
    public function __construct(array $config, $driver);

    /**
     * Query-Methode
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
    public function query(string $query, $args, $object);

    /**
     * Assimiliere die Anforderung und führe sie aus.
     * @return int $stmt->rowCount() 
     * - Anzahl betroffener Datensätze
     */
    public function exec();

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
    public function delete(string $table_name, $id = null);

    /**
     * UPDATE Abfragezeichenfolge
     * @param string $table_name 
     * - Betroffene Tabelle.
     * @param array $fields 
     * - Felder mit Spalten und Werten die aktualisiert werden sollen.
     * @param int|array $id 
     * - [[spalte,operator,wert],[spalte,wert],[wert (wenn nummeric, ist spalte = 'id')]]
     * - Betroffener Datensätze als Where Klausel:
     * @return int $stmt->rowCount() 
     * - Anzahl betroffener Datensätze
     */
    public function update(string $table_name, $fields = [], $id = null);

    /**
     * INSERT Abfragezeichenfolge
     * @param string $table_name 
     * - Betroffene Tabelle.
     * @param array $fields 
     * - Felder mit Spalten und Werten die aktualisiert werden sollen.
     * @return int $this->dbh->lastInsertId()
     * - Gibt bei Erfolg die letzte InsertId zurück.
     */
    public function insert(string $table_name, $fields = []);

    /**
     * Setzt die betroffene Tabelle. E.g für eine SELECT-Ausführung (Siehe Doku)
     * @param string $table_name
     * - Betroffene Tabelle 
     * @return Database $this
     */
    public function table(string $table_name);

    /**
     * Fügt dem SQL Ausdruck eine DISTINCT Anweisung hinzu.
     * @param string $columns [string:'column1, column2, column3']
     * - Betroffene Spalten die mit DISTINCT ausgeführt werden sollen.
     * @return Database $this
     */
    public function distinct(string $columns);

    /**
     * Fügt dem SQL Ausdruck SELECT Anweisungen hinzu.
     * @param string $columns [string:'column1, column2, column3'] 
     * - Betroffene Spalten die mit SELECT ausgeführt werden sollen.
     * @return Database $this
     */
    public function select(string $columns);

    /**
     * Fügt dem SQL Ausdruck eine JOIN Anweisung hinzu
     * @param string $joinType 
     * - Der Typ des Joins der verwendet werden soll
     * @param string $table 
     * - Die Tabelle die angefügt werden soll
     * @return Database $this
     */
    public function join(string $joinType, string $table);

    /**
     * Füge eine Where Anweisungen AND- Verknüpft hinzu
     * @param mixed $args 
     * - Der Funktion können bis zu 3 Parameter übergeben werden.
     * -- Übergebe Array e.g ['first_name', 'Julian'], ['age','>=', 20], ['id',1]
     * @return Database $this
     */
    public function where();

    /**
     * Füge eine Where Anweisungen OR- Verknüpft hinzu
     * @param mixed $args 
     * - Der Funktion können bis zu 3 Parameter übergeben werden.
     * -- Übergebe Array e.g ['first_name', 'Julian'], ['age','>=', 20], ['id',1]
     * @return Database $this
     */
    public function orWhere();

    /**
     * Erhalte das Ergebnis als Collection Objekt
     * @return Collection $collection
     */
    public function get();

    /**
     * Limitiere die Anfrage durch das setzten des Limits und Offsets
     * @param int $limit 
     * @param int $offset
     * @return Database $this
     */
    public function limit($limit, $offset = null);

    /**
     * Ergebnis in einer bestimmten Reihenfolge nach einem Spaltennamen sortieren
     * @param string $field_name 
     * - Der Spaltenname, nach dem Sie das Ergebnis sortieren wollen.
     * @param string $order 
     * - bestimmt, in welcher Reihenfolge Sie die Ergebnisse sehen wollen, ob 'ASC' oder 'DESC'.
     * @return Database $this 
     * - es liefert ein Database-Objekt
     */
    public function orderBy($field_name, $order = 'ASC');

    /**
     * Blätter durch die Seitenergebnisse und gebe die Ergebnisse als Collection zurück
     * @param int $page 
     * - Seite 
     * @param int $limit
     * - Limit 
     * @return Collection $collection
     */
    public function paginate($page, $limit);

    /**
     * Erhalte die Anzahl betroffener Datensätze. Das gleiche wie rowCount()
     * @return int
     */
    public function getCount();

    /**
     * Zähle die Ergebnisse einer Tabelle
     * @return int $stmt->fetch(\PDO::FETCH_NUM)[0]
     */
    public function count();

    /**
     * Erhalte die aktuellen Seitenumbruchsinformationen
     * @return array
     */
    public function PaginationInfo();
}
