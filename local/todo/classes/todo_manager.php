<?php

/**
 * Todo manager class for the todo local plugin.
 *
 * @package    local_todo
 * @copyright  2025 Anton Khrobust
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_todo;

defined('MOODLE_INTERNAL') || die();

/**
 * Todo manager class for handling todo operations.
 */
class todo_manager {

    /**
     * Get all todos for a user.
     *
     * @param int $userid User ID
     * @param bool $completedonly Show only completed todos
     * @return array Array of todo objects
     */
    public static function get_user_todos($userid, $completedonly = false) {
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
     * @return \stdClass|false Todo object or false if not found
     */
    public static function get_todo($todoid) {
        global $DB;
        return $DB->get_record('local_todo', ['id' => $todoid]);
    }

    /**
     * Create a new todo.
     *
     * @param \stdClass $data Todo data
     * @param int|null $userid User ID (defaults to current user)
     * @return int The ID of the new todo
     */
    public static function create_todo($data, $userid = null) {
        global $DB, $USER;

        if ($userid === null) {
            $userid = $USER->id;
        }

        $todo = new \stdClass();
        $todo->userid = $userid;
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
     * @param \stdClass $data Updated todo data
     * @return bool True on success
     */
    public static function update_todo($todoid, $data) {
        global $DB;

        $todo = self::get_todo($todoid);
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
    public static function delete_todo($todoid) {
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
    public static function can_manage_todo($todoid, $userid = null): bool {
        global $USER;

        if ($userid === null) {
            $userid = $USER->id;
        }

        $todo = self::get_todo($todoid);
        if (!$todo) {
            return false;
        }

        $usercontext = \context_user::instance($userid);
        return $todo->userid == $userid && has_capability('local/todo:manage', $usercontext);
    }

    /**
     * Get statistics for user's todos.
     *
     * @param int $userid User ID
     * @return \stdClass Statistics object with total, pending, completed counts
     */
    public static function get_user_statistics($userid) {
        $todos = self::get_user_todos($userid);
        
        $stats = new \stdClass();
        $stats->total = count($todos);
        $stats->completed = 0;
        $stats->pending = 0;

        foreach ($todos as $todo) {
            if ($todo->completed) {
                $stats->completed++;
            } else {
                $stats->pending++;
            }
        }

        return $stats;
    }
} 
