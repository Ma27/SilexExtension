<?php
namespace Ma27\Kernel\Value;

/**
 * Enum with keys containing the keys of the
 * app container
 *
 * @package Ma27\Kernel\Controller
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright 2014 - 2018, Maximilian Bosch
 * @license MIT
 */
final class KernelKeys
{
    /**
     * Key of the current execution environment in the
     * application container
     *
     * @var string
     *
     * @api
     */
    const ENVIRONMENT     = 'app.environment';

    /**
     * Key of the cache directory for the application
     *
     * @var string
     *
     * @api
     */
    const CACHE_DIR       = 'app.kernel.cache';

    /**
     * Configuration path of the application with all parameter lists
     * and custom files
     *
     * @var string
     *
     * @api
     */
    const CONFIG_DIR      = 'app.kernel.config';

    /**
     * Alias of the directory for your language files to load
     * by any translator
     *
     * @var string
     *
     * @api
     */
    const LOCALE_DIR      = 'app.kernel.locale';

    /**
     * Alias for the lambda which will be triggered after
     * the app dispatching process
     *
     * @var string
     *
     * @api
     */
    const RESPONSE_LAMBDA = 'request.app.handler';

    /**
     * Alias of the root directory of the app which contains configuration directory
     * and other important stuff
     *
     * @var string
     *
     * @api
     */
    const ROOT_DIR        = 'app.kernel.root';

    /**
     * Creates a list of all avaible keys
     *
     * @return string[]
     *
     * @api
     */
    public static function createList()
    {
        return array(
            static::ENVIRONMENT,
            static::CONFIG_DIR,
            static::CONFIG_DIR,
            static::LOCALE_DIR,
            static::RESPONSE_LAMBDA,
            static::ROOT_DIR
        );
    }
} 