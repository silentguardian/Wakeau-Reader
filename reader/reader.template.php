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

function template_reader_list()
{
	global $template;

	echo '
		<div class="page-header">
			<h2>Readers List</h2>
		</div>
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>Title</th>
					<th>Level</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>';

	if (empty($template['books']))
	{
		echo '
				<tr>
					<td class="align_center" colspan="3">There are not any books added yet!</td>
				</tr>';
	}

	foreach ($template['books'] as $book)
	{
		echo '
				<tr>
					<td>', $book['title'], '</td>
					<td class="span2 align_center">', $book['level'], '</td>
					<td class="span2 align_center">
						<a class="btn btn-warning" href="', build_url(array('reader', 'export', $book['id'])), '">Export</a>
					</td>
				</tr>';
	}

	echo '
			</tbody>
		</table>';
}

function template_reader_export()
{
	global $template;

	echo '
		<form class="form-horizontal" action="', build_url(array('reader', 'export')), '" method="post" enctype="multipart/form-data">
			<fieldset>
				<legend>Export Questions - ', $template['book']['title'], ' (Level ', $template['book']['level'], ')</legend>';

	$counter = 0;

	foreach ($template['questions'] as $question)
	{
		if ($counter % 10 == 0)
			echo '
				<legend>Question Set ', (($counter / 10) + 1), '</legend>';

		list ($pre, $body) = explode('<br />', nl2br($question['body']));

		echo '
				<div class="well" style="float: ', ($counter++ % 2 == 0 ? 'left' : 'right'), '; width: 45%; min-height: 170px;" id="well_', $counter, '">
					<strong>', $counter, '. ', $pre, '</strong><br />', $body, '<br /><br />';

		foreach ($question['options'] as $key => $value)
		{
			echo '
					', ($key == $question['answer'] ? '<strong>' : ''), $key, ') ', $value, ($key == $question['answer'] ? '</strong>' : ''), '<br />';
		}

		echo '
					<div style="float: right;">
						<label class="checkbox">
							<input type="checkbox" name="questions[]" value="', $question['id'], '" id="check_', $counter, '" onclick="update_question(', $counter, ');"> include
						</label>
					</div>
				</div>';
	}

	echo '
				<br class="clear" />
				<div class="well" style="background-color: #f2dede;">
					Keep in mind that there might be mistakes in the questions which could be related to the original question banks or our system. In any case, feel free to report them so they could be fixed.
				</div>
				<div class="well" style="background-color: #d9edf7;">
					<div class="pull-right">
						<input type="submit" class="btn btn-info" name="export" value="Plain Text" />
						<input type="submit" class="btn btn-primary" name="export" value="ExamView" />
						<input type="submit" class="btn" name="cancel" value="Back to List" />
					</div>
					You have selected <strong><span id="counter">0</span></strong> question(s). Choose the format to export your test.
				</div>
			</fieldset>
			<input type="hidden" name="reader" value="', $template['book']['id'], '" />
		</form>
		<script type="text/javascript"><!-- // --><![CDATA[
			function update_question(id)
			{
				var check = document.getElementById(\'check_\' + id).checked;
				var well = document.getElementById(\'well_\' + id);
				var counter = document.getElementById(\'counter\');

				if (check)
				{
					well.style.backgroundColor = \'#dff0d8\';
					counter.innerHTML = parseInt(counter.innerHTML) + 1;
				}
				else
				{
					well.style.backgroundColor = \'#f5f5f5\';
					counter.innerHTML = parseInt(counter.innerHTML) - 1;
				}
			}
		// ]]></script>';
}