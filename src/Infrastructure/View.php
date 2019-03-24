<?php declare( strict_types=1 );

/**
 * ASMP WordPress Integration Plugin.
 *
 * @package   ASMP\WordPressIntegration
 * @license   MIT
 * @link      https://www.alainschlesser.com/asmp
 */

namespace ASMP\WordPressIntegration\Infrastructure;

use ASMP\WordPressIntegration\Exception\FailedToLoadView;
use ASMP\WordPressIntegration\Exception\InvalidPath;

/**
 * The view interface defines how the rendering system works.
 *
 * When you render a view, you can pass a "context" information to it. This
 * context information is that made available to the scope in which the view
 * template is being rendered.
 *
 * As an example, with a default PHP-based view, the context information will be
 * available as properties of the '$this' variable.
 */
interface View extends Renderable {

	/**
	 * Render the current view with a given context.
	 *
	 * @param array $context Context in which to render.
	 *
	 * @return string Rendered HTML.
	 * @throws FailedToLoadView If the view could not be loaded.
	 */
	public function render( array $context = [] ): string;

	/**
	 * Render a partial view.
	 *
	 * This can be used from within a currently rendered view, to include
	 * nested partials.
	 *
	 * The passed-in context is optional, and will fall back to the parent's
	 * context if omitted.
	 *
	 * @param string     $path    Path of the partial to render.
	 * @param array|null $context Context in which to render the partial.
	 *
	 * @return string Rendered HTML.
	 * @throws InvalidPath If the provided path was not valid.
	 * @throws FailedToLoadView If the view could not be loaded.
	 */
	public function render_partial( string $path, array $context = null ): string;
}
