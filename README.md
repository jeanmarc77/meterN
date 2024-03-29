# meterN - Home energy monitor - (PHP/JS Energy Metering & Monitoring)

[![meterN demo](https://filedn.eu/lA1ykXBhnSe0rOKmNzxOM2H/images/mN/mn_ss.png)](https://youtu.be/NkhmwVdqF7Q "meterN demo")

# What can meterN do for you ?
meterN is a lightweight set of PHP/JS files that makes a « Home energy metering & monitoring » solution. It accept any meters like : electrical, water, gas, fuel consumption, solar, wind energy production and so on .. <br>
Sensors such as temperature or humidity are also accepted.
    
# Prerequisites
meterN rely on communication(s) application(s), which are -not- part of this project, see some <a href="https://github.com/jeanmarc77/meterN_comapps">examples here</a>.<br>
As it is running on top of a webserver, you must grant the access to your communication(s) application(s) as well as your communication port(s) to the 'http' user.<br>
Json, Calendar and Curl extensions have to be enable in php. Your server must allow HTTP authentication.
  
# Warnings
Modify your electrical installation must be done by a qualified person.<br>
Do not leave open the access of your website as it may reveal your house activities to any malicious person !<br>
  
# Installation 
- Install and test the communication applications for your meters/sensors and make sure they are reliable !<br>
- Put the archive on your web server's folder then extract. (tar -xzvf metern*.tar.gz)<br>
- Go then in your browser for configuration http://yourIP/metern/admin/
 
# Support, Update & Contact
Check the wiki and the 'Help and debugger' section in the administration section. It usually respond to most common(s) issue(s).

# License & External copyrights
meterN is released under the GNU GPLv3 license (General Public License).
This license allows you to freely integrate this library in your applications, modify the code and redistribute it in bundled packages as long as your application is also distributed with the GPL license. <br>
The GPLv3 license description can be found at http://www.gnu.org/licenses/gpl.html

Highcharts, the javascript charting library is free for non-commercial use only. (http://highcharts.com)<br>
 
Small-n-flat icons CC0 1.0 Universal (http://paomedia.github.io/small-n-flat/)

House icon Creative Commons (https://www.iconfinder.com/icons/299061/house_icon)
