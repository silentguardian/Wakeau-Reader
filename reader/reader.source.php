<?php

/**
 * @package Wakeau
 *
 * @author Selman Eser
 * @copyright 2014 Selman Eser
 * @license BSD 2-clause
 *
 * @version 1.0
 */

if (!defined('CORE'))
	exit();

function reader_main()
{
	global $core;

	$actions = array('list', 'export');

	$core['current_action'] = 'list';
	if (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], $actions))
		$core['current_action'] = $_REQUEST['action'];

	call_user_func($core['current_module'] . '_' . $core['current_action']);
}

function reader_list()
{
	global $core, $template;

	$request = db_query("
		SELECT id_book, title, level
		FROM rbook
		ORDER BY level, title");
	$template['books'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['books'][] = array(
			'id' => $row['id_book'],
			'title' => $row['title'],
			'level' => $row['level'],
		);
	}
	db_free_result($request);

	$template['page_title'] = 'Readers List';
	$core['current_template'] = 'reader_list';
}

function reader_export()
{
	global $core, $template, $user;

	if (!empty($_POST['cancel']))
		redirect(build_url('reader'));

	$id_book = !empty($_REQUEST['reader']) ? (int) $_REQUEST['reader'] : 0;

	$request = db_query("
		SELECT id_book, title, level
		FROM rbook
		WHERE id_book = $id_book
		LIMIT 1");
	while ($row = db_fetch_assoc($request))
	{
		$template['book'] = array(
			'id' => $row['id_book'],
			'title' => $row['title'],
			'level' => $row['level'],
		);
	}
	db_free_result($request);

	if (!isset($template['book']))
		fatal_error('The book requested does not exist!');

	if (!empty($_POST['export']))
	{
		if (empty($_POST['questions']) || !is_array($_POST['questions']))
			fatal_error('No questions were selected!');

		$questions = array();
		foreach ($_POST['questions'] as $question)
			$questions[] = (int) $question;

		$request = db_query("
			SELECT
				body, option_a, option_b,
				option_c, option_d, answer
			FROM rquestion
			WHERE id_question IN (" . implode(',', $questions) . ")
			ORDER BY RAND()");
		$data = array();
		while ($row = db_fetch_assoc($request))
		{
			$data[] = array(
				'q' => htmlspecialchars_decode($row['body'], ENT_QUOTES),
				'a' => htmlspecialchars_decode($row['option_a'], ENT_QUOTES),
				'b' => htmlspecialchars_decode($row['option_b'], ENT_QUOTES),
				'c' => htmlspecialchars_decode($row['option_c'], ENT_QUOTES),
				'd' => htmlspecialchars_decode($row['option_d'], ENT_QUOTES),
				't' => $row['answer'],
			);
		}
		db_free_result($request);

		$output = '';

		if ($_POST['export'] == 'Plain Text')
		{
			$counter = 0;
			$output .= htmlspecialchars_decode($template['book']['title'], ENT_QUOTES) . ' Test' . "\n\n";

			foreach ($data as $item)
			{
				$output .= ++$counter . '. ' . $item['q'] . "\n";

				foreach (array('a', 'b', 'c', 'd') as $o)
					$output .= $o . ') ' . $item[$o] . "\n";

				$output .= "\n";
			}

			$counter = 0;
			$output .= "\n" . 'Answer Key' . "\n\n";

			foreach ($data as $item)
				$output .= ++$counter . ') ' . $item['t'] . "\n";
		}
		elseif ($_POST['export'] == 'ExamView')
		{
			$counter = 0;
			$output .= htmlspecialchars_decode($template['book']['title'], ENT_QUOTES) . ' Test' . "\n\n";
			$output .= 'Multiple Choice' . "\n\n";

			foreach ($data as $item)
			{
				$output .= ++$counter . '. ' . $item['q'] . "\n";

				foreach (array('a', 'b', 'c', 'd') as $o)
					$output .= $o . '. ' . $item[$o] . "\n";

				$output .= 'ANS: ' . strtoupper($item['t']) . "\n";
			}
		}

		$file_name = $user['id'] . substr(md5(session_id() . mt_rand() . (string) microtime()), 0, 14);
		$file_alias = preg_replace('~[^A-Za-z0-9_]~', '', str_replace(' ', '_', strtolower(htmlspecialchars_decode($template['book']['title'], ENT_QUOTES)))) . '.txt';
		$file_dir = $core['site_dir'] . '/readers/' . $file_name;

		file_put_contents($file_dir, str_replace("\n", "\r\n", $output));

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $file_alias . '"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file_dir));

		ob_clean();
		flush();

		readfile($file_dir);
	}

	$request = db_query("
		SELECT
			id_question, body, option_a, option_b,
			option_c, option_d, answer
		FROM rquestion
		WHERE id_book = $id_book
		ORDER BY id_question");
	$template['questions'] = array();
	while ($row = db_fetch_assoc($request))
	{
		$template['questions'][] = array(
			'id' => $row['id_question'],
			'body' => $row['body'],
			'options' => array(
				'a' => $row['option_a'],
				'b' => $row['option_b'],
				'c' => $row['option_c'],
				'd' => $row['option_d'],
			),
			'answer' => $row['answer'],
		);
	}
	db_free_result($request);

	$template['page_title'] = 'Export Questions';
	$core['current_template'] = 'reader_export';
}

function reader_import()
{
	global $core;

	$files = array();
	$dir = $core['site_dir'] . '/out';

	if ($handle = @dir($dir))
	{
		while ($file = $handle->read())
		{
			if ($file[0] !== '.')
				$files[] = $file;
		}

		$handle->close();
	}

	foreach ($files as $file)
	{
		$data = unserialize(file_get_contents($dir . '/' . $file));

		$insert = array(
			'title' => "'" . htmlspecialchars($data['title'], ENT_QUOTES) . "'",
			'level' => $data['level'],
		);
		db_query("
			INSERT INTO rbook
				(" . implode(', ', array_keys($insert)) . ")
			VALUES
				(" . implode(', ', $insert) . ")");

		$id_book = db_insert_id();

		for ($i = 1; $i < 51; $i++)
		{
			$insert = array(
				'id_book' => $id_book,
				'body' => "'" . htmlspecialchars($data[$i]['question'], ENT_QUOTES) . "'",
				'option_a' => "'" . htmlspecialchars($data[$i]['options']['a'], ENT_QUOTES) . "'",
				'option_b' => "'" . htmlspecialchars($data[$i]['options']['b'], ENT_QUOTES) . "'",
				'option_c' => "'" . htmlspecialchars($data[$i]['options']['c'], ENT_QUOTES) . "'",
				'option_d' => "'" . htmlspecialchars($data[$i]['options']['d'], ENT_QUOTES) . "'",
				'answer' => "'" . $data[$i]['answer'] . "'",
			);
			db_query("
				INSERT INTO rquestion
					(" . implode(', ', array_keys($insert)) . ")
				VALUES
					(" . implode(', ', $insert) . ")");
		}
	}

	exit();
}