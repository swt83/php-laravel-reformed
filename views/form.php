<?php 

echo Form::open();
echo $reformed::get_alert();
echo '<table>';
foreach ($scaffold as $field)
{
	// Determine what the value of the field will be.
	
	$value = '';
	if (isset($field['populate'])) $value = $field['populate']; // if scaffold gives default value
	if (isset($populate[$field['name']])) $value = $populate[$field['name']]; // if method gives default value
	if ($reformed::populate($field['name'], null)) $value = $reformed::populate($field['name']); // get latest input value
	
	// Determine what the attributes of the field will be.
	
	$attributes = array();
	if (isset($field['required'])) if ($field['required']) $attributes[] = 'required';
	if (isset($field['autofocus'])) if ($field['autofocus']) $attributes[] = 'autofocus';
	if (isset($field['placeholder'])) $attributes[] = array('placeholder' => $field['placeholder']);
	
	// Build the field.

	echo '<tr>';
	switch (isset($field['type']) ? $field['type'] : 'text')
	{
		case 'submit':
			echo '<th></th>';
			echo '<td>'.Form::submit($field['label']).'</td>';
			break;
		case 'radio':
			echo '<th>'.Form::label($field['name'], $field['label']).'</th>';
			echo '<td>';
			foreach ($field['options'] as $k => $v)
			{
				echo Form::radio($field['name'], $k, $k === $value ? true : false).' '.$v.'<br/>';
			}
			echo '</td>';
			break;
		case 'checkbox':
			if ($reformed::populate_array($field['name'], null)) $value = $reformed::populate_array($field['name']); // patch value using array method
			echo '<th>'.Form::label($field['name'], $field['label']).'</th>';
			echo '<td>';
			foreach ($field['options'] as $k => $v)
			{
				echo Form::checkbox($field['name'], $k, $k === $value ? true : false).' '.$v.'<br/>';
			}
			echo '</td>';
			break;
		case 'textarea':
			echo '<th>'.Form::label($field['name'], $field['label']).'</th>';
			echo '<td>'.Form::textarea($field['name'], $value, $attributes).'</td>';
			break;
		case 'password':
			echo '<th>'.Form::label($field['name'], $field['label']).'</th>';
			echo '<td>'.Form::password($field['name'], $attributes).'</td>';
			break;
		default:
			echo '<th>'.Form::label($field['name'], $field['label']).'</th>';
			echo '<td>'.Form::text($field['name'], $value, $attributes).'</td>';
			break;
	}
	echo '</tr>';
}
echo '</table>';
echo Form::close();

?>