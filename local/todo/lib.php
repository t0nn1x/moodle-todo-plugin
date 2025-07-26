<?php

/**
 * Library for the todo local plugin.
 *
 * @package    local_todo
 * @copyright  2025 Anton Khrobust
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add todo link to the main navigation panel.
 *
 * @param global_navigation $nav The global navigation object
 */
function local_todo_extend_navigation(global_navigation $nav) {
    global $PAGE;

    // Only show to logged-in users (optional)
    if (isloggedin() && !isguestuser()) {
        $node = navigation_node::create(
            get_string('pluginname', 'local_todo'), // Link text
            new moodle_url('/local/todo/index.php'), // Link URL
            navigation_node::TYPE_CUSTOM,
            null,
            'local_todo',
            new pix_icon('i/check', '') // Optional: use a Moodle icon
        );
        $nav->add_node($node);
    }
}

/**
 * Add todo link to user profile menu.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $user The user object
 * @param context_user $usercontext The user context
 * @param stdClass $course The course to object
 * @param context_course $coursecontext The course context
*/
function local_todo_extend_navigation_user_profile(navigation_node $navigation, stdClass $user, context_user $usercontext, stdClass $course, context_course $coursecontext) {
    global $USER;

    // Only add for current user
    if ($USER->id == $user->id && has_capability('local/todo:view', $usercontext)) {
        $url = new moodle_url('/local/todo/index.php');
        $node = navigation_node::create(
            get_string('todolist', 'local_todo'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'todo',
            new pix_icon('i/report', '')
        );
        $navigation->add_node($node);
    }
}

/**
 * Get all todos for a user.
 *
 * @param int $userid User ID
 * @param bool $completedonly Show only completed todos
 * @return array Array of todo objects
*/
function local_todo_get_user_todos($userid, $completedonly = false) {
    global $DB;

    $conditions = ['userid' => $userid];
    if ($completedonly !== false) {
        $conditions['completed'] = $completedonly ? 1 : 0;
    }

    return $DB->get_records('local_todo', $conditions, 'duedate ASC, timecreated DESC');
}

/**
 * Get a single todo by ID.
 *
 * @param int $todoid Todo ID
 * @return stdClass|false Todo object or false if not found
*/
function local_todo_get_todo($todoid) {
    global $DB;
    return $DB->get_record('local_todo', ['id' => $todoid]);
}

/**
 * Create a new todo.
 *
 * @param stdClass $data Todo data
 * @return int The ID of the new todo
*/
function local_todo_create_todo($data) {
    global $DB, $USER;

    $todo = new stdClass();
    $todo->userid = $USER->id;
    $todo->name = $data->name;
    $todo->description = $data->description ?? '';
    $todo->duedate = $data->duedate ?? null;
    $todo->completed = $data->completed ?? 0;
    $todo->timecreated = time();
    $todo->timemodified = time();

    return $DB->insert_record('local_todo', $todo);
}

/**
 * Update an existing todo.
 *
 * @param int $todoid Todo ID
 * @param stdClass $data Updated todo data
 * @return bool True on success
*/
function local_todo_update_todo($todoid, $data) {
    global $DB;

    $todo = local_todo_get_todo($todoid);
    if (!$todo) {
        return false;
    }

    $todo->name = $data->name;
    $todo->description = $data->description ?? '';
    $todo->duedate = $data->duedate ?? null;
    $todo->completed = $data->completed ?? 0;
    $todo->timemodified = time();

    return $DB->update_record('local_todo', $todo);
}

/**
 * Delete a todo.
 *
 * @param int $todoid Todo ID
 * @return bool True on success
*/
function local_todo_delete_todo($todoid) {
    global $DB;
    return $DB->delete_records('local_todo', ['id' => $todoid]);
}

/**
 * Check if user can manage a specific todo.
 *
 * @param int $todoid Todo ID
 * @param int $userid User ID (optional, defaults to current user)
 * @return bool True if user can manage the todo
*/
function local_todo_can_manage_todo($todoid, $userid = null)
{
    global $USER, $DB;

    if ($userid === null) {
        $userid = $USER->id;
    }

    $todo = local_todo_get_todo($todoid);
    if (!$todo) {
        return false;
    }

    $usercontext = context_user::instance($userid);
    return $todo->userid == $userid && has_capability('local/todo:manage', $usercontext);
}
