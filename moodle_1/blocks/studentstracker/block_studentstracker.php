<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Studentstracker block
 *
 * @package    block_studentstracker
 * @copyright  2015 Pierre Duverneix
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_studentstracker extends block_base {
    public function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', 'block_studentstracker');
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function has_config() {
        return true;
    }

    public function instance_allow_config() {
        return true;
    }

    public function specialization() {
        if (isset($this->config)) {
            if (empty($this->config->title)) {
                $this->title = get_string('defaulttitle', 'block_studentstracker');
            } else {
                $this->title = $this->config->title;
            }
        }
    }

    public function get_content() {
        global $CFG, $COURSE;

        if ($this->content !== null) {
            return $this->content;
        }

        $context = context_course::instance($COURSE->id);

        if (!has_capability('moodle/course:manageactivities', $context)) {
            return $this->content;
        } else {
            $usercount = 0;

            $this->content = new stdClass();
            $this->content->items = array();

            if (!empty($this->config->days)) {
                $days = '-'.$this->config->days.' day';
            } else {
                $days = '-3 day';
            }

            if (!empty($this->config->days_critical)) {
                $dayscritical = '-'.$this->config->days_critical.' day';
            } else {
                $dayscritical = '-6 day';
            }

            if (!empty($this->config->color_days)) {
                $colordays = $this->config->color_days;
            } else {
                $colordays = '#FFD9BA';
            }

            if (!empty($this->config->color_days_critical)) {
                $colordayscritical = $this->config->color_days_critical;
            } else {
                $colordayscritical = '#FECFCF';
            }

            if (!empty($this->config->color_never)) {
                $colornever = $this->config->color_never;
            } else {
                $colornever = '#D0D0D0';
            }

            if (!empty($this->config->role)) {
                $role = (int)$this->config->role;
            } else {
                $role = 0;
            }

            $confs = array(
                'text_header',
                'text_header_fine',
                'text_never',
                'text_footer'
            );

            foreach ($confs as $conf) {
                if (empty($this->config->$conf)) {
                    $this->$conf = get_string('block_studentstracker_'.$conf, 'block_studentstracker');
                } else {
                    $this->$conf = $this->config->$conf;
                }
            }

            $enrols = get_enrolled_users($context);
            foreach ($enrols as $enrol) {
                if ($role === 0) {
                    if (!has_capability('moodle/course:manageactivities', $context, $enrol)) {
                        if ($enrol->lastaccess != 0) {
                            if ( (intval($enrol->lastaccess) < strtotime($days, time()))
                             && (intval($enrol->lastaccess) >= strtotime($dayscritical, time())) ) {
                                $lastaccess = date('d/m/Y H:i', $enrol->lastaccess);
                                $output = "<a href=\"mailto:$enrol->email\">";
                                $output .= "<li class='studentstracker-first' style='background:".$colordays."'>";
                                $output .= "$enrol->firstname $enrol->lastname<span> - $lastaccess</span></li></a>";
                                array_push($this->content->items, $output);
                                $usercount++;
                                unset($output);
                            } else if (intval($enrol->lastaccess) < strtotime($days, time())) {
                                $lastaccess = date('d/m/Y H:i', $enrol->lastaccess);
                                $output = "<a href=\"mailto:$enrol->email\">";
                                $output .= "<li class='studentstracker-critical' style='background:".$colordayscritical."'>";
                                $output .= "$enrol->firstname $enrol->lastname<span> - $lastaccess</span></li></a>";
                                array_push($this->content->items, $output);
                                $usercount++;
                                unset($output);
                            }
                        } else {
                            $output = "<a href=\"mailto:$enrol->email\">";
                            $output .= "<li class='studentstracker-never' style='background:".$colornever."''>";
                            $output .= "$enrol->firstname $enrol->lastname<span> - $this->text_never</span></li></a>";
                            array_push($this->content->items, $output);
                            $usercount++;
                            unset($output);
                        }
                    }
                } else {
                    if ($enrol->lastaccess != 0) {
                        if ( (intval($enrol->lastaccess) < strtotime($days, time()))
                         && (intval($enrol->lastaccess) >= strtotime($dayscritical, time())) ) {
                            $lastaccess = date('d/m/Y H:i', $enrol->lastaccess);
                            $output = "<a href=\"mailto:$enrol->email\">";
                            $output .= "<li class='studentstracker-first' style='background:".$colordays."'>";
                            $output .= "$enrol->firstname $enrol->lastname<span> - $lastaccess</span></li></a>";
                            array_push($this->content->items, $output);
                            $usercount++;
                            unset($output);
                        } else if (intval($enrol->lastaccess) < strtotime($days, time())) {
                            $lastaccess = date('d/m/Y H:i', $enrol->lastaccess);
                            $output = "<a href=\"mailto:$enrol->email\">";
                            $output .= "<li class='studentstracker-critical' style='background:".$colordayscritical."'>";
                            $output .= "$enrol->firstname $enrol->lastname<span> - $lastaccess</span></li></a>";
                            array_push($this->content->items, $output);
                            $usercount++;
                            unset($output);
                        }
                    } else {
                        $output = "<a href=\"mailto:$enrol->email\">";
                        $output .= "<li class='studentstracker-never' style='background:".$colornever."''>";
                        $output .= "$enrol->firstname $enrol->lastname<span> - $this->text_never</span></li></a>";
                        array_push($this->content->items, $output);
                        $usercount++;
                        unset($output);
                    }
                }
            }

            if ($usercount > 0) {
                $headertext = '<div class="studentstracker_header"><span class="badge badge-warning">'.$usercount.'</span>';
                $headertext .= $this->text_header.'</div>';
                $footertext = '<div class="studentstracker_footer">'.$this->text_footer.'</div>';
            } else {
                $headertext = '<div class="studentstracker_header">'.$this->text_header_fine.'</div>';
                $footertext = '';
            }

            $this->content->text = $headertext;
            $this->content->text .= "<ul>";
            foreach ($this->content->items as $item) {
                $this->content->text .= $item;
            }
            $this->content->text .= "</ul>";
            $this->content->text .= $footertext;

            return $this->content;
        }
    }

    public function applicable_formats() {
        return array('all' => false, 'course' => true, 'course-index' => false);
    }
}
