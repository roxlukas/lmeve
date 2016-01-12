<?php
/**********************************************************************************
								LM Framework v3
								
	A simple PHP based application framework.
	
	Contact: pozniak.lukasz@gmail.com
	
	Copyright (c) 2005-2013, �ukasz Po�niak
	All rights reserved.

	Redistribution and use in source and binary forms, with or without modification,
	are permitted provided that the following conditions are met:
	
	Redistributions of source code must retain the above copyright notice,
	this list of conditions and the following disclaimer.
	Redistributions in binary form must reproduce the above copyright notice,
	this list of conditions and the following disclaimer in the documentation
	and/or other materials provided with the distribution.
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
	AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
	THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
	ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS
	BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT
	OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
	OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
	WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
	OF THE POSSIBILITY OF SUCH DAMAGE.

**********************************************************************************/

?>

<br>
<div class="tekst">
<?php
include('../config/config.php'); //wczytaj nastawy konfiguracji
?>
<table width="90%" border="0" cellspacing="2" cellpadding="0">
<tr><td class="tab-header"><div class="tytul">
Changelog:
</div></td><tr></table>

<table width="90%" border="0" cellspacing="2" cellpadding="0">
<!-- changelog start -->
<tr><td class="tab" width="180">
<div class="tytul2">2015.09.25</div></td>
<td valign="top" class="tab">
<b>0.1.55 beta:</b><br>
<b>Poller version 26</b>
<ul>
        <li>Fully functional Killboard</li>
	<li>XML and CREST killmail support in API Poller</li>
	<li>Can use either XML or CREST killmail endpoint (configurable in Settings)</li>
        <li>CCP WebGL preview in Inventory and Killboard ship fitting window</li>
	<li>You can now input Corp API Keys in GUI</li>
        <li>Disabled accounts have existing sessions terminated immediately</li>
	<li>Bug fixes</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2015.08.18</div></td>
<td valign="top" class="tab">
<b>0.1.54 beta:</b><br>
<b>Poller version 24</b>
<ul>
	<li>Improved corporation Inventory browser</li>
	<li>Ship Fitting browser in corporation Inventory</li>
	<li>CCP WebGL update</li>
	<li>Custom SKIN support</li>
	<li>Additional tooltips</li>
	<li>New icon support for ingame items</li>
	<li>Customizable market hub to get material prices from</li>
	<li>Customizable manufacturing sytem Index (Crius NPC cost formula)</li>
	<li>Customizable price modifier for Buy Calculator</li>
	<li>Real time API poller stats</li>
	<li>Bug fixes</li>
</ul>
</td></tr>


<tr><td class="tab" width="180">
<div class="tytul2">2015.06.25</div></td>
<td valign="top" class="tab">
<b>0.1.53 beta:</b><br>
<b>Poller version 22</b>
<ul>
<li>
    SPYC library removed and code rewritten to use YAML PECL extension in PHP<ul>
        <li><strong>Please note:</strong> LMeve now requires YAML PECL extenstion installed on your host</li>
        <li>as a side effect, YAML import now takes several seconds instead of several minutes</li>
        <li>this was necessary to make LMeve compatible with SDE changes in Aegis release</li>
    </ul>
        
</li>
<li>
	EVE Time has been added on the top bar
</li>
<li>
	Industry Statistics have been divided by activity type (stacked bar chart breaking down into Manufacturing, Invention and so on)
</li>
<li>
	Timesheet can be now aggregated by LMeve user rather than by in game character. It means that work and ISK made by all characters belonging to a user will be summed up for a less cluttered Timesheet view.
</li>
<li>Bug fixes:<ul>
    <li>
        Ore Chart now properly displays it's contents again
    </li>
    <li>
        Session now correctly times out since last action in LMeve, instead of since logging in
    </li>
    <li>
        CREST now properly uses HTTPS instead of HTTP
    </li>
</ul>
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2015.03.26</div></td>
<td valign="top" class="tab">
<b>0.1.52 beta:</b><br>
<b>Poller version 22</b>
<ul>
<li>
	Poller now fetches StarbaseDetails.xml and CREST industry cost indexes
</li>
<li>
        New POS management screen - displays fuel amounts and how long it will last
</li>
<li>
        There is now a notification when any tower has less than 48 hours of fuel left
</li>
<li>
        Tech III invention added. You can now add Tech III invention tasks and get price estimates for Tech III
</li>
<li>
        New global setting for the default Relic type for use with Tech III Invention (Intact, Malfunctioning or Wrecked)
</li>
<li>Bug fixes:<ul>
    <li>
        Non-recurring Tasks (singleton) bug fix - they did not display correctly
    </li>
    <li>
        Bug fix in db.php "Notice: LM_dbhost is not defined" - added $LM_dbhost to global declaration
    </li>
    <li>
        Bug fix in Database - "function eregi() is deprecated" - replaced with preg_match()
    </li>
    <li>
        Bug fix in YAML updater - DROP TABLE <strong>IF EXISTS</strong>
    </li>
    </ul>
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2015.01.05</div></td>
<td valign="top" class="tab">
<b>0.1.51 beta:</b><br>
<b>Poller version 21</b>
<ul>
<li>
	Admin can now clear Orphane Tasks - tasks assigned to members who left the corporation
</li>
<li>
	Non-recurring Tasks (singleton) bug fix in Industry Facilities and Facility kits. Both now show singleton tasks only for the current month.
</li>
<li>
	Non-recurring Tasks will now only expire if:
        <ul>
            <li>Task is completed (100% complete)</li>
            <li>Task is older than x days (90 by default)</li>
        </ul>
</li>

<li>
        Mobile theme for smartphones
</li>
<li>
        Easier installation (no need to use CLI commands to reset admin password)
</li>
<li>
        PHP error_reporting is now set up according to $LM_DEBUG config variable
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2014.12.16</div></td>
<td valign="top" class="tab">
<b>0.1.50 beta:</b><br>
<b>Poller version 21</b>
<ul>
<li>
	Page caching. Pages that refresh very long are now cached - starting with Inventory and PoCos
</li>
<li>
	Profit Chart was causing timeouts when too many items have been selected. Added an AJAX lazy-loader.
</li>
<li>
	Big update regarding POCOs
        <ul>
            <li>
                POCOs are now linked to Planet they orbit using magic SQL Query that involves square root ;-)
            </li>
            <li>
                POCOs now display planet type they orbit
            </li>
            <li>
                POCOs now display a bar that shows how much they contributed to overall income
            </li>
            <li>
                When you hover over the planet name it will show exactly how much ISK it made in the last month
            </li>
        </ul>
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2014.11.05</div></td>
<td valign="top" class="tab">
<b>0.1.49 beta:</b><br>
<b>Poller version 21</b>
<ul>
<li>
	YAML Static Data updater made compatible with Phoebe Static Data
</li>
<li>
	Default ME & TE values for Tech II BPCs adjusted to 0/0
</li>
<li>
	Poller made compatible with Phoebe IndustryJobs endpoint changes
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2014.10.22</div></td>
<td valign="top" class="tab">
<b>0.1.48 beta:</b><br>
<b>Poller version 20</b>
<ul>
<li>
	New API Endpoint: Blueprints
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2014.09.02</div></td>
<td valign="top" class="tab">
<b>0.1.47 beta:</b><br>
<b>Poller version 19</b>
<ul>
<li>
	New Feature: EVE Online Single Sign-On
</li>
<li>
        Bug fixes
</li>
<li>
        Performance fixes
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2014.08.28</div></td>
<td valign="top" class="tab">
<b>0.1.46 beta:</b><br>
<b>Poller version 19</b>
<ul>
<li>
	New Feature: Profit Explorer & Profit chart - these will be available under Database.
</li>
<li>
	New Feature: API character Self-register. Users can link characters to their LMeve username using their personal API keys
</li>
<li>
	Change recommended by CCP: Poller now puts its user agent in HTTP headers
</li>
<li>
        Bug fixes
</li>
</ul>
</td></tr>








 
    
<!-- end of changelog -->

<?php

//HISTORIA ZMIAN

$history=$_GET['history'];
if (isset($history)) {
?>

<tr><td class="tab" width="180">
<div class="tytul2">2014.08.18</div></td>
<td valign="top" class="tab">
<b>0.1.45 beta:</b><br>
<b>Poller version 19</b>
<ul>
<li>
	New Feature: Location Kits. Players doing POS logistic don't want to know individual tasks being run in each POS Facility. Instead, they would like to know the materials required for all users of the specific facility. Well, now they know :-)
</li>
<li>
	Security update: CSRF protection added to all forms.
</li>
<li>
	Bug fix in YAML updater: -all and --all are not exactly the same thing.
</li>
<li>
	Bug fix in Kit builder: invention post Crius uses Base Materials instead of Extra Materials.
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2014.08.06</div></td>
<td valign="top" class="tab">
<b>0.1.44 beta:</b><br>
<b>Poller version 19</b>
<ul>
<li>
	First CREST endpoint in poller: /market/prices/
</li>
<li>
	Poller no longer updates the last update timestamp in "apistatus" table, when using cached data.
</li>
<li>
	New IndustryJobs.xml endpoint does not return productTypeID until the job is delivered, so it's now updated using data from IndustryJobsHistory.xml
        Additionally, if manufacturing job has its productTypeID=0, it is looked up in blueprints.yaml table at insert time.
</li>
<li>
	Bug fix in "Database"
</li>
<li>
	Prices obtained from CREST are now visible in "Database"
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2014.07.29</div></td>
<td valign="top" class="tab">
<b>0.1.43 beta:</b><br>
<b>Poller version 18</b>
<ul>
<li>
	Bug fixes in "Database"
        <ul>
            <li>
                    Fixed material requirements so it is compatible with Crius
            </li>
            <li>
                    Fixed Invention and Manufacturing cost estimation
            </li>
            <li>
                    Links in Bonuses and Traits are now translated, so they correctly point to LMeve "Database"
            </li>
        </ul>
</li>
<li>
	Bug fix in "Activity"
        <ul>
            <li>
                    Incursions and Ratting graphs were not displayed, if there wasn't at least 1 mission run in a given month.
            </li>
        </ul>
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2014.07.28</div></td>
<td valign="top" class="tab">
<b>0.1.42 beta:</b><br>
<b>Poller version 18</b>
<ul>
<li>
	Facilities.xml.aspx endpoint added
</li>
<li>
	Facilities and Starbases are now queried for in Locations endpoint
</li>
<li>
	POS list in Inventory now shows Tower Name
</li>
<li>
	"Mobile Laboratiores & Assembly Arrays" renamed to "Industry Facilities" in Inventory
</li>
<li>
    "Industry Facilities" now show Facilites obtained from new API endpoint instead of manual editing (thanks again, CCP FoxFour!)
</li>
<li>
    Default ME and TE for Tech II have been adjusted to 2 (used to be -4)
</li>
</ul>
</td></tr>


<tr><td class="tab" width="180">
<div class="tytul2">2014.07.24</div></td>
<td valign="top" class="tab">
<b>0.1.41 beta:</b><br>
<b>Poller version 17</b>
<ul>
<li>
	Field name changes in login template, autocapitalize=off for Safari in iOS
</li>
<li>
	Fix in "Ore Value" chart - Crius SDE has compressed ores mixed with uncompressed. Solution: filter added in SQL query.
</li>
<li>
	YAML importer now imports ship Traits and Bonuses from typeIDs.yaml
</li>
<li>
	"Database" now shows ship Traits and Bonuses
</li>
<li>
	"Database" now shows other meta types of the same item
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2014.07.24</div></td>
<td valign="top" class="tab">
<b>0.1.40 beta:</b><br>
<b>Poller version 17</b>
<ul>
<li>
	Crius changes in material counting formula
</li>
<li>
	EVE API poller adjusted to changes
        <ul>
            <li>Changes in /corp/IndustryJobs.xml</li>
            <li>New endpoint /corp/IndustryJobsHistory.xml</li>
        </ul>
</li>
<li>YAML importer now imports blueprints.yaml</li>
<li>Code fixes:
    <ul>
        <li>fixes in include_once relative paths in poller and CLI scripts</li>
        <li>short php open tags changed into long open tags</li>
        <li>static data tables now use CamelCase as in Fuzzysteve's conversion</li>
    </ul>
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2014.06.11</div></td>
<td valign="top" class="tab">
<b>0.1.31 beta:</b><br>
<b>Poller version 16</b>
<ul>
<li>
	New Feature: POCOs
	<ul>
		<li>
			Locations API implemented in Poller
		</li>
		<li>
			POCOs now show which planet they are on (using Locations API)
		</li>
	</ul>
</li>
<li>
	New Feature: PVE activity
	<ul>
		<li>
			Ratting graph is now displayed along Missions and Incursions in PVE Activity under Activity tab
		</li>
	</ul>
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2014.06.05</div></td>
<td valign="top" class="tab">
<b>0.1.30 beta:</b><br>
<b>Poller version 15</b>
<ul>
<li>
	Rolled back ME and TE (formerly PE) formulas that were postponed until EVE Online: Crius
	<ul>
		<li>
			These changes will be included when Crius is released
		</li>
	</ul>
</li>
<li>
	New Feature: POCOs
	<ul>
		<li>
			New Customs Office API endpoint in Poller (thanks, CCP FoxFour!)
		</li>
		<li>
			POCOs are now visible in Inventory module
		</li>
	</ul>
</li>
<li>
	New Feature: PVE activity
	<ul>
		<li>
			Mission and incursion graphs are now displayed in PVE Activity under Activity tab
		</li>
	</ul>
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2014.01.15</div></td>
<td valign="top" class="tab">
<b>0.1.29 beta:</b><br>
<b>Poller version 14</b>
<ul>
<li>
New feature: POS & Labs
    <ul>
    <li>
    POS Towers & Labs under Inventory
    </li>
    <li>
    Labs can be assigned to Tasks
    </li>
    <li>
    Labs will now show in Kits (so you know which labs to refill)
    </li>
    </ul>
</li>
<li>
Better Characters and Users modules for admins
    <ul>
    <li>
    Characters module will show red icon next to disabled users
    </li>
    <li>
    Characters that quit corp can now be deleted
    </li>
    <li>
    Disabled users now show on the bottom of the User list
    </li>
    </ul>
</li>
<li>
Bug fixes
    <ul>
    <li>
    HTML code in forms now contains proper method="get" or method="post"
    </li>
    </ul>
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2014.01.03</div></td>
<td valign="top" class="tab">
<b>0.1.28 beta:</b><br>
<b>Poller version 14</b>
<ul>
<li>
New Feature: built-in Wiki
</li>
<li>
Bug fixes
</li>
</ul>
</td></tr> 

<tr><td class="tab" width="180">
<div class="tytul2">2013.12.29</div></td>
<td valign="top" class="tab">
<b>0.1.27 beta:</b><br>
<b>Poller version 14</b>
<ul>
<li>
Error handling changes in API Poller. Random errors are ignored 10 times before permanent locking of an API key.
</li>
</ul>
</td></tr> 

<tr><td class="tab" width="180">
<div class="tytul2">2013.12.03</div></td>
<td valign="top" class="tab">
<b>0.1.27 beta:</b><br>
<b>Poller version 13</b>
<ul>
<li>
JQuery animations added in "Buy Calc" and "Inventory"
</li>
<li>
Ore Value Table moved from "Market" to "Database" for consistency (it's reference information)
</li>
<li>
Submenus have been reorganized
</li>
<li>
Fixes in interface for multiple corporations
</li>
<li>
Bug fixes in poller (RESTful error codes are now interpreted as well)
</li>
<li>
New CSS skin by famous Rixx Javix (<a href="https://twitter.com/RixxJavix" target="_blank">@rixxjavix</a> from <a href="http://eveoganda.blogspot.com/" target="_blank">EVEOGANDA</a>)
</li>
</ul>
</td></tr>  

<tr><td class="tab" width="180">
<div class="tytul2">2013.11.22</div></td>
<td valign="top" class="tab">
<b>0.1.26 beta:</b><br>
<b>Poller version 11</b>
<ul>
<li>
Ore Value Table added in "Market" module. Miners will now know which ore pays best.
</li>
<li>
Bug fixes
</li>
</ul>
</td></tr> 

<tr><td class="tab" width="180">
<div class="tytul2">2013.11.12</div></td>
<td valign="top" class="tab">
<b>0.1.25 beta:</b><br>
<b>Poller version 11</b>
<ul>
<li>
Adjustments in the Database module style (table widths). Listing unpublished items in search (in italics).
</li>
<li>
Stock tracking field (amount + track true/false) added in Database
</li>
<li>
Inventory module goes live!
<ul>
<li>
    It shows actual and required amounts + a percentage bar
</li>
<li>
    It is updated every 6 hours (limitation of Assets API)
</li>
    <li>Click header to toggle the group visible</li>
    <li>Cookies will remember which groups were visible previously</li>
</ul>
</li>
<li>
The tables in Overview module got overhauled with new CSS style
</li>
<li>
In Buy Calculator all makret groups are now collapsed by default
<ul>
    <li>Click header to toggle the group visible</li>
    <li>Cookies will remember which groups were visible previously</li>
</ul>
</li>
</ul>
</td></tr>  

<tr><td class="tab" width="180">
<div class="tytul2">2013.09.17</div></td>
<td valign="top" class="tab">
<b>0.1.24 beta:</b><br>
<b>Poller version 11</b>
<ul>
<li>
Tasks and Character pages will now show all industry jobs currently in progress. All times = EVE time.
</li>
<li>
You can now display a kit for the remaining number of runs on a Task.
</li>
<li>
API poller statistics graph on Settings page.
</li>
<li>
Character owner is now displayed on Character pages.
</li>
<li>
Bug fixes, including a nasty bug in Kit builder (thanks Crysis!).
</li>
</ul>
</td></tr>   

<tr><td class="tab" width="180">
<div class="tytul2">2013.08.23</div></td>
<td valign="top" class="tab">
<b>0.1.23 beta:</b><br>
<b>Poller version 11</b>
<ul>
<li>
Now using YAML Static Data for 3D preview - all ships display correctly
</li>
<li>
jQuery, jQueryUI and chart.js are now loaded in the default template
</li>
<li>
jQueryUI "Accordion" animated control used for Links
</li>
<li>
Table styling bug fixed in displayKit();
</li>
<li>
Display kit link is now under a "plastic wrap" icon instead of text label
</li>
<li>
First graph in Wallet module
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.08.19</div></td>
<td valign="top" class="tab">
<b>0.1.22 beta:</b><br>
<b>Poller version 11</b>
<ul>
<li>
3D preview for ships - using CCP WebGL technology
<ul>
<li>
If your browser supports WebGL a "3D" button will appear near ship name in Database module
</li>
<li>
By default the 3d preview opens inside LMeve window
</li>
<li>
Press "Fullscreen" button to maximize the 3D viewport
</li>
<li>
Matching ships to their models and texture packs will take some time - CCP has different naming scheme in game and in 3D resource files
</li>
<li>
All <a href="index.php?id=10&id2=0&marketGroupID=1377">Tech II Battleships</a> added for testing purposes
</li>
<li>
All <a href="index.php?id=10&id2=0&marketGroupID=1612">Special Edition Ships</a> added for testing purposes
</li>
<li>
Yes, you CAN now officially spin ships in LMeve. You don't have to log on to EVE just to spin ships ;-)
</li>
</ul>
</li>
<li>
    Manufacturing cost calculation feature in Database module
</li>
<li>
    Invention cost calculation feature in Database module
</li>
</ul>
</td></tr>
    
<tr><td class="tab" width="180">
<div class="tytul2">2013.08.11</div></td>
<td valign="top" class="tab">
<b>0.1.21 beta:</b><br>
<b>Poller version 11</b>
<ul>
<li>
Kit feature now in Tasks module!
</li>
<li>
Overview can now be filtered by your characters only
</li>
<li>
    No need to relog when IP address changed<br />
    - friendly to mobile devices on the move
</li>
<li>
Even more bugfixes
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.07.31</div></td>
<td valign="top" class="tab">
<b>0.1.20 beta:</b><br>
<b>Poller version 11</b>
<ul>
<li>
Bug fixes
</li>
<li>
Own characters now show on top of the Timesheet
</li>
<li>
New even/odd CSS style for tables applied on Timesheet
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.07.24</div></td>
<td valign="top" class="tab">
<b>0.1.19 beta:</b><br>
<b>Poller version 11</b>
<ul>
<li>
Industry jobs in progress show a different color on Tasks percent bars
</li>
<li>
Market module now shows in-game Market Orders
<ul>
    <li>shows system, price and sold volume</li>
    <li>shows ISK remaining on market</li>
</ul>
</li>
<li>
Administrators can now edit hours-per-point without phpMyAdmin ;-)
</li>
<li>
Disabling "Fetch prices" in Database will now delete existing price data as well
</li>
<li>
Clicking the type icon or type name in Tasks will now open database entry for that item
</li>
<li>
Optimizations in Tasks SQL query - reduced query time about 10 times
</li>
<li>
New even/odd CSS style for tables (most old tables don't use it yet; they will be updated soon)<br />
Feel free to have a look under Tasks and Market tabs.
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.07.15</div></td>
<td valign="top" class="tab">
<b>0.1.18 beta:</b><br>
<b>Poller version 11</b>
<ul>
<li>
Poller now fetches contract and items information from API
</li>
<li>
Market panel now matches in-game cotnracts with buyback orders in LMeve
</li>
<li>
Pressing "Enter" in Buy Calculator will now move focus to the next cell
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.06.28</div></td>
<td valign="top" class="tab">
<b>0.1.17 beta:</b><br>
<b>Poller version 10</b>
<ul>
<li>
Under-the-hood optimizations for Database feature
</li>
<li>
Database material panel rewritten in AJAX
</li>
<li>
Database now saves the default ME and PE for Cost Calculation*
</li>
</ul>
* Cost calculation feature to be added in a future release
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.06.08</div></td>
<td valign="top" class="tab">
<b>0.1.16 beta:</b><br>
<b>Poller version 10</b>
<ul>
<li>
Buy Calculator feature added under Market
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.06.05</div></td>
<td valign="top" class="tab">
<b>0.1.15 beta:</b><br>
<b>Poller version 10</b>
<ul>
<li>
Added API Poller reset button for admins (in case it hangs). Prior to that, a hung poller would have to be manually reset, which required logging in to the server.
</li>
<li>
Support for external Data Dump db schema (ie. outside of own LMeve schema)
</li>
<li>
Odyssey 1.00 Data Dump imported
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.05.27</div></td>
<td valign="top" class="tab">
<b>0.1.14 beta:</b><br>
<b>Poller version 10</b>
<ul>
<li>
Overview fix - correct activity is now set when Assign button was clicked
</li>
<li>
Tasks now show amount of items already done (without the need to hover and look for tooltips)
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.05.20</div></td>
<td valign="top" class="tab">
<b>0.1.13 beta:</b><br>
<b>Poller version 10</b>
<ul>
<li>
Added the Database module:
</li>
<ul>
<li>
You can explore the database using familiar market tree
</li>
<li>
Search will find items which names contain the entered string
</li>
<li>
Item view shows all statistics, materials and attributes of selected item
</li>
<li>
Shows eve-central.com price data for preconfigured item types
</li>
<li>
Allows to create a production task by a single click of a button
</li>
</ul>
<li>
Links to Database added in Overview and Tasks
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.05.07</div></td>
<td valign="top" class="tab">
<b>0.1.12 beta:</b><br>
<b>Poller version 10</b>
<ul>
<li>
Adding a Tech II manufacturing task will create tasks for copying and invention automatically
</li>
<li>
Changes to Overview "By Item": it now shows all activities, and not only manufacturing
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.05.07</div></td>
<td valign="top" class="tab">
<b>0.1.11 beta:</b><br>
<b>Poller version 10</b>
<ul>
<li>
First public beta version!
</li>
<li>
Full HTTPS support - you can import <a href="srwpodbiurkiem_ca.cer">this CA cert</a> to avoid HTTPS warnings
</li>
<li>
Task editing now works as intended
</li>
<li>
Wallet summary now includes contracts
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.04.30</div></td>
<td valign="top" class="tab">
<b>0.0.10 alpha 1:</b><br>
<b>Poller version 10</b>
<ul>
<li>
Tasks panel added. ToDo: Task editing.
</li>
<li>
Poller now updates existing IndustryJobs with completion status - required for Invention Success Ratio
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.04.25</div></td>
<td valign="top" class="tab">
<b>0.0.9 alpha 1:</b><br>
<b>Poller version 9</b>
<ul>
<li>
Character tab added:
</li>
<ul>
<li>
Shows a big portrait, name, title, base and when character has joined the corp.
</li>
<li>
Shows a summary of item types made by the character in the last month.
</li>
</ul>
<li>
Today tab removed (because it was useless!)
</li>
<li>
Number of jobs added to Timesheet and Character tabs
</li>
<li>
Added proper copyright notices
</li>
<li>
Fixed a bug in Wallet SQL query. Now properly shows buy/sell on all corp wallets
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.04.15</div></td>
<td valign="top" class="tab">
<b>0.0.8 alpha 1:</b><br>
<b>Poller version 9</b>
<ul>
<li>
Complete security subsystem revamp:
</li>
<ul>
<li>
Every piece of code has been altered to use the new system!
</li>
<li>
Users can now have one or more roles
</li>
<li>
Roles can have one or more Rights
</li>
<li>
Each app module can have a set of required Rights
</li>
</ul>
<li>
index.php state tree has been rewritten for clarity
</li>
<li>
Rights and Roles Management have been added under "Users"
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.04.05</div></td>
<td valign="top" class="tab">
<b>0.0.6 alpha 1:</b><br>
<b>Poller version 9</b>
<ul>
<li>
Task Overview tab added</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.04.05</div></td>
<td valign="top" class="tab">
<b>0.0.5 alpha 1:</b><br>
<b>Poller version 9</b>
<ul>
<li>
Fixed a bug in poller ragarding walking Wallet Journal
</li>
<li>
Poller now downloads delecter market prices feed from eve-central.com (every 60 minutes).
</li>
<li>
Added configuration table for market feed with TypeIDs of minerals, materials and products (by default 147 typeIDs)
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.04.02</div></td>
<td valign="top" class="tab">
<b>0.0.5 alpha 1:</b><br>
<b>Poller version 8</b>
<ul>
<li>
Wallet now contains current balance
</li>
<li>
ISK flow column now contains all wallet entry types (Tax from bounties, Contracts, GM ISK transfer...)
</li>
<li>
Fixed a bug in SQL schema - no PRIMARY KEY in apiwalletjournal table caused poller to insert duplicate records
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.04.02</div></td>
<td valign="top" class="tab">
<b>0.0.4 alpha 1:</b><br>
<b>Poller version 8</b>
<ul>
<li>
New corp feeds: AssetList.xml FacWarStats.xml ContainerLog.xml ContactList.xml
</li>
<li>
To decode Asset locations new database table was merged from Static Data Dump: mapSolarSystems + staStations
</li>
<li>
Statistics added on Timesheet
</li>
<li>
Previous and next month navigation added to Timesheet and Wallet
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.04.02</div></td>
<td valign="top" class="tab">
<b>0.0.3 alpha 1:</b><br>
<b>Poller version 7</b>
<ul>
<li>
New corp feed: AccountBalance.xml
</li>
<li>
New global feeds: RefTypes.xml ErrorList.xml ConquerableStationsList.xml
</li>
<li>
Poller now saves polling time for statistics purposes
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.03.27</div></td>
<td valign="top" class="tab">
<b>0.0.3 alpha 1:</b><br>
<b>Poller version 6</b>
<ul>
<li>
Added totals in Timesheet
</li>
<li>
Added 1 point = xxx ISK in Timesheet
</li>
<li>
Wallet panel added
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.03.26</div></td>
<td valign="top" class="tab">
<b>0.0.2 alpha 1:</b><br>
<b>Poller version 6</b>
<ul>
<li>
Poller now properly handles HTTPS errors (for example when CCP disables the API for a while)
</li>
<li>
Poller now properly handles Wallet Transactions (separate feed for each accountKey)
</li>
<li>
Login window CSS overhauled to look like the rest of the app
</li>
<li>
Added a table with points to Timesheet
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.03.19</div></td>
<td valign="top" class="tab">
<b>0.0.2 alpha 1:</b><br>
<ul>
<li>
Timesheet rearranged, looks more like the one on Aideron website
</li>
<li>
Settings module now shows API information and API feeds status
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.03.17</div></td>
<td valign="top" class="tab">
<b>0.0.1 alpha 1:</b><br>
<ul>
<li>
CSS overhauled, looks like Aideron Robotics website
</li>
<li>
Messages module translated and now working
</li>
<li>
Users module translated
</li>
<li>
About page translated
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.03.16</div></td>
<td valign="top" class="tab">
<b>0.0.0 alpha 1:</b><br>
<ul>
<li>
Initial GUI revision
</li>
<li>
API Poller downloads the following feeds: APIKeyInfo.xml, MemberTracking.xml, IndustryJobs.xml,	CorporationSheet.xml, WalletJournal.xml, WalletTransactions.xml, MarketOrders.xml<br>
<br>
API calls are optimized, but database inserts are not.<br>
<br>
Still missing some feeds.<br>
</li>
<li>
Classic CSS
</li>
<li>
Very simple timesheet added
</li>
<li>
Began UI translation from polish to english
</li>
</ul>
</td></tr>

<tr><td class="tab" width="180">
<div class="tytul2">2013.03.12</div></td>
<td valign="top" class="tab">
<b></b><br>
<ul>
<li>
Project started. First version of API Poller written in PHP
</li>
<li>
no GUI yet
</li>
</ul>
</td></tr>

<?php
echo('<tr><td><a href="?id=254">Close complete history</a></td></tr>');
} else {
	echo('<tr><td><a href="?id=254&history=1">Show complete history</a></td></tr>');
}
?>
</table>

<table width="90%" border="0" cellspacing="2" cellpadding="0">
<tr><td class="tab-header"><div class="tytul2">
Map of LMeve:
</div></td><tr></table>

<table width="90%" border="0" cellspacing="2" cellpadding="0">
<tr>
<td class="tab-header"><b>Timesheet</b></td>
<td class="tab-header"><b>Statistics</b></td>
<td class="tab-header"><b>Tasks</b></td>
<td class="tab-header"><b>Characters</b></td>
<td class="tab-header"><b>Database</b></td>
<td class="tab-header"><b>Inventory</b></td>
<td class="tab-header"><b>Market</b></td>
<td class="tab-header"><b>Messages</b></td>
<td class="tab-header"><b>Settings</b></td>
<td class="tab-header"><b>Wallet</b></td>
<td class="tab-header"><b>Users</b></td>
</tr>
<tr>
<td class="tab">timesheet, wages</td>
<td class="tab">various corp stats</td>
<td class="tab">task list</td>
<td class="tab">character-to-user mapping</td>
<td class="tab">EVE static DB</td>
<td class="tab">corp asset list</td>
<td class="tab">market data</td>
<td class="tab">built in messaging system</td>
<td class="tab">options</td>
<td class="tab">corp wallet data</td>
<td class="tab">user options</td>
</tr>
</table>

<table width="90%" border="0" cellspacing="2" cellpadding="0">
<tr><td class="tab-header"><div class="tytul">
Licenses:
</div></td><tr></table>

<table width="90%" border="0" cellspacing="2" cellpadding="0">
<tr><td class="tab">
 
<h3>CCP Copyright Notice</h3>
<pre>EVE Online, the EVE logo, EVE and all associated logos and designs are the intellectual
property of CCP hf. All artwork, screenshots, characters, vehicles, storylines, world facts or other recognizable features
of the intellectual property relating to these trademarks are likewise the intellectual property of CCP hf.
EVE Online and the EVE logo are the registered trademarks of CCP hf. All rights are reserved worldwide.
All other trademarks are the property of their respective owners. CCP hf. has granted permission to Lukasz Pozniak to use EVE Online
and all associated logos and designs for promotional and information purposes on its website but does not endorse,
and is not in any way affiliated with, Lukas' Manager for EVE (LMeve). CCP is in no way responsible for the content
on or functioning of this website, nor can it be liable for any damage arising from the use of this website. </pre>

<h3>Contains CCP WebGL</h3>
<pre></pre>        
        
<h3>This app contains LMframework v3</h3>
<div style="font-family: monospace;">LM Framework v3<br/>
								<br/>
	A simple PHP based application framework.<br/>
	<br/>
	Contact: pozniak.lukasz@gmail.com<br/>
	<br/>
	Copyright (c) 2005-2013, Lukasz Pozniak<br/>
	All rights reserved.<br/>
<br/>
	Redistribution and use in source and binary forms, with or without modification,<br/>
	are permitted provided that the following conditions are met:<br/>
	<br/>
	Redistributions of source code must retain the above copyright notice,<br/>
	this list of conditions and the following disclaimer.<br/>
	Redistributions in binary form must reproduce the above copyright notice,<br/>
	this list of conditions and the following disclaimer in the documentation<br/>
	and/or other materials provided with the distribution.<br/>
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"<br/>
	AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,<br/>
	THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE<br/>
	ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS<br/>
	BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,<br/>
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT<br/>
	OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;<br/>
	OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,<br/>
	WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)<br/>
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED<br/>
	OF THE POSSIBILITY OF SUCH DAMAGE.<br/>
</div>

<h3>jQuery, jQuery UI</h3>
<pre>Copyright 2013 jQuery Foundation and other contributors
http://jquery.com/

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.</pre>

<h3>chart.js</h3>
<pre>Copyright (c) 2013 Nick Downie

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"),
to deal in the Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish, distribute, sublicense,
and/or sell copies of the Software, and to permit persons to whom the Software
is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH
THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.</pre>

<h3>StackedBar chart plugin for chart.js</h3>
<strong>https://github.com/Regaddi/Chart.StackedBar.js</strong>

<pre>The MIT License (MIT)

Copyright (c) 2015 @Regaddi

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.</pre>

</td></tr>
</table>

</div>
