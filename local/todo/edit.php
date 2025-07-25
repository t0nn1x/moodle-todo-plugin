<?php

/**
 * Edit todo page.
 *
 * @package    local_todo
 * @copyright  2025 Anton Khrobust
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = optional_param('id', 0, PARAM_INT);

require_login();

$context = context_user::instance($USER->id);
require_capability('local/todo:manage', $context);

// check if editing existing todo
$todo = null;
$editing = false;
if ($id) {
    $todo = local_todo_get_todo($id);
    if (!$todo || !local_todo_can_manage_todo($id)) {
        throw new moodle_exception('invalidtodo', 'local_todo');
    }
    $editing = true;
}

$PAGE->set_context($context);
$PAGE->set_url('/local/todo/edit.php', ['id' => $id]);
$PAGE->set_pagelayout('standard');

$title = $editing ? get_string('edittodo', 'local_todo') : get_string('addtodo', 'local_todo');
$PAGE->set_title($title);
$PAGE->set_heading($title);

$mform = new \local_todo\form\todo_form();

if ($editing) {
    $mform->set_data($todo);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/todo/index.php'));
} else if ($data = $mform->get_data()) {
    if ($editing) {
        // update existing todo
        if (local_todo_update_todo($id, $data)) {
            redirect(
                new moodle_url('/local/todo/index.php'),
                get_string('todoupdated', 'local_todo')
            );
        } else {
            throw new moodle_exception('Failed to update todo');
        }
    } else {
        // create new todo
        $newid = local_todo_create_todo($data);
        if ($newid) {
            redirect(
                new moodle_url('/local/todo/index.php'),
                get_string('todoadded', 'local_todo')
            );
        } else {
            throw new moodle_exception('Failed to create todo');
        }
    }
}

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
