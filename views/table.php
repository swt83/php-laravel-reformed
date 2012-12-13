<?php

echo '<table>';
echo '<thead>';
echo '<tr>';
foreach ($headers as $header)
{
	echo '<th>'.$header.'</th>';
}
echo '</tr>';
echo '</thead>';
echo '<tbody>';
foreach ($data as $record)
{
	echo '<tr>';
	foreach ($closure($record) as $value)
	{
		echo '<td>'.$value.'</td>';
	}
	echo '</tr>';
}
echo '</tbody>';
echo '</table>';

?>