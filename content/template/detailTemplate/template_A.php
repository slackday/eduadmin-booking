<?php
ob_start();
global $wp_query;
$api_key = EDU()->get_option( 'eduadmin-api-key' );

if ( ! EDU()->api_connection ) {
	echo esc_html_x( 'EduAdmin Booking could not connect to the API', 'frontend', 'eduadmin-booking' );
} else {
	if ( ! $api_key || empty( $api_key ) ) {
		echo 'Please complete the configuration: <a href="' . esc_url( admin_url() . 'admin.php?page=eduadmin-settings' ) . '">EduAdmin - Api Authentication</a>';
	} else {
		include 'course-info.php';
		if ( ! $selected_course ) {
			?>
			<script type="text/javascript">location.href = '<?php echo esc_url( $base_url ); ?>';</script>
			<?php
			exit( 200 );
		}
		?>
		<div class="eduadmin detail-view"
		     data-courseid="<?php echo esc_attr( $selected_course['CourseTemplateId'] ); ?>"
		     data-eventid="<?php echo( isset( $_REQUEST['eid'] ) ? esc_attr( sanitize_text_field( $_REQUEST['eid'] ) ) : '' ); ?>">
			<a href="javascript://" onclick="eduGlobalMethods.GoBack('../', event);"
			   class="backLink"><?php echo esc_html_x( '« Go back', 'frontend', 'eduadmin-booking' ); ?></a>
			<div class="title">
				<?php if ( ! empty( $selected_course['ImageUrl'] ) ) : ?>
					<img src="<?php echo esc_url( $selected_course['ImageUrl'] ); ?>" class="courseImage" />
				<?php endif; ?>
				<h1 class="courseTitle"><?php echo esc_html( $name ); ?>
					<small
						class="courseLevel"><?php echo esc_html( false !== $course_level ? $course_level['Name'] : '' ); ?></small>
				</h1>
			</div>
			<hr />
			<div class="textblock">
				<?php
				if ( ! in_array( 'description', $hide_sections, true ) && ! empty( $selected_course['CourseDescription'] ) ) {
					if ( $show_headers ) {
						?>
						<h3><?php echo esc_html_x( 'Course description', 'frontend', 'eduadmin-booking' ); ?></h3>
					<?php } ?>
					<div>
						<?php
						echo wp_kses_post( $selected_course['CourseDescription'] );
						?>
					</div>
				<?php } ?>
				<?php
				if ( ! in_array( 'goal', $hide_sections, true ) && ! empty( $selected_course['CourseGoal'] ) ) {
					if ( $show_headers ) {
						?>
						<h3><?php echo esc_html_x( 'Course goal', 'frontend', 'eduadmin-booking' ); ?></h3>
					<?php } ?>
					<div>
						<?php
						echo wp_kses_post( $selected_course['CourseGoal'] );
						?>
					</div>
				<?php } ?>
				<?php
				if ( ! in_array( 'target', $hide_sections, true ) && ! empty( $selected_course['TargetGroup'] ) ) {
					if ( $show_headers ) {
						?>
						<h3><?php echo esc_html_x( 'Target group', 'frontend', 'eduadmin-booking' ); ?></h3>
					<?php } ?>
					<div>
						<?php
						echo wp_kses_post( $selected_course['TargetGroup'] );
						?>
					</div>
				<?php } ?>
				<?php
				if ( ! in_array( 'prerequisites', $hide_sections, true ) && ! empty( $selected_course['Prerequisites'] ) ) {
					if ( $show_headers ) {
						?>
						<h3><?php echo esc_html_x( 'Prerequisites', 'frontend', 'eduadmin-booking' ); ?></h3>
					<?php } ?>
					<div>
						<?php
						echo wp_kses_post( $selected_course['Prerequisites'] );
						?>
					</div>
				<?php } ?>
				<?php
				if ( ! in_array( 'after', $hide_sections, true ) && ! empty( $selected_course['CourseAfter'] ) ) {
					if ( $show_headers ) {
						?>
						<h3><?php echo esc_html_x( 'After the course', 'frontend', 'eduadmin-booking' ); ?></h3>
					<?php } ?>
					<div>
						<?php
						echo wp_kses_post( $selected_course['CourseAfter'] );
						?>
					</div>
				<?php } ?>
				<?php
				if ( ! in_array( 'quote', $hide_sections, true ) && ! empty( $selected_course['Quote'] ) ) {
					if ( $show_headers ) {
						?>
						<h3><?php echo esc_html_x( 'Quotes', 'frontend', 'eduadmin-booking' ); ?></h3>
					<?php } ?>
					<div>
						<?php
						echo wp_kses_post( $selected_course['Quote'] );
						?>
					</div>
				<?php } ?>
			</div>
			<div class="eventInformation">
				<?php
				if ( ! in_array( 'time', $hide_sections, true ) && ! empty( $selected_course['StartTime'] ) && ! empty( $selected_course['EndTime'] ) ) {
					?>
					<h3><?php echo esc_html_x( 'Time', 'frontend', 'eduadmin-booking' ); ?></h3>
					<?php
					/* translators: 1: Number of days */
					echo esc_html( ( $selected_course['Days'] > 0 ? sprintf( _n( '%1$d day', '%1$d days', $selected_course['Days'], 'eduadmin-booking' ), $selected_course['Days'] ) . ', ' : '' ) . $selected_course['StartTime'] . ' - ' . $selected_course['EndTime'] );
				}

				if ( ! in_array( 'price', $hide_sections, true ) && ! empty( $prices ) ) {
					?>
					<h3><?php echo esc_html_x( 'Price', 'frontend', 'eduadmin-booking' ); ?></h3>
					<?php
					$currency = EDU()->get_option( 'eduadmin-currency', 'SEK' );

					if ( 1 === count( $prices ) ) {
						echo wp_kses_post( sprintf( '<div class="pricename"><span class="pricename-description">%1$s</span> <span class="pricename-price">%2$s</span></div>', current( $prices )['PriceNameDescription'], edu_get_price( current( $prices )['Price'], $selected_course['ParticipantVat'] ) ) );
					} else {
						foreach ( $prices as $up ) {
							echo wp_kses_post( sprintf( '<div class="pricename"><span class="pricename-description">%1$s</span> <span class="pricename-price">%2$s</span></div>', $up['PriceNameDescription'], edu_get_price( $up['Price'], $selected_course['ParticipantVat'] ) ) );
						}
					}
				}
				?>
			</div>

			<?php
			include 'blocks/event-list.php';
			if ( $allow_interest_reg_object && false !== $object_interest_page ) {
				?>
				<br />
				<div class="inquiry">
					<a class="inquiry-link"
					   href="<?php echo esc_url( $base_url . '/' . make_slugs( $name ) . '__' . $selected_course['CourseTemplateId'] . '/interest/' . edu_get_query_string( '?' ) . '&_=' . time() ); ?>"><?php echo esc_html_x( 'Send inquiry about this course', 'frontend', 'eduadmin-booking' ); ?></a>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	}
}
$out = ob_get_clean();

return $out;
