<?php

/**
 * Delete todo page.
 *
 * @package    local_todo
 * @copyright  2025 Anton Khrobust
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

require_login();

$context = context_user::instance($USER->id);
require_capability('local/todo:delete', $context);

// get permissions
$todo = local_todo_get_todo($id);
if (!$todo || !local_todo_can_manage_todo($id)) {
    throw new moodle_exception('invalidtodo', 'local_todo');
}

$PAGE->set_context($context);
$PAGE->set_url('/local/todo/delete.php', ['id' => $id]);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('deletetodo', 'local_todo'));
$PAGE->set_heading(get_string('deletetodo', 'local_todo'));

if ($confirm && confirm_sesskey()) {
    if (local_todo_delete_todo($id)) {
        redirect(
            new moodle_url('/local/todo/index.php'),
            get_string('tododeleted', 'local_todo')
        );
    } else {
        throw new moodle_exception('Failed to delete todo');
    }
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('deletetodo', 'local_todo'));

$message = get_string('confirmdelete', 'local_todo') . '<br><br>' .
    '<strong>' . format_string($todo->name) . '</strong>';

echo $OUTPUT->confirm(
    $message,
    new moodle_url('/local/todo/delete.php', ['id' => $id, 'confirm' => 1, 'sesskey' => sesskey()]),
    new moodle_url('/local/todo/index.php')
);

echo $OUTPUT->footer();
