<select id="php_version" name="php_version">
<?php
foreach ( $this->options as $version ) :
	printf( '<option value="%1$s">%1$s</option>', $version );
endforeach;
?>
</select>
