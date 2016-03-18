<div class="eduadmin wrap">
	<h2><?php echo sprintf(__("EduAdmin settings - %s", "eduadmin"), __("Booking settings", "eduadmin")); ?></h2>
	<form method="post" action="options.php">
		<?php settings_fields('eduadmin-booking'); ?>
		<?php do_settings_sections('eduadmin-booking'); ?>
		<div class="block">
<?php
$apiUserId = get_option('eduadmin-api_user_id');
$apiHash = get_option('eduadmin-api_hash');

if(!$apiUserId || !$apiHash || (empty($apiUserId) || empty($apiHash)))
{
	echo 'Please complete the configuration: <a href="' . admin_url() . 'admin.php?page=eduadmin-settings">EduAdmin settings - Api authentication</a>';
}
else
{
	$api = new EduAdminClient();

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
	$api->debug = false;
?>
			<h3><?php echo __("Default customer group", "eduadmin"); ?></h3>
			<?php
			$ft = new XFiltering();
			$f = new XFilter('PublicGroup', '=', 'true');
			$ft->AddItem($f);

			$st = new XSorting();
			$s = new XSort('ParentCustomerGroupID', 'ASC');
			$st->AddItem($s);

			$cg = $api->GetCustomerGroup($token, $st->ToString(), $ft->ToString());

			foreach($cg as $i => $v)
			{
				$parent[$i] = $v->ParentCustomerGroupID;
			}

			array_multisort($parent, SORT_ASC, $cg);

			$levelStack = array();
			foreach($cg as $g)
			{
				$levelStack[$g->ParentCustomerGroupID][] = $g;
			}

			$depth = 0;


			function edu_writeOptions($g, $array, $depth)
			{
				$selectedOption = get_option('eduadmin-customerGroupId', 0);
				echo
				"<option value=\"" . $g->CustomerGroupID . "\"" . ($selectedOption == $g->CustomerGroupID ? " selected=\"selected\"" : "") . ">" .
					str_repeat('&nbsp;', $depth * 4) .
					$g->CustomerGroupName .
				"</option>\n";
				if(array_key_exists($g->CustomerGroupID, $array))
				{
					$depth++;
					foreach($array[$g->CustomerGroupID] as $ng)
					{
						edu_writeOptions($ng, $array, $depth);
					}
					$depth--;
				}
			}

			?>
			<select name="eduadmin-customerGroupId">
			<?php
			$root = $levelStack['0'];
			foreach($root as $g)
			{
				edu_writeOptions($g, $levelStack, $depth);
			}
			?></select>
			<br />
			<br />
			<label>
				<input type="checkbox" name="eduadmin-useLogin" <?php echo (get_option("eduadmin-useLogin", false) ? " checked=\"checked\"" : ""); ?> />
				<?php _e("Use login", "eduadmin"); ?>
			</label>
			<?php
				$selectedCurrency = get_option('eduadmin-currency', 'SEK');
			?>
			<h3><?php _e("Currency", "eduadmin"); ?></h3>
			<select name="eduadmin-currency">
				<option value="AED"<?php echo ($selectedCurrency == "AED" ? " selected=\"selected\"" : ""); ?>>AED - United Arab Emirates, Dirhams</option>
				<option value="AFN"<?php echo ($selectedCurrency == "AFN" ? " selected=\"selected\"" : ""); ?>>AFN - Afghanistan, Afghanis</option>
				<option value="ALL"<?php echo ($selectedCurrency == "ALL" ? " selected=\"selected\"" : ""); ?>>ALL - Albania, Leke</option>
				<option value="AMD"<?php echo ($selectedCurrency == "AMD" ? " selected=\"selected\"" : ""); ?>>AMD - Armenia, Drams</option>
				<option value="ANG"<?php echo ($selectedCurrency == "ANG" ? " selected=\"selected\"" : ""); ?>>ANG - Netherlands Antilles, Guilders (also called Florins)</option>
				<option value="AOA"<?php echo ($selectedCurrency == "AOA" ? " selected=\"selected\"" : ""); ?>>AOA - Angola, Kwanza</option>
				<option value="ARS"<?php echo ($selectedCurrency == "ARS" ? " selected=\"selected\"" : ""); ?>>ARS - Argentina, Pesos</option>
				<option value="AUD"<?php echo ($selectedCurrency == "AUD" ? " selected=\"selected\"" : ""); ?>>AUD - Australia, Dollars</option>
				<option value="AWG"<?php echo ($selectedCurrency == "AWG" ? " selected=\"selected\"" : ""); ?>>AWG - Aruba, Guilders (also called Florins)</option>
				<option value="AZN"<?php echo ($selectedCurrency == "AZN" ? " selected=\"selected\"" : ""); ?>>AZN - Azerbaijan, New Manats</option>
				<option value="BAM"<?php echo ($selectedCurrency == "BAM" ? " selected=\"selected\"" : ""); ?>>BAM - Bosnia and Herzegovina, Convertible Marka</option>
				<option value="BBD"<?php echo ($selectedCurrency == "BBD" ? " selected=\"selected\"" : ""); ?>>BBD - Barbados, Dollars</option>
				<option value="BDT"<?php echo ($selectedCurrency == "BDT" ? " selected=\"selected\"" : ""); ?>>BDT - Bangladesh, Taka</option>
				<option value="BGN"<?php echo ($selectedCurrency == "BGN" ? " selected=\"selected\"" : ""); ?>>BGN - Bulgaria, Leva</option>
				<option value="BHD"<?php echo ($selectedCurrency == "BHD" ? " selected=\"selected\"" : ""); ?>>BHD - Bahrain, Dinars</option>
				<option value="BIF"<?php echo ($selectedCurrency == "BIF" ? " selected=\"selected\"" : ""); ?>>BIF - Burundi, Francs</option>
				<option value="BMD"<?php echo ($selectedCurrency == "BMD" ? " selected=\"selected\"" : ""); ?>>BMD - Bermuda, Dollars</option>
				<option value="BND"<?php echo ($selectedCurrency == "BND" ? " selected=\"selected\"" : ""); ?>>BND - Brunei Darussalam, Dollars</option>
				<option value="BOB"<?php echo ($selectedCurrency == "BOB" ? " selected=\"selected\"" : ""); ?>>BOB - Bolivia, Bolivianos</option>
				<option value="BRL"<?php echo ($selectedCurrency == "BRL" ? " selected=\"selected\"" : ""); ?>>BRL - Brazil, Brazil Real</option>
				<option value="BSD"<?php echo ($selectedCurrency == "BSD" ? " selected=\"selected\"" : ""); ?>>BSD - Bahamas, Dollars</option>
				<option value="BTN"<?php echo ($selectedCurrency == "BTN" ? " selected=\"selected\"" : ""); ?>>BTN - Bhutan, Ngultrum</option>
				<option value="BWP"<?php echo ($selectedCurrency == "BWP" ? " selected=\"selected\"" : ""); ?>>BWP - Botswana, Pulas</option>
				<option value="BYR"<?php echo ($selectedCurrency == "BYR" ? " selected=\"selected\"" : ""); ?>>BYR - Belarus, Rubles</option>
				<option value="BZD"<?php echo ($selectedCurrency == "BZD" ? " selected=\"selected\"" : ""); ?>>BZD - Belize, Dollars</option>
				<option value="CAD"<?php echo ($selectedCurrency == "CAD" ? " selected=\"selected\"" : ""); ?>>CAD - Canada, Dollars</option>
				<option value="CDF"<?php echo ($selectedCurrency == "CDF" ? " selected=\"selected\"" : ""); ?>>CDF - Congo/Kinshasa, Congolese Francs</option>
				<option value="CHF"<?php echo ($selectedCurrency == "CHF" ? " selected=\"selected\"" : ""); ?>>CHF - Switzerland, Francs</option>
				<option value="CLP"<?php echo ($selectedCurrency == "CLP" ? " selected=\"selected\"" : ""); ?>>CLP - Chile, Pesos</option>
				<option value="CNY"<?php echo ($selectedCurrency == "CNY" ? " selected=\"selected\"" : ""); ?>>CNY - China, Yuan Renminbi</option>
				<option value="COP"<?php echo ($selectedCurrency == "COP" ? " selected=\"selected\"" : ""); ?>>COP - Colombia, Pesos</option>
				<option value="CRC"<?php echo ($selectedCurrency == "CRC" ? " selected=\"selected\"" : ""); ?>>CRC - Costa Rica, Colones</option>
				<option value="CUP"<?php echo ($selectedCurrency == "CUP" ? " selected=\"selected\"" : ""); ?>>CUP - Cuba, Pesos</option>
				<option value="CVE"<?php echo ($selectedCurrency == "CVE" ? " selected=\"selected\"" : ""); ?>>CVE - Cape Verde, Escudos</option>
				<option value="CZK"<?php echo ($selectedCurrency == "CZK" ? " selected=\"selected\"" : ""); ?>>CZK - Czech Republic, Koruny</option>
				<option value="DJF"<?php echo ($selectedCurrency == "DJF" ? " selected=\"selected\"" : ""); ?>>DJF - Djibouti, Francs</option>
				<option value="DKK"<?php echo ($selectedCurrency == "DKK" ? " selected=\"selected\"" : ""); ?>>DKK - Denmark, Kroner</option>
				<option value="DOP"<?php echo ($selectedCurrency == "DOP" ? " selected=\"selected\"" : ""); ?>>DOP - Dominican Republic, Pesos</option>
				<option value="DZD"<?php echo ($selectedCurrency == "DZD" ? " selected=\"selected\"" : ""); ?>>DZD - Algeria, Algeria Dinars</option>
				<option value="EGP"<?php echo ($selectedCurrency == "EGP" ? " selected=\"selected\"" : ""); ?>>EGP - Egypt, Pounds</option>
				<option value="ERN"<?php echo ($selectedCurrency == "ERN" ? " selected=\"selected\"" : ""); ?>>ERN - Eritrea, Nakfa</option>
				<option value="ETB"<?php echo ($selectedCurrency == "ETB" ? " selected=\"selected\"" : ""); ?>>ETB - Ethiopia, Birr</option>
				<option value="EUR"<?php echo ($selectedCurrency == "EUR" ? " selected=\"selected\"" : ""); ?>>EUR - Euro Member Countries, Euro</option>
				<option value="FJD"<?php echo ($selectedCurrency == "FJD" ? " selected=\"selected\"" : ""); ?>>FJD - Fiji, Dollars</option>
				<option value="FKP"<?php echo ($selectedCurrency == "FKP" ? " selected=\"selected\"" : ""); ?>>FKP - Falkland Islands (Malvinas), Pounds</option>
				<option value="GBP"<?php echo ($selectedCurrency == "GBP" ? " selected=\"selected\"" : ""); ?>>GBP - United Kingdom, Pounds</option>
				<option value="GEL"<?php echo ($selectedCurrency == "GEL" ? " selected=\"selected\"" : ""); ?>>GEL - Georgia, Lari</option>
				<option value="GHS"<?php echo ($selectedCurrency == "GHS" ? " selected=\"selected\"" : ""); ?>>GHS - Ghana, Cedis</option>
				<option value="GIP"<?php echo ($selectedCurrency == "GIP" ? " selected=\"selected\"" : ""); ?>>GIP - Gibraltar, Pounds</option>
				<option value="GMD"<?php echo ($selectedCurrency == "GMD" ? " selected=\"selected\"" : ""); ?>>GMD - Gambia, Dalasi</option>
				<option value="GNF"<?php echo ($selectedCurrency == "GNF" ? " selected=\"selected\"" : ""); ?>>GNF - Guinea, Francs</option>
				<option value="GTQ"<?php echo ($selectedCurrency == "GTQ" ? " selected=\"selected\"" : ""); ?>>GTQ - Guatemala, Quetzales</option>
				<option value="GYD"<?php echo ($selectedCurrency == "GYD" ? " selected=\"selected\"" : ""); ?>>GYD - Guyana, Dollars</option>
				<option value="HKD"<?php echo ($selectedCurrency == "HKD" ? " selected=\"selected\"" : ""); ?>>HKD - Hong Kong, Dollars</option>
				<option value="HNL"<?php echo ($selectedCurrency == "HNL" ? " selected=\"selected\"" : ""); ?>>HNL - Honduras, Lempiras</option>
				<option value="HRK"<?php echo ($selectedCurrency == "HRK" ? " selected=\"selected\"" : ""); ?>>HRK - Croatia, Kuna</option>
				<option value="HTG"<?php echo ($selectedCurrency == "HTG" ? " selected=\"selected\"" : ""); ?>>HTG - Haiti, Gourdes</option>
				<option value="HUF"<?php echo ($selectedCurrency == "HUF" ? " selected=\"selected\"" : ""); ?>>HUF - Hungary, Forint</option>
				<option value="IDR"<?php echo ($selectedCurrency == "IDR" ? " selected=\"selected\"" : ""); ?>>IDR - Indonesia, Rupiahs</option>
				<option value="ILS"<?php echo ($selectedCurrency == "ILS" ? " selected=\"selected\"" : ""); ?>>ILS - Israel, New Shekels</option>
				<option value="INR"<?php echo ($selectedCurrency == "INR" ? " selected=\"selected\"" : ""); ?>>INR - India, Rupees</option>
				<option value="IQD"<?php echo ($selectedCurrency == "IQD" ? " selected=\"selected\"" : ""); ?>>IQD - Iraq, Dinars</option>
				<option value="IRR"<?php echo ($selectedCurrency == "IRR" ? " selected=\"selected\"" : ""); ?>>IRR - Iran, Rials</option>
				<option value="ISK"<?php echo ($selectedCurrency == "ISK" ? " selected=\"selected\"" : ""); ?>>ISK - Iceland, Kronur</option>
				<option value="JMD"<?php echo ($selectedCurrency == "JMD" ? " selected=\"selected\"" : ""); ?>>JMD - Jamaica, Dollars</option>
				<option value="JOD"<?php echo ($selectedCurrency == "JOD" ? " selected=\"selected\"" : ""); ?>>JOD - Jordan, Dinars</option>
				<option value="JPY"<?php echo ($selectedCurrency == "JPY" ? " selected=\"selected\"" : ""); ?>>JPY - Japan, Yen</option>
				<option value="KES"<?php echo ($selectedCurrency == "KES" ? " selected=\"selected\"" : ""); ?>>KES - Kenya, Shillings</option>
				<option value="KGS"<?php echo ($selectedCurrency == "KGS" ? " selected=\"selected\"" : ""); ?>>KGS - Kyrgyzstan, Soms</option>
				<option value="KHR"<?php echo ($selectedCurrency == "KHR" ? " selected=\"selected\"" : ""); ?>>KHR - Cambodia, Riels</option>
				<option value="KMF"<?php echo ($selectedCurrency == "KMF" ? " selected=\"selected\"" : ""); ?>>KMF - Comoros, Francs</option>
				<option value="KPW"<?php echo ($selectedCurrency == "KPW" ? " selected=\"selected\"" : ""); ?>>KPW - Korea (North), Won</option>
				<option value="KRW"<?php echo ($selectedCurrency == "KRW" ? " selected=\"selected\"" : ""); ?>>KRW - Korea (South), Won</option>
				<option value="KWD"<?php echo ($selectedCurrency == "KWD" ? " selected=\"selected\"" : ""); ?>>KWD - Kuwait, Dinars</option>
				<option value="KYD"<?php echo ($selectedCurrency == "KYD" ? " selected=\"selected\"" : ""); ?>>KYD - Cayman Islands, Dollars</option>
				<option value="KZT"<?php echo ($selectedCurrency == "KZT" ? " selected=\"selected\"" : ""); ?>>KZT - Kazakhstan, Tenge</option>
				<option value="LAK"<?php echo ($selectedCurrency == "LAK" ? " selected=\"selected\"" : ""); ?>>LAK - Laos, Kips</option>
				<option value="LBP"<?php echo ($selectedCurrency == "LBP" ? " selected=\"selected\"" : ""); ?>>LBP - Lebanon, Pounds</option>
				<option value="LKR"<?php echo ($selectedCurrency == "LKR" ? " selected=\"selected\"" : ""); ?>>LKR - Sri Lanka, Rupees</option>
				<option value="LRD"<?php echo ($selectedCurrency == "LRD" ? " selected=\"selected\"" : ""); ?>>LRD - Liberia, Dollars</option>
				<option value="LSL"<?php echo ($selectedCurrency == "LSL" ? " selected=\"selected\"" : ""); ?>>LSL - Lesotho, Maloti</option>
				<option value="LYD"<?php echo ($selectedCurrency == "LYD" ? " selected=\"selected\"" : ""); ?>>LYD - Libya, Dinars</option>
				<option value="MAD"<?php echo ($selectedCurrency == "MAD" ? " selected=\"selected\"" : ""); ?>>MAD - Morocco, Dirhams</option>
				<option value="MDL"<?php echo ($selectedCurrency == "MDL" ? " selected=\"selected\"" : ""); ?>>MDL - Moldova, Lei</option>
				<option value="MGA"<?php echo ($selectedCurrency == "MGA" ? " selected=\"selected\"" : ""); ?>>MGA - Madagascar, Ariary</option>
				<option value="MKD"<?php echo ($selectedCurrency == "MKD" ? " selected=\"selected\"" : ""); ?>>MKD - Macedonia, Denars</option>
				<option value="MMK"<?php echo ($selectedCurrency == "MMK" ? " selected=\"selected\"" : ""); ?>>MMK - Myanmar (Burma), Kyats</option>
				<option value="MNT"<?php echo ($selectedCurrency == "MNT" ? " selected=\"selected\"" : ""); ?>>MNT - Mongolia, Tugriks</option>
				<option value="MOP"<?php echo ($selectedCurrency == "MOP" ? " selected=\"selected\"" : ""); ?>>MOP - Macau, Patacas</option>
				<option value="MRO"<?php echo ($selectedCurrency == "MRO" ? " selected=\"selected\"" : ""); ?>>MRO - Mauritania, Ouguiyas</option>
				<option value="MUR"<?php echo ($selectedCurrency == "MUR" ? " selected=\"selected\"" : ""); ?>>MUR - Mauritius, Rupees</option>
				<option value="MWK"<?php echo ($selectedCurrency == "MWK" ? " selected=\"selected\"" : ""); ?>>MWK - Malawi, Kwachas</option>
				<option value="MVR"<?php echo ($selectedCurrency == "MVR" ? " selected=\"selected\"" : ""); ?>>MVR - Maldives (Maldive Islands), Rufiyaa</option>
				<option value="MXN"<?php echo ($selectedCurrency == "MXN" ? " selected=\"selected\"" : ""); ?>>MXN - Mexico, Pesos</option>
				<option value="MYR"<?php echo ($selectedCurrency == "MYR" ? " selected=\"selected\"" : ""); ?>>MYR - Malaysia, Ringgits</option>
				<option value="MZN"<?php echo ($selectedCurrency == "MZN" ? " selected=\"selected\"" : ""); ?>>MZN - Mozambique, Meticais</option>
				<option value="NAD"<?php echo ($selectedCurrency == "NAD" ? " selected=\"selected\"" : ""); ?>>NAD - Namibia, Dollars</option>
				<option value="NGN"<?php echo ($selectedCurrency == "NGN" ? " selected=\"selected\"" : ""); ?>>NGN - Nigeria, Nairas</option>
				<option value="NIO"<?php echo ($selectedCurrency == "NIO" ? " selected=\"selected\"" : ""); ?>>NIO - Nicaragua, Cordobas</option>
				<option value="NOK"<?php echo ($selectedCurrency == "NOK" ? " selected=\"selected\"" : ""); ?>>NOK - Norway, Krone</option>
				<option value="NPR"<?php echo ($selectedCurrency == "NPR" ? " selected=\"selected\"" : ""); ?>>NPR - Nepal, Nepal Rupees</option>
				<option value="NZD"<?php echo ($selectedCurrency == "NZD" ? " selected=\"selected\"" : ""); ?>>NZD - New Zealand, Dollars</option>
				<option value="OMR"<?php echo ($selectedCurrency == "OMR" ? " selected=\"selected\"" : ""); ?>>OMR - Oman, Rials</option>
				<option value="PAB"<?php echo ($selectedCurrency == "PAB" ? " selected=\"selected\"" : ""); ?>>PAB - Panama, Balboa</option>
				<option value="PEN"<?php echo ($selectedCurrency == "PEN" ? " selected=\"selected\"" : ""); ?>>PEN - Peru, Nuevos Soles</option>
				<option value="PGK"<?php echo ($selectedCurrency == "PGK" ? " selected=\"selected\"" : ""); ?>>PGK - Papua New Guinea, Kina</option>
				<option value="PHP"<?php echo ($selectedCurrency == "PHP" ? " selected=\"selected\"" : ""); ?>>PHP - Philippines, Pesos</option>
				<option value="PKR"<?php echo ($selectedCurrency == "PKR" ? " selected=\"selected\"" : ""); ?>>PKR - Pakistan, Rupees</option>
				<option value="PLN"<?php echo ($selectedCurrency == "PLN" ? " selected=\"selected\"" : ""); ?>>PLN - Poland, Zlotych</option>
				<option value="PYG"<?php echo ($selectedCurrency == "PYG" ? " selected=\"selected\"" : ""); ?>>PYG - Paraguay, Guarani</option>
				<option value="QAR"<?php echo ($selectedCurrency == "QAR" ? " selected=\"selected\"" : ""); ?>>QAR - Qatar, Rials</option>
				<option value="RON"<?php echo ($selectedCurrency == "RON" ? " selected=\"selected\"" : ""); ?>>RON - Romania, New Lei</option>
				<option value="RSD"<?php echo ($selectedCurrency == "RSD" ? " selected=\"selected\"" : ""); ?>>RSD - Serbia, Dinars</option>
				<option value="RUB"<?php echo ($selectedCurrency == "RUB" ? " selected=\"selected\"" : ""); ?>>RUB - Russia, Rubles</option>
				<option value="RWF"<?php echo ($selectedCurrency == "RWF" ? " selected=\"selected\"" : ""); ?>>RWF - Rwanda, Rwanda Francs</option>
				<option value="SAR"<?php echo ($selectedCurrency == "SAR" ? " selected=\"selected\"" : ""); ?>>SAR - Saudi Arabia, Riyals</option>
				<option value="SBD"<?php echo ($selectedCurrency == "SBD" ? " selected=\"selected\"" : ""); ?>>SBD - Solomon Islands, Dollars</option>
				<option value="SCR"<?php echo ($selectedCurrency == "SCR" ? " selected=\"selected\"" : ""); ?>>SCR - Seychelles, Rupees</option>
				<option value="SDG"<?php echo ($selectedCurrency == "SDG" ? " selected=\"selected\"" : ""); ?>>SDG - Sudan, Pounds</option>
				<option value="SEK"<?php echo ($selectedCurrency == "SEK" ? " selected=\"selected\"" : ""); ?>>SEK - Sweden, Kronor</option>
				<option value="SGD"<?php echo ($selectedCurrency == "SGD" ? " selected=\"selected\"" : ""); ?>>SGD - Singapore, Dollars</option>
				<option value="SHP"<?php echo ($selectedCurrency == "SHP" ? " selected=\"selected\"" : ""); ?>>SHP - Saint Helena, Pounds</option>
				<option value="SLL"<?php echo ($selectedCurrency == "SLL" ? " selected=\"selected\"" : ""); ?>>SLL - Sierra Leone, Leones</option>
				<option value="SOS"<?php echo ($selectedCurrency == "SOS" ? " selected=\"selected\"" : ""); ?>>SOS - Somalia, Shillings</option>
				<option value="SRD"<?php echo ($selectedCurrency == "SRD" ? " selected=\"selected\"" : ""); ?>>SRD - Suriname, Dollars</option>
				<option value="STD"<?php echo ($selectedCurrency == "STD" ? " selected=\"selected\"" : ""); ?>>STD - São Tome and Principe, Dobras</option>
				<option value="SYP"<?php echo ($selectedCurrency == "SYP" ? " selected=\"selected\"" : ""); ?>>SYP - Syria, Pounds</option>
				<option value="SZL"<?php echo ($selectedCurrency == "SZL" ? " selected=\"selected\"" : ""); ?>>SZL - Swaziland, Emalangeni</option>
				<option value="THB"<?php echo ($selectedCurrency == "THB" ? " selected=\"selected\"" : ""); ?>>THB - Thailand, Baht</option>
				<option value="TJS"<?php echo ($selectedCurrency == "TJS" ? " selected=\"selected\"" : ""); ?>>TJS - Tajikistan, Somoni</option>
				<option value="TMT"<?php echo ($selectedCurrency == "TMT" ? " selected=\"selected\"" : ""); ?>>TMT - Turkmenistan, New Manats</option>
				<option value="TND"<?php echo ($selectedCurrency == "TND" ? " selected=\"selected\"" : ""); ?>>TND - Tunisia, Dinars</option>
				<option value="TOP"<?php echo ($selectedCurrency == "TOP" ? " selected=\"selected\"" : ""); ?>>TOP - Tonga, Pa'anga</option>
				<option value="TRY"<?php echo ($selectedCurrency == "TRY" ? " selected=\"selected\"" : ""); ?>>TRY - Turkey, New Lira</option>
				<option value="TTD"<?php echo ($selectedCurrency == "TTD" ? " selected=\"selected\"" : ""); ?>>TTD - Trinidad and Tobago, Dollars</option>
				<option value="TWD"<?php echo ($selectedCurrency == "TWD" ? " selected=\"selected\"" : ""); ?>>TWD - Taiwan, New Dollars</option>
				<option value="TZS"<?php echo ($selectedCurrency == "TZS" ? " selected=\"selected\"" : ""); ?>>TZS - Tanzania, Shillings</option>
				<option value="UAH"<?php echo ($selectedCurrency == "UAH" ? " selected=\"selected\"" : ""); ?>>UAH - Ukraine, Hryvnia</option>
				<option value="UGX"<?php echo ($selectedCurrency == "UGX" ? " selected=\"selected\"" : ""); ?>>UGX - Uganda, Shillings</option>
				<option value="USD"<?php echo ($selectedCurrency == "USD" ? " selected=\"selected\"" : ""); ?>>USD - United States of America, Dollars</option>
				<option value="UYU"<?php echo ($selectedCurrency == "UYU" ? " selected=\"selected\"" : ""); ?>>UYU - Uruguay, Pesos</option>
				<option value="UZS"<?php echo ($selectedCurrency == "UZS" ? " selected=\"selected\"" : ""); ?>>UZS - Uzbekistan, Sums</option>
				<option value="VEF"<?php echo ($selectedCurrency == "VEF" ? " selected=\"selected\"" : ""); ?>>VEF - Venezuela, Bolivares Fuertes</option>
				<option value="VND"<?php echo ($selectedCurrency == "VND" ? " selected=\"selected\"" : ""); ?>>VND - Viet Nam, Dong</option>
				<option value="WST"<?php echo ($selectedCurrency == "WST" ? " selected=\"selected\"" : ""); ?>>WST - Samoa, Tala</option>
				<option value="VUV"<?php echo ($selectedCurrency == "VUV" ? " selected=\"selected\"" : ""); ?>>VUV - Vanuatu, Vatu</option>
				<option value="XAF"<?php echo ($selectedCurrency == "XAF" ? " selected=\"selected\"" : ""); ?>>XAF - Communauté Financière Africaine BEAC, Francs</option>
				<option value="XAG"<?php echo ($selectedCurrency == "XAG" ? " selected=\"selected\"" : ""); ?>>XAG - Silver, Ounces</option>
				<option value="XAU"<?php echo ($selectedCurrency == "XAU" ? " selected=\"selected\"" : ""); ?>>XAU - Gold, Ounces</option>
				<option value="XCD"<?php echo ($selectedCurrency == "XCD" ? " selected=\"selected\"" : ""); ?>>XCD - East Caribbean Dollars</option>
				<option value="XDR"<?php echo ($selectedCurrency == "XDR" ? " selected=\"selected\"" : ""); ?>>XDR - International Monetary Fund (IMF) Special Drawing Rights</option>
				<option value="XOF"<?php echo ($selectedCurrency == "XOF" ? " selected=\"selected\"" : ""); ?>>XOF - Communauté Financière Africaine BCEAO, Francs</option>
				<option value="XPD"<?php echo ($selectedCurrency == "XPD" ? " selected=\"selected\"" : ""); ?>>XPD - Palladium Ounces</option>
				<option value="XPF"<?php echo ($selectedCurrency == "XPF" ? " selected=\"selected\"" : ""); ?>>XPF - Comptoirs Français du Pacifique Francs</option>
				<option value="XPT"<?php echo ($selectedCurrency == "XPT" ? " selected=\"selected\"" : ""); ?>>XPT - Platinum, Ounces</option>
				<option value="YER"<?php echo ($selectedCurrency == "YER" ? " selected=\"selected\"" : ""); ?>>YER - Yemen, Rials</option>
				<option value="ZAR"<?php echo ($selectedCurrency == "ZAR" ? " selected=\"selected\"" : ""); ?>>ZAR - South Africa, Rand</option>
				<option value="ZMW"<?php echo ($selectedCurrency == "ZMW" ? " selected=\"selected\"" : ""); ?>>ZMW - Zambia, Kwacha</option>
			</select>
			<h3><?php _e("Booking terms", "eduadmin"); ?></h3>
			<h4><?php _e("Booking terms link", "eduadmin"); ?></h4>
			<input type="url" class="form-control" style="width: 100%;" name="eduadmin-bookingTermsLink" placeholder="Booking terms link" value="<?php echo get_option('eduadmin-bookingTermsLink'); ?>" />
			<br />
			<label>
				<input type="checkbox" name="eduadmin-useBookingTermsCheckbox" value="true"<?php if(get_option('eduadmin-useBookingTermsCheckbox', false)) { echo " checked=\"checked\""; } ?> /> <?php _e("Use booking terms", "eduadmin"); ?>
			</label>
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __("Save settings", "eduadmin"); ?>" />
			</p>
<?php } ?>
		</div>
	</form>
</div>