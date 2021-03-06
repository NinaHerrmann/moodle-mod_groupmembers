<?php
// This file is part of a plugin for Moodle - http://moodle.org/
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
 * Renderer definition for mod_groupmembers
 *
 * @package    mod_groupmembers
 * @copyright  2017 Dennis M. Riehle, WWU Münster
 * @copyright  2017 Jan C. Dageförde, WWU Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__. '/lib.php');
require_once($CFG->libdir. '/weblib.php');

/**
 * Renderer definition for mod_groupmembers
 *
 * @package    mod_groupmembers
 * @copyright  2017 Dennis M. Riehle, WWU Münster
 * @copyright  2017 Jan C. Dageförde, WWU Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupmembers_renderer extends plugin_renderer_base {

    /**
     * Render the groups table(s)
     *
     * @param array $groups Array of groups as created by \mod_groupmembers\groups::get_groups_and_members()
     * @param bool $showemail Setting whether email addresses should be suppressed
     * @return string Rendered template
     */
    public function render_allgroups(array $groups, $showemail) {
        global $USER, $COURSE, $CFG;
        $data = array(
            'groups' => []
        );
        foreach ($groups as $group) {
            $members = array();
            foreach ($group['members'] as $member) {
                $memberemail = null;
                $memberemailtext = null;
                $memberemailhidden = false;
                $membermessage = null;
                if ($USER->id != $member->id) {
                    if ($member->maildisplay == core_user::MAILDISPLAY_HIDE) {
                        // Email address should not be rendered unless user has at least enabled display to course members.
                        // User has chosen not to reveal an email address. This can be made explicit by the template.
                        $memberemailhidden = true;
                    } else if ($showemail == GROUPMEMBERS_SHOWEMAIL_ALLGROUPS ||
                        ($showemail == GROUPMEMBERS_SHOWEMAIL_OWNGROUP && $group['ismember'])) {
                        // Since user allows showing, the final decision is up to the module's settings ($showemail).
                        $memberemail = obfuscate_email($member->email);
                        $memberemailtext = obfuscate_text($member->email);
                    }

                    if (!empty($CFG->messaging) &&
                        has_capability('moodle/site:sendmessage', \context_system::instance())) {
                        $membermessage = new moodle_url('/message/index.php', ['id' => $member->id]);
                    }
                }

                $members[] = array(
                    'id' => $member->id,
                    'picture' => $this->output->user_picture($member),
                    'displayname' => fullname($member),
                    'maillink' => $memberemail,
                    'mailtext' => $memberemailtext,
                    'mailhidden' => $memberemailhidden,
                    'profileurl' => new moodle_url('/user/view.php', ['id' => $member->id, 'course' => $COURSE->id]),
                    'messageurl' => $membermessage
                );
            }

            $data['groups'][] = array(
                'id' => $group['group']->id,
                'name' => $group['group']->name,
                'members' => $members,
                'ismember' => $group['ismember']
            );
        }
        return $this->render_from_template('mod_groupmembers/allgroups', $data);
    }
}
