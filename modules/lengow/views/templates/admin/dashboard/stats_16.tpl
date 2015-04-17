{*
 * Copyright 2014 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *	 http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 *  @author	   Ludovic Drin <ludovic@lengow.com> Romain Le Polh <romain@lengow.com>
 *  @copyright 2014 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}
<script type="text/javascript">
;$(document).ready(function() {
	function lengowLoadStats(key) {
		var ctx = $("#lengow-stats").get(0).getContext("2d");
		var data = {
			labels : data_stats[key].evolution_libelle ,
			datasets : [
				{
					fillColor : "rgba(151,187,205,0.5)",
					strokeColor : "rgba(151,187,205,1)",
					pointColor : "rgba(151,187,205,1)",
					pointStrokeColor : "#fff",
					data : data_stats[key].evolution_values
				}
			]
		};
		new Chart(ctx).Line(data);
	}
	// admin dashboard
	$('#table-feeds').hide();
	$('#lengow-info').hide();
	var lengowAPI = 'https://solution.lengow.com/routine/PrestaShop/dashboard_plugin_v2.php?token={$token|escape:"str"}&idClient={$id_customer|escape:"intval"}&idGroup={$id_group|escape:"intval"}&callback=?' ,
		table_feeds = '' ,
		select = '',
		data_stats = {};
	$.getJSON(lengowAPI, function(json) { 
		if (json.return == 'ok') {
			data_stats = json.stats;
			$('#lengow-load').hide();
			for(key in json.feeds) {
				table_feeds += '<tr>'
							 + '<td>' + json.feeds[key].id + '</td>'
							 + '<td>' + json.feeds[key].type + '</td>'
							 + '<td>' + json.feeds[key].diffuseur + '</td>'
							 + '<td>' + json.feeds[key].nom + '</td>'
							 + '<td>' + json.feeds[key].nbProduit + '</td>'
							 + '<td>' + json.feeds[key].nbProduitActif + '</td>'
							 + '</th>';
			}
			select = '<select name="lengow-change" id="lengow-change">';
			for(key in json.stats) {
				select += '<option value="' + key + '">' + json.stats[key].name + '</option>';
			}
			select += '</select>';
			$('#table-feeds tbody').html(table_feeds); 
			$('#table-feeds').show();
			$('#lengow-info').show();
			$('#lengow-change-select').html(select);
			$('#lengow-root').html('<canvas id="lengow-stats" width="587" height="400"></canvas>');
			$('#lengow-change').change(function() {
				var selected = $('#lengow-change').val();
				lengowLoadStats(selected);
			});
			lengowLoadStats(0);
		}
	});
});
</script>
<div class="panel widget">
	<header class="panel-heading">
		<i class="icon-bar-chart"></i> {l s='Dashboard Lengow' mod='lengow'}
		<span class="panel-heading-action">
			<div id="lengow-change-select"></div>
		</span>
	</header>
	<div id="lengow-load">
		{l s='Loading Lengow dashboard...' mod='lengow'}
	</div>
	<div id="lengow-info">
		<h5>{l s='Dashboard Lengow' mod='lengow'} <div id="lengow-change-select"></div></h5>
		<div id="lengow-root"></div>
	</div>
	<br />
	<div class="table-responsive">
		<table id="table-feeds" class="table table-striped">
			<thead>
				<tr>
					<th><span>{l s='ID' mod='lengow'}</span></th>
					<th><span>{l s='Type' mod='lengow'}</span></th>
					<th><span>{l s='Supplier' mod='lengow'}</span></th>
					<th><span>{l s='Name' mod='lengow'}</span></th>
					<th><span>{l s='Products' mod='lengow'}</span></th>
					<th><span>{l s='Enable\'s products' mod='lengow'}</span></th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>