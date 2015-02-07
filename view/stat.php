<?php
include APP_PATH . 'view/header.php';
?>
<script>
$(document).ready(function() {
	$( "#flt-date" ).datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-dd',
		altField: '#fltTimestamp',
		altFormat: '@',
	});
});
</script>  

<div class="flt-wrap">
<form action="" method="get">
	<input type="hidden" name="partner_id" value="<?=$partnerID?>">
	Дата: <input type="text" id="flt-date">
	<input type="hidden" name="fltTimestamp" id="fltTimestamp">
	Оператор: 
	<select name="operatorID">
		<option value="0">Выбор оператора...</option>
		<?php foreach ($Operators as $operatorID => $Operator):?>
			<option value="<?=$operatorID?>"><?=$Operator['name']?></option>
		<?php endforeach;?>
	</select>
	<button>Применить</button>
</form>
</div>
<?php if (!empty($Visitors)):?>
	<table width="300" cellpadding=3 border=1>
		<tr>
			<td>Дата</td>
			<td>Просмотры</td>
			<td>Уники</td>
		</tr>
	<?php
	$i = 0;
	foreach ($Visitors as $DayStat):
	?>
		<tr>
			<td><?=date("d/m/Y", $DayStat['dateAdd'])?></td>
			<td><?=$DayStat['cnt']?></td>
			<td><?=$UniqueVisitors[$i]['cnt']?></td>
		</tr>
	<?php 
	$i++;
	endforeach;?>
	</table>
<?php endif;?>

<?php if (!empty($VisitorsByTime)):?>
	<h2>Распределение по времени</h2>
	<?php
	foreach ($VisitorsByTime as $hour => $cnt){
		$hoursRow .= '<td align="center" width="30">' . $hour . '</td>';
		$visitsRow .= '<td align="center">' . $cnt . '</td>';
		$uniqueRow .= '<td align="center">' . $UniqueVisitorsByTime[$hour] . '</td>';
	}

	?>
	<table cellpadding=3 border=1>
		<tr><td>Время</td><?=$hoursRow?></tr>
		<tr><td>Просмотры</td><?=$visitsRow?></tr>
		<tr><td>Уникальные</td><?=$uniqueRow?></tr>
	</table>
	
	<div style="width:1000px">
		<div>
			<canvas id="canvasTimeline" height="250" width="600"></canvas>
		</div>
	</div>

<script>
var data = {
    labels: <?=json_encode(array_keys($UniqueVisitorsByTime))?>,
    datasets: [
        {
            label: "Уникальные визиты",
            fillColor: "rgba(220,220,220,0.5)",
            strokeColor: "rgba(220,220,220,0.8)",
            highlightFill: "rgba(220,220,220,0.75)",
            highlightStroke: "rgba(220,220,220,1)",
            data: <?=json_encode(array_values($UniqueVisitorsByTime))?>
        },
        {
            label: "Просмотры",
            fillColor: "rgba(151,187,205,0.5)",
            strokeColor: "rgba(151,187,205,0.8)",
            highlightFill: "rgba(151,187,205,0.75)",
            highlightStroke: "rgba(151,187,205,1)",
            data: <?=json_encode(array_values($VisitorsByTime))?>
        }
    ]
};

window.onload = function(){
		var ctx = document.getElementById("canvasTimeline").getContext("2d");
		window.myLine = new Chart(ctx).Bar(data, {
			responsive: true
		});
	}
</script>
<?php endif;?>
<?php
include APP_PATH . 'view/footer.php';
?>