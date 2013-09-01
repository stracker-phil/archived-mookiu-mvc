<div class="<?php echo self::PREFIX; ?>message <?php esc_attr_e( $class ); ?>">
	<?php foreach( $this->notices[ $type ] as $message_data ) : ?>
		<?php if( $message_data[ 'mode' ] == 'user' || $this->debug_mode ) : ?>
			<p><?php echo $message_data[ 'message' ] ; ?></p>
		<?php endif; ?>
	<?php endforeach; ?>
</div>