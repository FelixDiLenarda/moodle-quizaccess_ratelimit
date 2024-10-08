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
 * Implementation of the quizaccess_ratelimit plugin.
 *
 * @package    quizaccess_ratelimit
 * @copyright  2021 Martin Gauk, TU Berlin <gauk@math.tu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Workaround to support Moodle 4.1 as well as 4.2 and higher.
// For reference see `mod/quiz/accessrule/upgrade.txt`.
if (class_exists('\mod_quiz\local\access_rule_base')) {
    // These aliases ensure Moodle >=4.2 compatibility.
    class_alias('\mod_quiz\local\access_rule_base', '\access_rule_base_alias');
    class_alias('\mod_quiz\form\preflight_check_form', '\preflight_check_form_alias');
    class_alias('\mod_quiz\quiz_settings', '\quiz_settings_alias');
} else {
    // These aliases ensure Moodle <=4.1 compatibility.
    require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');
    class_alias('\quiz_access_rule_base', '\access_rule_base_alias');
    class_alias('\mod_quiz_preflight_check_form', '\preflight_check_form_alias');
    class_alias('\quiz', '\quiz_settings_alias');
}

/**
 * Implementation of the quizaccess_ratelimit plugin.
 *
 * @copyright  2021 Martin Gauk, TU Berlin <gauk@math.tu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_ratelimit extends access_rule_base_alias {

    /**
     * This is the maximum possible delay (created by this plugin) before a quiz attempt can be started.
     */
    const MAX_DELAY = 15 * 60;

    /**
     * Return an appropriately configured instance of this rule, if it is applicable
     * to the given quiz, otherwise return null.
     *
     * @param quiz_settings_alias $quizobj information about the quiz in question.
     * @param int $timenow the time that should be considered as 'now'.
     * @param bool $canignoretimelimits whether the current user is exempt from
     *      time limits by the mod/quiz:ignoretimelimits capability.
     * @return self|null the rule, if applicable, else null.
     */
    public static function make(
        quiz_settings_alias $quizobj,
        $timenow,
        $canignoretimelimits,
    ): self|null {
        return new self($quizobj, $timenow);
    }

    /**
     * Information, such as might be shown on the quiz view page, relating to this restriction.
     * There is no obligation to return anything. If it is not appropriate to tell students
     * about this rule, then just return ''.
     * @return mixed a message, or array of messages, explaining the restriction
     *         (may be '' if no message is appropriate).
     */
    public function description(): string {
        global $PAGE;

        if ($this->quizobj->has_capability('quizaccess/ratelimit:exempt')) {
            $maxdelay = 0;
        } else if ($this->quiz->timeclose && $this->quiz->timelimit) {
            // The user should have enough time to take the quiz including a short safety margin.
            $maxdelay = max(0, $this->quiz->timeclose - $this->quiz->timelimit - $this->timenow - 2);
            $maxdelay = min(self::MAX_DELAY, $maxdelay);
        } else {
            $maxdelay = self::MAX_DELAY;
        }

        $PAGE->requires->js_call_amd('quizaccess_ratelimit/ratelimit', 'init', [$maxdelay]);

        return '';
    }

    /**
     * The pre-flight check has passed. This is a chance to record that fact in
     * some way.
     * @param int|null $attemptid the id of the current attempt, if there is one,
     *      otherwise null.
     */
    public function notify_preflight_check_passed($attemptid): void {
        global $SESSION;
        unset($SESSION->quizaccess_ratelimit_time);
    }
}
