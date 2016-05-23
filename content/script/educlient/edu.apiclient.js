var edu = edu ? edu : {};

edu.apiclient = {
	baseUrl: null,
	courseFolder: null,
	parseDocument: function(doc) {
		if(wp_edu != undefined) {
			//this.baseUrl = wp_edu.BaseUrl + '/wp-json/eduadmin/v1/';
			this.baseUrl = wp_edu.BaseUrl + '/wp-content/plugins/eduadmin/backend/edu.api.backend.php';
			this.courseFolder = wp_edu.CourseFolder;
		}
		var lw = doc.querySelector('[data-eduwidget="loginwidget"]');
		if(lw) {
			this.getLoginWidget(lw);
		}
	},
	getEventList: function(target, objectid) {
	},
	getNextEvent: function(target, objectid) {
	},
	getLoginWidget: function(target) {
		var loginText = 'Log in';
		var logoutText = 'Log out';
		var guestText = 'Guest';
		if(jQuery(target).data('logintext')) {
			loginText = jQuery(target).data('logintext');
		}

		if(jQuery(target).data('logouttext')) {
			logoutText = jQuery(target).data('logouttext');
		}

		if(jQuery(target).data('guesttext')) {
			guestText = jQuery(target).data('guesttext');
		}

		jQuery.ajax({
			url: this.baseUrl + '?module=login_widget',
			type: 'POST',
			data: {
				baseUrl: wp_edu.BaseUrl,
				courseFolder: wp_edu.CourseFolder,
				logintext: loginText,
				logouttext: logoutText,
				guesttext: guestText
			},
			success: function(d) {
				jQuery(target).replaceWith(d);
			}
		});
	}
};

(function() {
	if(jQuery != undefined) {
		jQuery('document').ready(function() {
			edu.apiclient.parseDocument(document);
		});
	} else {
		edu.apiclient.parseDocument(document);
	}
})();