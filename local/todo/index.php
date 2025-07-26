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

require_login();

$context =   $context = context_system::instance();
require_capability('local/todo:view', $context);

$PAGE->set_context($context);
$PAGE->set_url('/local/todo/index.php');
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('todolist', 'local_todo'));
$PAGE->set_heading(get_string('todolist', 'local_todo'));

echo $OUTPUT->header();

$addurl = new moodle_url('/local/todo/edit.php');
echo html_writer::div(
    $OUTPUT->single_button($addurl, get_string('addtodo', 'local_todo'), 'get', ['class' => 'btn btn-primary']),
    'mb-3'
);

// get user's todos
$todos = local_todo_get_user_todos($USER->id);

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
    $table->attributes['class'] = 'table table-striped';

    foreach ($todos as $todo) {
        $name = format_string($todo->name);
        $duedate = $todo->duedate ? userdate($todo->duedate, get_string('strftimedatefullshort')) : '-';
        $status = $todo->completed ?
            html_writer::span(get_string('complete', 'local_todo'), 'badge badge-success') :
            html_writer::span(get_string('pending', 'local_todo'), 'badge badge-warning');

        // action buttons
        $actions = [];

        if (has_capability('local/todo:manage', $context)) {
            $editurl = new moodle_url('/local/todo/edit.php', ['id' => $todo->id]);
            $actions[] = html_writer::link(
                $editurl,
                get_string('edit', 'local_todo'),
                ['class' => 'btn btn-sm btn-secondary']
            );
        }

        if (has_capability('local/todo:delete', $context)) {
            $deleteurl = new moodle_url('/local/todo/delete.php', ['id' => $todo->id]);
            $actions[] = html_writer::link(
                $deleteurl,
                get_string('delete', 'local_todo'),
                ['class' => 'btn btn-sm btn-danger']
            );
        }

        $table->data[] = [$name, $duedate, $status, implode(' ', $actions)];
    }

    echo html_writer::table($table);
}

echo $OUTPUT->footer();
