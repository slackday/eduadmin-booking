<?php
global $wp_query;
$apiUserId = get_option('eduadmin-api_user_id');
$apiHash = get_option('eduadmin-api_hash');

if(!$apiUserId || !$apiHash || (empty($apiUserId) || empty($apiHash)))
{
	echo 'Please complete the configuration: <a href="' . admin_url() . 'admin.php?page=eduadmin-settings">EduAdmin - Api Authentication</a>';
}
else
{
	$api = new EduAdminClient();
	$api->debug = true;
	$token = get_transient('eduadmin-token');
	if(!$token)
	{
		$token = $api->GetAuthToken($apiUserId, $apiHash);
		set_transient('eduadmin-token', $token, HOUR_IN_SECONDS);
	}
	else
	{
		$valid = $api->ValidateAuthToken($token);
		if(!$valid)
		{
			$token = $api->GetAuthToken($apiUserId, $apiHash);
			set_transient('eduadmin-token', $token, HOUR_IN_SECONDS);
		}
	}


	$filtering = new XFiltering();
	$f = new XFilter('ShowOnWeb','=','true');
	$filtering->AddItem($f);

	$edo = $api->GetEducationObject($token, '', $filtering->ToString());


	$selectedCourse = false;
	$name = "";
	foreach($edo as $object)
	{
		$name = (!empty($object->PublicName) ? $object->PublicName : $object->ObjectName);
		$id = $object->ObjectID;
		if(makeSlugs($name) == $wp_query->query_vars['courseSlug'] && $id == $wp_query->query_vars["courseId"])
		{
			$selectedCourse = $object;
			break;
		}
	}
	if(!$selectedCourse)
	{
		?>
		<script>history.go(-1);</script>
		<?php
		die();
	}

	$ft = new XFiltering();
	$f = new XFilter('PeriodStart', '>=', date("Y-m-d 00:00:00", strtotime('now +1 day')));
	$ft->AddItem($f);
	$f = new XFilter('ShowOnWeb', '=', 'true');
	$ft->AddItem($f);
	$f = new XFilter('StatusID', '=', '1');
	$ft->AddItem($f);
	$f = new XFilter('ObjectID', '=', $selectedCourse->ObjectID);
	$ft->AddItem($f);
	$f = new XFilter('LastApplicationDate', '>=', date("Y-m-d H:i:s"));
	$ft->AddItem($f);

	$st = new XSorting();
	$groupByCity = get_option('eduadmin-groupEventsByCity', FALSE);
	$groupByCityClass = "";
	if($groupByCity)
	{
		$s = new XSort('City', 'ASC');
		$st->AddItem($s);
		$groupByCityClass = " noCity";
	}
	$s = new XSort('PeriodStart', 'ASC');
	$st->AddItem($s);

	$events = get_transient('eduadmin-events-object' . $selectedCourse->ObjectID);
	if(!$events)
	{
		$events = $api->GetEvent(
			$token,
			$st->ToString(),
			$ft->ToString()
		);
		set_transient('eduadmin-events-object' . $selectedCourse->ObjectID, $events, 6 * HOUR_IN_SECONDS);
	}

	$showHeaders = get_option('eduadmin-showDetailHeaders', true);
?>
<div class="eduadmin">
	<a href="../" class="backLink"><?php edu_e("« Go back"); ?></a>
	<div class="title">
		<img src="<?php echo $selectedCourse->ImageUrl; ?>" style="max-width: 8em; max-height: 8em; margin-right: 2em;" />
		<h1 style="display: inline-block;"><?php echo $name; ?></h1>
	</div>
	<hr />
	<div class="textblock leftBlock">
		<?php if(!empty($selectedCourse->CourseDescription)) { ?>
			<?php if($showHeaders) { ?>
			<h3><?php edu_e("Course description"); ?></h3>
			<?php } ?>
			<div>
			<?php
				echo $selectedCourse->CourseDescription;
			?>
			</div>
		<?php } ?>
		<?php if(!empty($selectedCourse->CourseGoal)) { ?>
			<?php if($showHeaders) { ?>
		<h3><?php edu_e("Course goal"); ?></h3>
			<?php } ?>
		<div>
		<?php
			echo $selectedCourse->CourseGoal;
		?>
		</div>
		<?php } ?>
		<?php if(!empty($selectedCourse->TargetGroup)) { ?>
			<?php if($showHeaders) { ?>
		<h3><?php edu_e("Target group"); ?></h3>
			<?php } ?>
		<div>
		<?php
			echo $selectedCourse->TargetGroup;
		?>
		</div>
		<?php } ?>
		<?php if(!empty($selectedCourse->Prerequisites)) { ?>
			<?php if($showHeaders) { ?>
		<h3><?php edu_e("Prerequisites"); ?></h3>
			<?php } ?>
		<div>
		<?php
			echo $selectedCourse->Prerequisites;
		?>
		</div>
	</div>
	<div class="textblock rightBlock">
		<?php } ?>
		<?php if(!empty($selectedCourse->CourseAfter)) { ?>
			<?php if($showHeaders) { ?>
		<h3><?php edu_e("After the course"); ?></h3>
			<?php } ?>
		<div>
		<?php
			echo $selectedCourse->CourseAfter;
		?>
		</div>
		<?php } ?>
		<?php if(!empty($selectedCourse->Quote)) { ?>
			<?php if($showHeaders) { ?>
		<h3><?php edu_e("Quotes"); ?></h3>
			<?php } ?>
		<div>
		<?php
			echo $selectedCourse->Quote;
		?>
		</div>
		<?php } ?>
	</div>
	<div class="eventInformation">
		<?php if(!empty($selectedCourse->StartTime) && !empty($selectedCourse->EndTime)) { ?>
		<h3><?php edu_e("Time"); ?></h3>
		<?php
			echo ($selectedCourse->Days > 0 ? sprintf(edu_n('%1$d day', '%1$d days', $selectedCourse->Days), $selectedCourse->Days) . ', ' : '') . date("H:i", strtotime($selectedCourse->StartTime)) . ' - ' . date("H:i", strtotime($selectedCourse->EndTime));
		?>
		<?php } ?>
		<?php

		$occIds = Array();
		$occIds[] = -1;
		foreach($events as $ev)
		{
			$occIds[] = $ev->OccationID;
		}

		$ft = new XFiltering();
		$f = new XFilter('PublicPriceName', '=', 'true');
		$ft->AddItem($f);
		$f = new XFilter('ObjectID', '=', $selectedCourse->ObjectID);
		$ft->AddItem($f);

		$prices = $api->GetObjectPriceName($token, '', $ft->ToString());
		$uniquePrices = Array();
		foreach($prices as $price)
		{
			$uniquePrices[$price->Description] = $price;
		}

		if(count($prices) > 0) {
		?>
		<h3><?php edu_e("Price"); ?></h3>
		<?php
			$currency = get_option('eduadmin-currency', 'SEK');
			if(count($uniquePrices) == 1) {
		?>
		<?php echo sprintf('%1$s %2$s', current($uniquePrices)->Description, convertToMoney(current($uniquePrices)->Price, $currency)); ?>
		<?php
			}
			else
			{
				foreach($uniquePrices as $up)
				{
		?>
		<?php echo sprintf('%1$s %2$s', $up->Description, convertToMoney($up->Price, $currency)); ?><br />
		<?php
				}
			}
		} ?>
	</div>
	<div class="eventDays">
	<?php
	foreach($events as $ev)
	{
		if($groupByCity && $lastCity != $ev->City)
		{
			$i = 0;
			echo '<div class="eventSeparator">' . $ev->City . '</div>';
		}

		if(isset($_REQUEST['eid']))
		{
			if($ev->EventID != $_REQUEST['eid'])
			{
				continue;
			}
		}
	?>
		<div class="eventItem">
			<div class="eventDate<?php echo $groupByCityClass; ?>">
				<?php echo GetStartEndDisplayDate($ev->PeriodStart, $ev->PeriodEnd); ?>,
				<?php echo date("H:i", strtotime($ev->PeriodStart)); ?> - <?php echo date("H:i", strtotime($ev->PeriodEnd)); ?>
			</div>
			<?php if(!$groupByCity) { ?>
			<div class="eventCity">
				<?php
				echo $ev->City;
				?>
			</div>
			<?php } ?>
			<div class="eventStatus<?php echo $groupByCityClass; ?>">
			<?php
				$spotsLeft = ($ev->MaxParticipantNr - $ev->TotalParticipantNr);
				echo getSpotsLeft($spotsLeft, $ev->MaxParticipantNr);
			?>
			</div>
			<div class="eventBook<?php echo $groupByCityClass; ?>">
			<?php
			if($ev->MaxParticipantNr == 0 || $spotsLeft > 0) {
			?>
				<a class="book-link" href="./book/?eid=<?php echo $ev->EventID; ?>" style="text-align: center;"><?php edu_e("Book"); ?></a>
			<?php
			} else {
			?>
				<i class="fullBooked"><?php edu_e("Full"); ?></i>
			<?php } ?>
			</div>
		</div>
	<?php
		$lastCity = $ev->City;
		$i++;
	}

	if(count($events) == 0)
	{
	?>
	<div class="noDatesAvailable">
		<i><?php edu_e("No available dates for the selected course"); ?></i>
	</div>
	<?php
	}
	?>
	</div>
</div>
<?php
$originalTitle = get_the_title();
$newTitle = $name . " | " . $originalTitle;
?>
<script type="text/javascript">
(function() {
	var title = document.title;
	title = title.replace('<?php echo $originalTitle; ?>', '<?php echo $newTitle; ?>');
	document.title = title;
})();
</script>
<?php } ?>