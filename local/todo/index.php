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

echo html_writer::start_div('todo-main-container');

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

// calculate statistics
$total_todos = count($todos);
$completed_todos = 0;
$pending_todos = 0;

foreach ($todos as $todo) {
    if ($todo->completed) {
        $completed_todos++;
    } else {
        $pending_todos++;
    }
}

// create header with title and statistics
echo html_writer::start_div('todo-header');
echo html_writer::tag('h1', get_string('todos', 'local_todo'), ['class' => 'todo-title']);
echo html_writer::start_div('todo-stats');
echo html_writer::tag('span', 'Total: ' . $total_todos, ['class' => 'stat-item']);
echo html_writer::tag('span', 'Pending: ' . $pending_todos, ['class' => 'stat-item stat-pending']);
echo html_writer::tag('span', 'Completed: ' . $completed_todos, ['class' => 'stat-item stat-completed']);
echo html_writer::end_div();
echo html_writer::end_div();

if (empty($todos)) {
    echo $OUTPUT->notification(get_string('notodos', 'local_todo'), 'info');
} else {
    // create table
    $table = new html_table();
    $table->head = [
        get_string('name', 'local_todo'),
        get_string('duedate', 'local_todo'),
        get_string('status', 'local_todo'),
        get_string('actions', 'local_todo')
    ];
    $table->attributes['class'] = 'todo-table';

    foreach ($todos as $todo) {
        $name = format_string($todo->name);
        $duedate = $todo->duedate ? userdate($todo->duedate, get_string('strftimedatefullshort')) : '-';
        $status = $todo->completed ?
            html_writer::span(get_string('complete', 'local_todo'), 'badge badge-success') :
            html_writer::span(get_string('pending', 'local_todo'), 'badge badge-warning');

        $actions = [];

        if (has_capability('local/todo:manage', $context)) {
            $editurl = new moodle_url('/local/todo/index.php', ['id' => $todo->id]);
            $actions[] = html_writer::link(
                $editurl,
                get_string('edit', 'local_todo'),
                ['class' => 'btn btn-secondary']
            );
        }

        if (has_capability('local/todo:delete', $context)) {
            $deleteurl = new moodle_url('/local/todo/delete.php', ['id' => $todo->id]);
            $actions[] = html_writer::link(
                $deleteurl,
                get_string('delete', 'local_todo'),
                ['class' => 'btn btn-danger']
            );
        }

        $table->data[] = [$name, $duedate, $status, implode(' ', $actions)];
    }

    echo html_writer::table($table);
}

// display the form below the table
$formtitle = $editing ? get_string('edittodo', 'local_todo') : get_string('addtodo', 'local_todo');

echo html_writer::start_div('todo-form-wrapper', ['style' => 'border: 1px solid #ccc; border-radius: 8px; padding: 24px; margin-top: 24px; background: #fafafa;']);

echo html_writer::start_div('todo-form-header');
echo html_writer::tag('h3', $formtitle, ['class' => 'mt-4']);

if ($editing) {
    // show a 'New Todo' button while in edit mode to return to add mode
    $newurl = new moodle_url('/local/todo/index.php');
    echo html_writer::link($newurl, get_string('addtodo', 'local_todo'), ['class' => 'btn btn-link']);
}

echo html_writer::end_div();

$mform->display();
echo html_writer::end_div();

echo html_writer::end_div();

echo $OUTPUT->footer();
