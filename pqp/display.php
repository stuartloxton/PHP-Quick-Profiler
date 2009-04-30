<?php

/* - - - - - - - - - - - - - - - - - - - - - - - - - - - 

 Title : HTML Output for Php Quick Profiler
 Author : Created by Ryan Campbell
 URL : http://particletree.com/features/php-quick-profiler/

 Last Updated : April 22, 2009

 Description : This is a horribly ugly function used to output
 the PQP HTML. This is great because it will just work in your project,
 but it is hard to maintain and read. See the README file for how to use
 the Smarty file we provided with PQP.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

function displayPqp($output, $config) {
	
	$cssUrl = $config.'css/pQp.css';
	$logCount = count($output['logs']['console']);
	$fileCount = count($output['files']);
	$memoryUsed = $output['memoryTotals']['used'];
	$queryCount = $output['queryTotals']['count'];
	$speedTotal = $output['speedTotals']['total'];	
	?>
	
	
	
<script type="text/javascript" charset="utf-8">
	files = []
	if(typeof(jQuery) != 'function') {
		files.push('<?=$config?>js/jquery-1.3.2.min.js');
		files.push('<?=$config?>js/jquery-ui-1.7.1.custom.min.js');
	} else if(typeof(jQuery.fn.resizable) != 'function') {
		files.push('<?=$config?>js/jquery-ui-1.7.1.custom.min.js');
	}
	
	if( files.length ) {
		var head = document.getElementsByTagName("head")[0];
		for(filei = 0; filei < files.length; filei++) {
			c = document.createElement('script');
			c.type = 'text/javascript';
			c.src = files[filei];
			head.appendChild(c);
		}
	}
</script>
<script type="text/javascript">
	var PQP_DETAILS = true;
	
	jQuery.noConflict();
	
	addEvent(window, 'load', loadCSS);
	
	jQuery(function() {
		jQuery('#pqp-console .side td').click(function() {
			if( jQuery(this).css('opacity') == '0.2' ) { var topac = 1; } else { var topac = 0.2 };
			jQuery(this).fadeTo(200, topac);
			jQuery('tr.log-' + this.className.split(' ')[1].substr(1)).toggle();
		}).css('cursor', 'pointer');
		bindResizer();
	});
	
	function bindResizer() {
		jQuery('#pQp').resizable({
			handles: 'n',
			alsoResize: '#pqp-console, #pqp-speed, #pqp-queries, #pqp-memory, #pqp-files',
			minHeight: 109,
			stop: function() {
				jQuery('#pqp-console, #pqp-speed, #pqp-queries, #pqp-memory, #pqp-files').css('width', 'auto');
				createCookie('pqp-height', jQuery('#pQp').height());
			}
		});
	}
	
	function unbindResizer() {
		jQuery('#pQp').resizable('destroy');
	}
	
	function createCookie(name,value,days) {
		if (days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		}
		else var expires = "";
		document.cookie = name+"="+value+expires+"; path=/";
	}

	function readCookie(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	}

	function eraseCookie(name) {
		createCookie(name,"",-1);
	}
	
	
	function changeTab(tab) {
		var pQp = document.getElementById('pQp');
		hideAllTabs();
		addClassName(pQp, tab, true);
		if( !PQP_DETAILS ) toggleDetails();
		createCookie('pqp-tab', tab);
	}
	
	function hideAllTabs() {
		var pQp = document.getElementById('pQp');
		removeClassName(pQp, 'console');
		removeClassName(pQp, 'speed');
		removeClassName(pQp, 'queries');
		removeClassName(pQp, 'memory');
		removeClassName(pQp, 'files');
	}
	
	function toggleDetails( forceOpen ){
		var container = document.getElementById('pqp-container');
		
		if(PQP_DETAILS && !forceOpen){
			addClassName(container, 'hideDetails', true);
			jQuery('#pQp').height(76).css('top', 'auto');
			unbindResizer();
			PQP_DETAILS = false;
			createCookie('pqp-details', false);
		} else{
			removeClassName(container, 'hideDetails');
			var xTarg = (readCookie('pqp-height') && readCookie('pqp-height') > 76) ? readCookie('pqp-height') : 109;
			jQuery('#pQp').css('height', xTarg + 'px' );
			jQuery('#pqp-console, #pqp-speed, #pqp-queries, #pqp-memory, #pqp-files').height( xTarg - 107);
			bindResizer();
			PQP_DETAILS = true;
			createCookie('pqp-details', true);
		}
	}
	function toggleHeight(){
		var container = document.getElementById('pqp-container');
		
		if(PQP_HEIGHT == "short"){
			addClassName(container, 'tallDetails', true);
			PQP_HEIGHT = "tall";
		}
		else{
			removeClassName(container, 'tallDetails');
			PQP_HEIGHT = "short";
		}
	}
	
	function loadCSS() {
		var sheet = document.createElement("link");
		sheet.setAttribute("rel", "stylesheet");
		sheet.setAttribute("type", "text/css");
		sheet.setAttribute("href", "<?=$cssUrl?>");
		document.getElementsByTagName("head")[0].appendChild(sheet);
		setTimeout(function(){document.getElementById("pqp-container").style.display = "block"}, 10);
		if(readCookie('pqp-tab')) {
			changeTab(readCookie('pqp-tab'));
		}
		if(readCookie('pqp-details') === 'false') {
			toggleDetails();
		} else {
			toggleDetails(true);
		}
	}
	
	function addClassName(objElement, strClass, blnMayAlreadyExist){
		jQuery(objElement).addClass(strClass);
	}
	
	function removeClassName(objElement, strClass){
	   jQuery(objElement).removeClass(strClass);
	}

	//http://ejohn.org/projects/flexible-javascript-events/
	function addEvent( obj, type, fn ) {
	  if ( obj.attachEvent ) {
	    obj["e"+type+fn] = fn;
	    obj[type+fn] = function() { obj["e"+type+fn]( window.event ) };
	    obj.attachEvent( "on"+type, obj[type+fn] );
	  } 
	  else{
	    obj.addEventListener( type, fn, false );	
	  }
	}
</script>

<div id="pqp-container" class="pQp" style="display:none">

<div id="pQp" class="console">
<table id="pqp-metrics" cellspacing="0">
<tr>
	<td class="green" onclick="changeTab('console');">
		<var><?=$logCount?></var>
		<h4>Console</h4>
	</td>
	<td class="blue" onclick="changeTab('speed');">
		<var><?=$speedTotal?></var>
		<h4>Load Time</h4>
	</td>
	<td class="purple" onclick="changeTab('queries');">
		<var><?=$queryCount?> Queries</var>
		<h4>Database</h4>
	</td>
	<td class="orange" onclick="changeTab('memory');">
		<var><?=$memoryUsed?></var>
		<h4>Memory Used</h4>
	</td>
	<td class="red" onclick="changeTab('files');">
		<var><?=$fileCount?> Files</var>
		<h4>Included</h4>
	</td>
</tr>
</table>

<div id="pqp-console" class="pqp-box">

<? if($logCount ==  0) { ?>
	<h3>This panel has no log items.</h3>
<? } else { ?>
<table class="side" cellspacing="0">
		<tr>
			<td class="alt1 tlog"><var><?=$output['logs']['logCount']?></var><h4>Logs</h4></td>
			<td class="alt2 terror"><var><?=$output['logs']['errorCount']?></var> <h4>Errors</h4></td>
		</tr>
		<tr>
			<td class="alt3 tmemory"><var><?=$output['logs']['memoryCount']?></var> <h4>Memory</h4></td>
			<td class="alt4 tspeed"><var><?=$output['logs']['speedCount']?></var> <h4>Speed</h4></td>
		</tr>
		</table>
		<table class="main" cellspacing="0">
		
		<?
		$class = '';
		foreach($output['logs']['console'] as $log) { ?>
			<tr class="log-<?=$log['type']?>">
				<td class="type"><?=$log['type']?></td>
				<td class="<?=$class?>">
			<? if($log['type'] == 'log') { ?>
				<div><pre><?=$log['data']?></pre></div>
			<? } elseif($log['type'] == 'memory') { ?>
				<div><pre><?=$log['data']?></pre> <em><?=$log['dataType']?></em>: <?=$log['name']?> </div>
			<? } elseif($log['type'] == 'speed') { ?>
				<div><pre><?=$log['data']?></pre> <em><?=$log['name']?></em></div>
			<? } elseif($log['type'] == 'error') { ?>
				<div><em>Line <?=$log['line']?></em> : <?=$log['data']?> <pre><?=$log['file']?></pre></div>
			<? } ?>
		
			</td></tr>
			<? if($class == '') $class = 'alt';
			else $class = '';
		} ?>
			
		</table>
<? } ?>

</div>

<div id="pqp-speed" class="pqp-box">

<? if($output['logs']['speedCount'] ==  0) { ?>
	<h3>This panel has no log items.</h3>
<? } else { ?>
	<table class="side" cellspacing="0">
		  <tr><td><var><?=$output['speedTotals']['total']?></var><h4>Load Time</h4></td></tr>
		  <tr><td class="alt"><var><?=$output['speedTotals']['allowed']?></var> <h4>Max Execution Time</h4></td></tr>
	</table>
	<table class="main" cellspacing="0">
		
		<? $class = '';
		foreach($output['logs']['console'] as $log) {
			if($log['type'] == 'speed') { ?>
				<tr class="log-<?=$log['type']?>">
				<td class="<?=$class?>">
					<div><pre><?=$log['data']?></pre> <em><?=$log['name']?></em></div>
				</td></tr>
				<? 
				if($class == '') $class = 'alt';
				else $class = ''; 
			}
		} ?>
			
		</table>
<? } ?>

</div>

<div id="pqp-queries" class="pqp-box">

<? if($output['queryTotals']['count'] ==  0) { ?>
	<h3>This panel has no log items.</h3>
<? } else { ?>
	<table class="side" cellspacing="0">
		  <tr><td><var><?=$output['queryTotals']['count']?></var><h4>Total Queries</h4></td></tr>
		  <tr><td class="alt"><var><?=$output['queryTotals']['time']?></var> <h4>Total Time</h4></td></tr>
		  <tr><td><var>0</var> <h4>Duplicates</h4></td></tr>
		 </table>
		<table class="main" cellspacing="0">
		
		<? $class = '';
		foreach($output['queries'] as $query) { ?>
			<tr>
				<td class="<?=$class?>"><?=$query['sql']?>
			<? if($query['explain']) { ?>
					<em>
						Possible keys: <b><?=$query['explain']['possible_keys']?></b> &middot; 
						Key Used: <b><?=$query['explain']['key']?></b> &middot; 
						Type: <b><?=$query['explain']['type']?></b> &middot; 
						Rows: <b><?=$query['explain']['rows']?></b> &middot; 
						Speed: <b><?=$query['time']?></b>
					</em>
			<? } ?>
			</td></tr>
			<? if($class == '') $class = 'alt';
			else $class = '';
		} ?>
			
		</table>
<? } ?>

</div>

<div id="pqp-memory" class="pqp-box">

<? if($output['logs']['memoryCount'] ==  0) { ?>
	<h3>This panel has no log items.</h3>
<? } else { ?>
	<table class="side" cellspacing="0">
		  <tr><td><var><?=$output['memoryTotals']['used']?></var><h4>Used Memory</h4></td></tr>
		  <tr><td class="alt"><var><?=$output['memoryTotals']['total']?></var> <h4>Total Available</h4></td></tr>
		 </table>
		<table class="main" cellspacing="0">
	
		<?
		$class = '';
		foreach($output['logs']['console'] as $log) {
			if($log['type'] == 'memory') { ?>
				<tr class="log-<?=$log['type']?>">
				<td class="<?=$class?>"><b><?=$log['data']?></b> <em><?=$log['dataType']?></em>: <?=$log['name']?></td>
				</tr>
				<? if($class == '') $class = 'alt';
				else $class = '';
			}
		} ?>
			
	</table>
<? } ?>

</div>

<div id="pqp-files" class="pqp-box">

<? if($output['fileTotals']['count'] ==  0) { ?>
	<h3>This panel has no log items.</h3>
<? } else { ?>
	<table class="side" cellspacing="0">
		  	<tr><td><var><?=$output['fileTotals']['count']?></var><h4>Total Files</h4></td></tr>
			<tr><td class="alt"><var><?=$output['fileTotals']['size']?></var> <h4>Total Size</h4></td></tr>
			<tr><td><var><?=$output['fileTotals']['largest']?></var> <h4>Largest</h4></td></tr>
		 </table>
		<table class="main" cellspacing="0">
		
		<? $class ='';
		foreach($output['files'] as $file) { ?>
			<tr><td class="<?=$class?>"><b><?=$file['size']?></b> <?=$file['name']?></td></tr>
			<? if($class == '') $class = 'alt';
			else $class = '';
		} ?>
			
		</table>
<? } ?>

</div>

	<table id="pqp-footer" cellspacing="0">
		<tr>
			<td class="credit">
				<a href="http://particletree.com" target="_blank">
				<strong>PHP</strong> 
				<b class="green">Q</b><b class="blue">u</b><b class="purple">i</b><b class="orange">c</b><b class="red">k</b>
				Profiler</a></td>
			<td class="actions">
				<a href="#" onclick="toggleDetails();return false">Details</a>
			</td>
		</tr>
	</table>
		
</div></div>

<? } ?>