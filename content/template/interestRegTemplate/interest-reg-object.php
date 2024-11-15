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
		if ( ! empty( $_POST['edu-interest-nonce'] ) && wp_verify_nonce( $_POST['edu-interest-nonce'], 'edu-object-interest' ) && isset( $_POST['act'] ) && 'objectInquiry' === sanitize_text_field( $_POST['act'] ) ) {
			include_once 'send-object-inquiry.php';
		}

		$course_id     = $wp_query->query_vars['courseId'];
		$group_by_city = EDU()->is_checked( 'eduadmin-groupEventsByCity', false );
		$fetch_months  = EDU()->get_option( 'eduadmin-monthsToFetch', 6 );
		if ( ! is_numeric( $fetch_months ) ) {
			$fetch_months = 6;
		}

		$edo = json_decode( EDUAPIHelper()->GetCourseDetailInfo( $course_id, $fetch_months, $group_by_city ), true );

		$selected_course = false;
		$name            = '';
		if ( $edo ) {
			$name            = ( ! empty( $edo['CourseName'] ) ? $edo['CourseName'] : $edo['InternalCourseName'] );
			$selected_course = $edo;
		}

		if ( ! $selected_course ) {
			?>
			<script>history.go(-1);</script>
			<?php
			die();
		}

		?>
		<div class="eduadmin">
			<a href="javascript://" onclick="eduGlobalMethods.GoBack('../', event);"
			   class="backLink"><?php echo esc_html_x( '« Go back', 'frontend', 'eduadmin-booking' ); ?></a>
			<div class="title">
				<?php if ( ! empty( $selected_course['ImageUrl'] ) ) : ?>
					<img src="<?php echo esc_url( $selected_course['ImageUrl'] ); ?>" class="courseImage" />
				<?php endif; ?>
				<h1 class="courseTitle"><?php echo esc_html( $name ); ?> - <?php echo esc_html_x( 'Inquiry', 'frontend', 'eduadmin-booking' ); ?></h1>
			</div>
			<hr />
			<div class="textblock">
				<?php echo esc_html_x( 'Please fill out the form below to send a inquiry to us about this course.', 'frontend', 'eduadmin-booking' ); ?>
				<hr />
				<form action="" method="POST" autocomplete="off">
					<input type="hidden" name="edu-interest-nonce"
					       value="<?php echo esc_attr( wp_create_nonce( 'edu-object-interest' ) ); ?>" />
					<input type="hidden" name="objectid"
					       value="<?php echo esc_attr( $selected_course['CourseTemplateId'] ); ?>" />
					<input type="hidden" name="act" value="objectInquiry" />
					<input type="hidden" name="email" />
					<label class="edu-interest-company">
						<div
							class="inputLabel"><?php echo esc_html_x( 'Customer name', 'frontend', 'eduadmin-booking' ); ?> *
						</div>
						<div class="inputHolder">
							<input type="text" autocomplete="off" required name="edu-companyName" maxlength="100"
							       placeholder="<?php echo esc_attr_x( 'Customer name', 'frontend', 'eduadmin-booking' ); ?>" />
						</div>
					</label>
					<label class="edu-interest-contact">
						<div
							class="inputLabel"><?php echo esc_html_x( 'Contact name', 'frontend', 'eduadmin-booking' ); ?> *
						</div>
						<div class="inputHolder" style="display: flex;">
							<input type="text" autocomplete="off" required name="edu-contactFirstName"
							       class="first-name" maxlength="100"
							       placeholder="<?php echo esc_attr_x( 'Contact first name', 'frontend', 'eduadmin-booking' ); ?>" />
							<input type="text" autocomplete="off" required name="edu-contactLastName" class="last-name"
							       maxlength="100"
							       placeholder="<?php echo esc_attr_x( 'Contact surname', 'frontend', 'eduadmin-booking' ); ?>" />
						</div>
					</label>
					<label class="edu-interest-email">
						<div
							class="inputLabel"><?php echo esc_html_x( 'E-mail address', 'frontend', 'eduadmin-booking' ); ?> *
						</div>
						<div class="inputHolder">
							<input type="email" autocomplete="off" required name="edu-emailAddress" maxlength="200"
							       placeholder="<?php echo esc_attr_x( 'E-mail address', 'frontend', 'eduadmin-booking' ); ?>" />
						</div>
					</label>
					<label class="edu-interest-phone">
						<div
							class="inputLabel"><?php echo esc_html_x( 'Phone number', 'frontend', 'eduadmin-booking' ); ?></div>
						<div class="inputHolder">
							<input type="tel" autocomplete="off" name="edu-phone" maxlength="50"
							       placeholder="<?php echo esc_attr_x( 'Phone number', 'frontend', 'eduadmin-booking' ); ?>" />
						</div>
					</label>
					<label class="edu-interest-mobile">
						<div
							class="inputLabel"><?php echo esc_html_x( 'Mobile number', 'frontend', 'eduadmin-booking' ); ?></div>
						<div class="inputHolder">
							<input type="tel" autocomplete="off" name="edu-mobile" maxlength="50"
							       placeholder="<?php echo esc_attr_x( 'Mobile number', 'frontend', 'eduadmin-booking' ); ?>" />
						</div>
					</label>
					<label class="edu-interest-notes">
						<div
							class="inputLabel"><?php echo esc_html_x( 'Notes', 'frontend', 'eduadmin-booking' ); ?></div>
						<div class="inputHolder">
						<textarea name="edu-notes" autocomplete="off" maxlength="1000"
						          placeholder="<?php echo esc_attr_x( 'Notes', 'frontend', 'eduadmin-booking' ); ?>"></textarea>
						</div>
					</label>
					<?php if ( EDU()->is_checked( 'eduadmin-singlePersonBooking', false ) ) { ?>
						<input type="hidden" name="edu-participants" value="1" />
					<?php } else { ?>
						<label class="edu-interest-participants">
							<div
								class="inputLabel"><?php echo esc_html_x( 'Number of participants', 'frontend', 'eduadmin-booking' ); ?> *
							</div>
							<div class="inputHolder">
								<input type="number" autocomplete="off" name="edu-participants"
								       placeholder="<?php echo esc_attr_x( 'Number of participants', 'frontend', 'eduadmin-booking' ); ?>" />
							</div>
						</label>
					<?php } ?>

					<input type="submit" class="bookButton cta-btn"
					       value="<?php echo esc_attr_x( 'Send inquiry', 'frontend', 'eduadmin-booking' ); ?>" />
				</form>
			</div>
		</div>
		<?php
		$original_title = get_the_title();
		$new_title      = $name . ' | ' . $original_title;
		?>
		<script type="text/javascript">
			(function () {
				var title = document.title;
				title = title.replace('<?php echo esc_js( $original_title ); ?>', '<?php echo esc_js( $new_title ); ?>');
				document.title = title;
			})();
		</script>
		<?php
	}
}

$out = ob_get_clean();

return $out;
