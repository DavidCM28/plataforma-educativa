<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Session\Handlers\BaseHandler;
use CodeIgniter\Session\Handlers\FileHandler;

class Session extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Session Driver
     * --------------------------------------------------------------------------
     */
    public string $driver = FileHandler::class;

    /**
     * --------------------------------------------------------------------------
     * Session Cookie Name
     * --------------------------------------------------------------------------
     */
    public string $cookieName = 'ci_session';

    /**
     * --------------------------------------------------------------------------
     * Session Expiration (en segundos)
     * --------------------------------------------------------------------------
     */
    public int $expiration = 7200;

    /**
     * --------------------------------------------------------------------------
     * Session Save Path
     * --------------------------------------------------------------------------
     * Para el FileHandler, debe ser una carpeta existente y escribible.
     */
    public string $savePath = WRITEPATH . 'session'; // asegúrate que exista "writable/session/"

    /**
     * --------------------------------------------------------------------------
     * Match IP
     * --------------------------------------------------------------------------
     */
    public bool $matchIP = false;

    /**
     * --------------------------------------------------------------------------
     * Tiempo para regenerar el ID de sesión (en segundos)
     * --------------------------------------------------------------------------
     */
    public int $timeToUpdate = 300;

    /**
     * --------------------------------------------------------------------------
     * Regenerate Destroy
     * --------------------------------------------------------------------------
     */
    public bool $regenerateDestroy = false;

    /**
     * --------------------------------------------------------------------------
     * Database Group (solo si usas sesiones en BD)
     * --------------------------------------------------------------------------
     */
    public ?string $DBGroup = null;

    /**
     * --------------------------------------------------------------------------
     * Lock Retry Interval (microseconds)
     * --------------------------------------------------------------------------
     */
    public int $lockRetryInterval = 100_000;

    /**
     * --------------------------------------------------------------------------
     * Lock Max Retries
     * --------------------------------------------------------------------------
     */
    public int $lockMaxRetries = 300;

    /**
     * --------------------------------------------------------------------------
     * ⚙️ Ajustes de Cookie (fix sesiones AJAX con fetch)
     * --------------------------------------------------------------------------
     */
    public string $cookieDomain = 'localhost';   // Dominio del sitio
    public string $cookiePath = '/';           // Rango de validez
    public bool $cookieSecure = false;         // false para localhost
    public string $cookieSameSite = 'Lax';       // permite uso con fetch() sin romper sesión
}
