<?php

/**
 * Todo form for the todo local plugin.
 *
 * @package    local_todo
 * @copyright  2025 Anton Khrobust
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_todo\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

class todo_form extends \moodleform {
    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id'); // hidden field for todo id for edit mode
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name', 'local_todo'), ['size' => 50]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('namerequired', 'local_todo'), 'required', null, 'client');
        $mform->addRule('name', get_string('nametoolong', 'local_todo'), 'maxlength', 255, 'client');

        $mform->addElement(
            'textarea',
            'description',
            get_string('description', 'local_todo'),
            ['rows' => 4, 'cols' => 50]
        );
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement(
            'date_selector',
            'duedate',
            get_string('duedate', 'local_todo'),
            ['optional' => true]
        );

        $mform->addElement('checkbox', 'completed', get_string('completed', 'local_todo'));

        $this->add_action_buttons();
    }

    /**
     * Validate form data.
     *
     * @param array $data Form data
     * @param array $files Uploaded files
     * @return array Array of errors
     */
    public function validation($data, $files) { 
        $errors = parent::validation($data, $files);

        if (strlen($data['name']) >255) { 
            $errors['name'] = get_string('nametoolong', 'local_todo');
        }

        if (!empty($data['duedate']) && $data['duedate'] < strtotime('today')) {
            $errors['duedate'] = 'Due date cannot be in the past';
        }

        return $errors;
    }
}
