<?php
function edu_api_eventlist($request)
{
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

	if(!empty($request['city']))
	{
		$f = new XFilter('City', '=', $request['city']);
		$ft->AddItem($f);
	}

	$st = new XSorting();
	$groupByCity = $request['city'];
	$groupByCityClass = "";
	if($groupByCity)
	{
		$s = new XSort('City', 'ASC');
		$st->AddItem($s);
		$groupByCityClass = " noCity";
	}
	$s = new XSort('PeriodStart', 'ASC');
	$st->AddItem($s);

	$events = $eduapi->GetEvent(
		$edutoken,
		$st->ToString(),
		$ft->ToString()
	);

	$occIds = array();

	foreach($events as $e)
	{
		$occIds[] = $e->OccationID;
	}

	$ft = new XFiltering();
	$f = new XFilter('PublicPriceName', '=', 'true');
	$ft->AddItem($f);
	$f = new XFilter('OccationID', 'IN', join(",", $occIds));
	$ft->AddItem($f);
	$pricenames = $eduapi->GetPriceName($edutoken,'',$ft->ToString());

	if(!empty($pricenames))
	{
		$events = array_filter($events, function($object) use (&$pricenames) {
			$pn = $pricenames;
			foreach($pn as $subj)
			{
				if($object->OccationID == $subj->OccationID)
				{
					return true;
				}
			}
			return false;
		});
	}

	$surl = $request['baseUrl'];
	$cat = $request['courseFolder'];

	$lastCity = "";

	$showMore = isset($attributes['showmore']) && !empty($attributes['showmore']) ? $attributes['showmore'] : -1;
	$spotLeftOption = get_option('eduadmin-spotsLeft', 'exactNumbers');

	$baseUrl = $surl . '/' . $cat;
	$name = (!empty($selectedCourse->PublicName) ? $selectedCourse->PublicName : $selectedCourse->ObjectName);
	$retStr .= '<div class="eduadmin"><div class="event-table eventDays">';
	$i = 0;
	$hasHiddenDates = false;

	foreach($events as $ev)
	{
		$spotsLeft = ($ev->MaxParticipantNr - $ev->TotalParticipantNr);

		if(isset($_REQUEST['eid']))
		{
			if($ev->EventID != $_REQUEST['eid'])
			{
				continue;
			}
		}

		if($groupByCity && $lastCity != $ev->City)
		{
			$i = 0;
			if($hasHiddenDates)
			{
				$retStr .= "<div class=\"eventShowMore\"><a href=\"javascript://\" onclick=\"eduDetailView.ShowAllEvents('eduev-" . $lastCity . "', this);\">" . edu__("Show all events") . "</a></div>";
			}
			$hasHiddenDates = false;
			$retStr .= '<div class="eventSeparator">' . $ev->City . '</div>';
		}

		if($showMore > 0 && $i >= $showMore)
		{
			$hasHiddenDates = true;
		}

		$retStr .= '<div data-groupid="eduev' . ($groupByCity ? "-" . $ev->City : "") . '" class="eventItem' . ($i % 2 == 0 ? " evenRow" : " oddRow") . ($showMore > 0 && $i >= $showMore ? " showMoreHidden" : "") . '">';
		$retStr .= '
		<div class="eventDate' . $groupByCityClass . '">
			' . GetStartEndDisplayDate($ev->PeriodStart, $ev->PeriodEnd, true) . ',
			' . date("H:i", strtotime($ev->PeriodStart)) . ' - ' . date("H:i", strtotime($ev->PeriodEnd)) . '
		</div>
		'. (!$groupByCity ?
		'<div class="eventCity">
			' . $ev->City . '
		</div>' : '') .
		'<div class="eventStatus' . $groupByCityClass . '">
		' .
			getSpotsLeft($spotsLeft, $ev->MaxParticipantNr)
		 . '
		</div>
		<div class="eventBook' . $groupByCityClass . '">
		' . ($ev->MaxParticipantNr == 0 || $spotsLeft > 0 ?

			'<a class="book-link" href="' . $baseUrl . '/' . makeSlugs($name) . '__' . $selectedCourse->ObjectID . '/book/?eid=' . $ev->EventID . edu_getQueryString("&", array('eid')) . '" style="text-align: center;">' . edu__("Book") . '</a>'
		:
			'<i class="fullBooked">' . edu__("Full") . '</i>'
		) . '
		</div>';
		$retStr .= '</div><!-- /eventitem -->';
		$lastCity = $ev->City;
		$i++;
	}
	if(empty($events))
	{
		$retStr.= '<div class="noDatesAvailable"><i>' . edu__("No available dates for the selected course") . '</i></div>';
	}
	if($hasHiddenDates)
	{
		$retStr .= "<div class=\"eventShowMore\"><a href=\"javascript://\" onclick=\"eduDetailView.ShowAllEvents('eduev" . ($groupByCity ? "-" . $ev->City : "") . "', this);\">" . edu__("Show all events") . "</a></div>";
	}
	$retStr .= '</div></div>';
}
?>