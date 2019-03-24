<?php
/**
 * ASMP WordPress Integration Plugin.
 *
 * @package   ASMP\WordPressIntegration
 * @license   MIT
 * @link      https://www.alainschlesser.com/asmp
 *
 * -----------------------------------------------------------------------------
 * -- 1. Provide the plugin meta information that WordPress needs             --
 * -----------------------------------------------------------------------------
 *
 * @wordpress-plugin
 * Plugin Name:  ASMP WordPress Integration Plugin
 * Plugin URI:   https://www.alainschlesser.com/asmp
 * Description:  WordPress Integration Plugin for ASMP.
 * Version:      0.1.0
 * Requires PHP: 7.2
 * Text Domain:  asmp
 * Domain Path:  /languages
 * License:      MIT
 * License URI:  https://opensource.org/licenses/MIT
 */

namespace ASMP\WordPressIntegration;

/*
 * This is the plugin's bootstrap file. It serves three main purposes:
 *  1. Provide the plugin meta information that WordPress needs;
 *  2. Prepare the environment so that it is ready to execute our OOP code;
 *  3. Instantiate and kick off our "composition root" (our 'Plugin' class).
 *
 * The bootstrap file should not do anything else, so that we have a clean
 * separation between a.) code that needs to be run sequentially and produces
 * side-effects and b.) declarations that can be taken out of contexts for
 * testing and reuse and have no side-effects.
 *
 * Anything past this bootstrap file should be autoloadable classes, interfaces
 * or traits without any side-effects.
 */

/*
 * As this is the only PHP file having side effects, we need to provide a
 * safeguard, so we want to make sure this file is only run from within
 * WordPress and cannot be directly called through a web request.
 */
if ( ! defined( 'ABSPATH' ) ) {
	die();
}



/*
 * -----------------------------------------------------------------------------
 * -- 2. Prepare the environment so that it is ready to execute our OOP code  --
 * -----------------------------------------------------------------------------
 */

/*
 * We try to load the Composer if it exists.
 * If it doesn't exist, we fall back to a basic bundled autoloader
 * implementation. This allows us to just use the plugin as-is without requirin
 * the 'composer install' step.
 * Note: If you use Composer not only for autoloading, but also including
 * dependencies needed in production, the 'composer install' becomes mandatory
 * and the fallback autoloader should probably be removed.
 */
$composer_autoloader = __DIR__ . '/vendor/autoload.php';

if ( is_readable ( $composer_autoloader ) ) {
	require $composer_autoloader;
}

if ( ! class_exists( __NAMESPACE__ . '\\PluginFactory' ) ) {
	// Composer autoloader apparently was not found, so fall back to our bundled
	// autoloader.
	require_once __DIR__ . '/src/Infrastructure/Autoloader.php';

	( new Infrastructure\Autoloader() )
		->add_namespace( __NAMESPACE__, __DIR__ . '/src' )
		->register();
}



/*
 * -----------------------------------------------------------------------------
 * -- 3. Instantiate and kick off our "composition root" (our 'Plugin' class) --
 * -----------------------------------------------------------------------------
 */

/*
 * We use a factory to instantiate the actual plugin.
 * The factory keeps the object as a shared instance, so that you can also
 * get outside access to that same plugin instance through the factory.
 * This is similar to a Singleton, but without all the drawbacks the Singleton
 * anti-pattern brings along.
 * For more information on why to avoid a Singleton, read:
 * https://www.alainschlesser.com/singletons-shared-instances/
 */
$plugin = PluginFactory::create();

/*
 * We register activation and deactivation hooks by using closures, as these
 * need static access to work correctly.
 */
\register_activation_hook( __FILE__, function () use ( $plugin ) {
	$plugin->activate();
} );

\register_deactivation_hook( __FILE__, function () use ( $plugin ) {
	$plugin->deactivate();
} );

/*
 * Finally, we run the plugin's register method to Hook the plugin into the
 * WordPress request lifecycle.
 */
$plugin->register();
