<?php declare( strict_types=1 );

/**
 * ASMP WordPress Integration Plugin.
 *
 * @package   ASMP\WordPressIntegration
 * @license   MIT
 * @link      https://www.alainschlesser.com/asmp
 */

namespace ASMP\WordPressIntegration\Infrastructure\View;

use ASMP\WordPressIntegration\Exception\FailedToLoadView;
use ASMP\WordPressIntegration\Exception\InvalidPath;
use ASMP\WordPressIntegration\Infrastructure\View;
use ASMP\WordPressIntegration\Infrastructure\ViewFactory;

/**
 * A simplified implementation of a renderable view object.
 */
class SimpleView implements View {

	/**
	 * Extension to use for view files.
	 */
	protected const VIEW_EXTENSION = 'php';

	/**
	 * Path to the view file to render.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Internal storage for passed-in context.
	 *
	 * @var array
	 */
	protected $_context_ = [];

	/** @var ViewFactory */
	protected $view_factory;

	/**
	 * Instantiate a SimpleView object.
	 *
	 * @param string      $path         Path to the view file to render.
	 * @param ViewFactory $view_factory View factory instance to use.
	 * @throws InvalidPath If an invalid Path was passed into the View.
	 */
	public function __construct( string $path, ViewFactory $view_factory ) {
		$this->path         = $this->validate( $path );
		$this->view_factory = $view_factory;
	}

	/**
	 * Render the current view with a given context.
	 *
	 * @param array $context Context in which to render.
	 *
	 * @return string Rendered HTML.
	 * @throws FailedToLoadView If the View path could not be loaded.
	 */
	public function render( array $context = [] ): string {

		// Add context to the current instance to make it available within the
		// rendered view.
		foreach ( $context as $key => $value ) {
			$this->$key = $value;
		}

		// Add entire context as array to the current instance to pass onto
		// partial views.
		$this->_context_ = $context;

		// Save current buffering level so we can backtrack in case of an error.
		// This is needed because the view itself might also add an unknown
		// number of output buffering levels.
		$buffer_level = \ob_get_level();
		\ob_start();

		try {
			include $this->path;
		} catch ( \Exception $exception ) {
			// Remove whatever levels were added up until now.
			while ( \ob_get_level() > $buffer_level ) {
				\ob_end_clean();
			}
			throw FailedToLoadView::from_view_exception(
				$this->path,
				$exception
			);
		}

		return \ob_get_clean();
	}

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
	public function render_partial( string $path, array $context = null ): string {
		$view = $this->view_factory->create( $path );

		return $view->render( $context ?: $this->_context_ );
	}

	/**
	 * Validate a path.
	 *
	 * @param string $path Path to validate.
	 *
	 * @return string Validated path.
	 * @throws InvalidPath If an invalid path was passed into the View.
	 */
	protected function validate( string $path ): string {
		$path = $this->check_extension( $path, static::VIEW_EXTENSION );
		$path = $this->ensure_trailing_slash( \dirname( __DIR__, 3 ) ) . $path;

		if ( ! \is_readable( $path ) ) {
			throw InvalidPath::from_path( $path );
		}

		return $path;
	}

	/**
	 * Check that the path has the correct extension.
	 *
	 * Optionally adds the extension if none was detected.
	 *
	 * @param string $path      Path to check the extension of.
	 * @param string $extension Extension to use.
	 *
	 * @return string Path with correct extension.
	 */
	protected function check_extension( string $path, string $extension ): string {
		$detected_extension = \pathinfo( $path, PATHINFO_EXTENSION );

		if ( $extension !== $detected_extension ) {
			$path .= '.' . $extension;
		}

		return $path;
	}

	/**
	 * Ensure the path has a trailing slash.
	 *
	 * @param string $path Path to maybe add a trailing slash.
	 * @return string Path with trailing slash.
	 */
	protected function ensure_trailing_slash( string $path ): string {
		return \rtrim( $path, '/\\' ) . '/';
	}
}
