<?php

/**
 * This file is part of Totara LMS
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author patrick.nehme@learnchamp.com <totara@learnchamp.com>
 * @package block_hello_world
 */

defined('MOODLE_INTERNAL') || die();

class block_hello_world extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_hello_world');
    }

    function get_required_javascript() {
        global $PAGE;

        $PAGE->requires->js_call_amd('block_hello_world/hello_world', 'init', []);
    }

    function get_content() {
        global $PAGE, $USER, $CFG, $DB, $OUTPUT;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $coreRenderer = $PAGE->get_renderer('core');
        $templateData = [];
        $templateData['heading'] = get_string('heading', 'block_hello_world');

        $this->content = new stdClass;
        $this->content->text = $coreRenderer->render_from_template('block_hello_world/hello_world', $templateData);

        return $this->content;
    }
}
