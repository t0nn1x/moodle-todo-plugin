<?php

/**
 * Todo list page.
 *
 * @package    local_todo
 * @copyright  2025 Anton Khrobust
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/todo/classes/form/todo_form.php');

require_login();

$context = context_system::instance();
require_capability('local/todo:view', $context);

$PAGE->set_context($context);
$PAGE->set_url('/local/todo/index.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('todolist', 'local_todo'));
$PAGE->set_heading(get_string('todolist', 'local_todo'));
$PAGE->requires->css(new moodle_url('/local/todo/styles.css'));

echo $OUTPUT->header();

$id = optional_param('id', 0, PARAM_INT);
$editing = false;
$todo = null;
if ($id) {
    $todo = local_todo_get_todo($id);
    if ($todo && local_todo_can_manage_todo($id)) {
        $editing = true;
    } else {
        $todo = null;
    }
}

$mform = new \local_todo\form\todo_form();
if ($editing && $todo) {
    $mform->set_data($todo);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/todo/index.php'));
} else if ($data = $mform->get_data()) {
    if (!empty($data->id)) {
        // update existing todo
        if (local_todo_update_todo($data->id, $data)) {
            redirect(new moodle_url('/local/todo/index.php'), get_string('todoupdated', 'local_todo'));
        } else {
            echo $OUTPUT->notification('Failed to update todo', 'error');
        }
    } else {
        // create new todo
        $newid = local_todo_create_todo($data);
        if ($newid) {
            redirect(new moodle_url('/local/todo/index.php'), get_string('todoadded', 'local_todo'));
        } else {
            echo $OUTPUT->notification('Failed to create todo', 'error');
        }
    }
}

// get user's todos
$todos = local_todo_get_user_todos($USER->id);

$renderer = $PAGE->get_renderer('local_todo');
echo $renderer->render_todo_page($todos, $mform, $editing, $todo);

echo $OUTPUT->footer();
