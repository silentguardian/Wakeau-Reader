<?php

echo '<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>';

$filename = 'file.txt';

$content = file_get_contents($filename);
$lines = explode("\n", $content);

$questions = array(
	'title' => '',
	'level' => 0,
);

$question = 0;
$answers = 0;

foreach ($lines as $line)
{
	$line = preg_replace('~([abcd]) \[ \]~', '#\\1#', preg_replace('~\s+~', ' ', trim($line)));

	if (preg_match('~^(\d+) ([abcd])$~', $line, $match))
		$questions[$match[1]]['answer'] = $match[2];
	elseif (preg_match('~^\d+ (?!marks)~', $line))
	{
		if ($question == 50)
			continue;

		$pre_question = ($question > 19 && $question < 30) ? 'Who said this?' : 'Choose the best answer.';
		$questions[++$question]['question'] = $pre_question . "\n" . substr($line, strpos($line, ' ') + 1);
	}
	elseif (preg_match('~^#[abcd]~', $line))
	{
		preg_match_all('~#([abcd])# ([^#]+)~', $line, $matches);

		foreach ($matches[0] as $key => $dummy)
		{
			$questions[$question]['options'][$matches[1][$key]] = trim($matches[2][$key]);
			$answers++;
		}
	}
}

$filename = $questions['level'] . '_' . str_replace(' ', '_', strtolower($questions['title'])) . '.txt';
file_put_contents('out/' . $filename, serialize($questions));

echo $answers;

echo '<pre>';
print_r($questions);
echo '</pre>';

exit();