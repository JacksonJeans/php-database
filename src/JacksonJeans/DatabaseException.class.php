<?php

namespace JacksonJeans;

/**
 * DatabaseException
 * 
 * @category    Class
 * @package     GIDUTEX
 * @author      Julian Tietz <julian.tietz@gidutex.de>
 * @license     Julian Tietz <julian.tietz@gidutex.de>
 * @link        https://gidutex.de
 * @version     1.0
 */
class DatabaseException extends \Exception
{

    const CODE_CONNECTION_FAIL = 0x01;
    const CODE_UNKNOWN_ERROR = 0x02;
    const CODE_JOIN_FAIL = 0x03;
    const CODE_INVALID_ARGUMENT = 0x04;
    const CODE_NO_ENVIRONMENT = 0x05;
    const CODE_UNSPPORTED_DRIVER = 0x06;

    /**
     * @var array Vordefinierte Fehlermeldung
     */
    private static $errorMessages = array(
        self::CODE_CONNECTION_FAIL => 'Die Verbindung konnte nicht eingerichtet werden. "\'%s\'".',
        self::CODE_UNKNOWN_ERROR => 'Unbekannter Error: Die Meldung lautet: "\'%s\'".',
        self::CODE_JOIN_FAIL => 'on() kann erst aufgerufen werden, wenn join() aufgerufen wurde.',
        self::CODE_INVALID_ARGUMENT => 'Das Argument "\'%s\'" fehlt oder ist nicht richtig.',
        self::CODE_NO_ENVIRONMENT => 'Es wurde keine oder keine ensprechende Umgebung konfigurtiert.',
        self::CODE_UNSPPORTED_DRIVER => 'Der Datenbank Treiber: \'%s\' wird aktuell nicht unterstützt.'
    );

    /**
     * Neue SplittingException erstellen, fügt automatisch eine aussagekräftige Fehlermeldung hinzu, wenn der Fehlercode bekannt ist.
     *
     * @param int $code
     * - Error Code
     * @param string $splittingSubject 
     * - Die Zeichenfolge, die zu spalten versucht wurde
     */
    public function __construct($code, $splittingSubject = '')
    {
        if (!array_key_exists($code, self::$errorMessages)) {
            $code = self::CODE_UNKNOWN_ERROR;
        }
        $message = sprintf(self::$errorMessages[$code], $splittingSubject);

        parent::__construct($message, $code);
    }
}
