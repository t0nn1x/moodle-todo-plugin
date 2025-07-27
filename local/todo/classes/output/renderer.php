<?php

/**
 * Renderer for the todo local plugin.
 *
 * @package    local_todo
 * @copyright  2025 Anton Khrobust
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_todo\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use html_writer;
use moodle_url;
use local_todo\todo_manager;

/**
 * Renderer for todo plugin
 */
class renderer extends plugin_renderer_base {

    /**
     * Render the main todo page.
     *
     * @param array $todos Array of todo objects
     * @param object $mform The todo form
     * @param bool $editing Whether we're in edit mode
     * @param object|null $todo Current todo being edited (if any)
     * @return string HTML output
     */
    public function render_todo_page($todos, $mform, $editing = false, $todo = null) {
        global $USER;
        
        $data = new \stdClass();
        
        $stats = todo_manager::get_user_statistics($USER->id);
        
        $data->title = get_string('todos', 'local_todo');
        $data->stats = (object) [
            'total' => $stats->total,
            'pending' => $stats->pending,
            'completed' => $stats->completed,
            'total_label' => get_string('total', 'local_todo'),
            'pending_label' => get_string('pending', 'local_todo'),
            'completed_label' => get_string('complete', 'local_todo')
        ];

        // check if no todos
        $data->notodos = empty($todos);
        if ($data->notodos) {
            $data->notification = (object) [
                'message' => get_string('notodos', 'local_todo')
            ];
        }

        // table data
        if (!empty($todos)) {
            $data->table = $this->prepare_table_data($todos);
        }

        $data->form = $this->prepare_form_data($mform, $editing, $todo);

        return $this->render_from_template('local_todo/index', $data);
    }

    /**
     * Prepare table data for template.
     *
     * @param array $todos Array of todo objects
     * @return stdClass Table data
     */
    protected function prepare_table_data($todos) {
        global $USER;
        
        $context = \context_system::instance();
        
        $table = (object) [
            'headers' => [
                get_string('name', 'local_todo'),
                get_string('duedate', 'local_todo'),
                get_string('status', 'local_todo'),
                get_string('actions', 'local_todo')
            ],
            'rows' => []
        ];

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
                $deleteurl = new moodle_url('/local/todo/delete.php', [
                    'id' => $todo->id, 
                    'confirm' => 1, 
                    'sesskey' => sesskey()
                ]);
                $actions[] = html_writer::link(
                    $deleteurl,
                    get_string('delete', 'local_todo'),
                    [
                        'class' => 'btn btn-danger',
                        'onclick' => 'return confirm("' . get_string('confirmdelete', 'local_todo') . 
                                   '\\n\\n' . addslashes($todo->name) . '")'
                    ]
                );
            }

            $table->rows[] = (object) [
                'name' => $name,
                'duedate' => $duedate,
                'status' => $status,
                'actions' => implode(' ', $actions)
            ];
        }

        return $table;
    }

    /**
     * Prepare form data for template.
     *
     * @param object $mform The todo form
     * @param bool $editing Whether we're in edit mode
     * @param object|null $todo Current todo being edited (if any)
     * @return stdClass Form data
     */
    protected function prepare_form_data($mform, $editing = false, $todo = null) {
        // start output buffering to capture form HTML
        ob_start();
        $mform->display();
        $form_html = ob_get_clean();

        $form = (object) [
            'title' => $editing ? get_string('edittodo', 'local_todo') : get_string('addtodo', 'local_todo'),
            'form_html' => $form_html,
            'editing' => $editing
        ];

        if ($editing) {
            $form->new_todo_url = (new moodle_url('/local/todo/index.php'))->out(false);
            $form->new_todo_text = get_string('addtodo', 'local_todo');
        }

        return $form;
    }
} 
