---
id: troubleshooting
title: Troubleshooting
sidebar_label: How to troubleshoot
---

## Common issues

These issues have been reported most of all, 
and is likely a configuration problem, 
or compatibility problem with other WordPress plugins.

### The web site is showing old data

If the data on the web page isn't updating after you have updated the information in [**EduAdmin**](https://www.eduadmin.se),
you might want to clear any eventual cache plugins, and the internal cache in our plugin.

We cache some data for a period of time, to make the website as fast as possible.

### Nothing happens when I click anything

Make sure you are not using any plugins that combine/rearrange stylesheets or javascripts, 
or put our scripts in a whitelist, so they are not combined. Many of these plugins are not
checking in what order they should be loaded and might put the scripts in the wrong order.

### It's not showing in the correct language

By default, WordPress will download language files for plugins, 
but we have noticed in some instances that it either fails to do so,
or another translation plugin is prohibiting the translation to work properly.

### Whenever I try to complete a booking, an unexpected error occurs

Most of the time, when the plugin connects to [**EduAdmin**](https://www.eduadmin.se) to complete the booking,
we get back either an success, or an array of errors.

The unexpected error means something went wrong, that we do not have a classification for,
so please **contact us** at our support portal whenever this occurs.

You can find the support portal at [**https://support.multinet.se**](https://support.multinet.se).