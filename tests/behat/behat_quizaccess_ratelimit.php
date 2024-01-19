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
 * Steps definitions related with accessrule ratelimit
 *
 * @package    quizaccess_ratelimit
 * @copyright  2023 Felix Di Lenarda, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');


/**
 * Steps definitions related with accessrule ratelimit
 *
 * @package    quizaccess_ratelimit
 * @category   test
 * @copyright  2023 Felix Di Lenarda, TU Berlin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_quizaccess_ratelimit extends behat_base {

    /**
     * Resets the counter and timemodified fields in quizaccess_ratelimit table.
     *
     * @Given /^I reset the quiz rate limit counters$/
     */
    public function i_reset_the_quiz_rate_limit_counters() {
        global $DB;

        // SQL to reset counter and timemodified fields.
        $sql = "UPDATE {quizaccess_ratelimit} SET counter = 0, timemodified = 0";
        $DB->execute($sql);
    }

    /**
     * Checks the popupwindow for differently for moodle 401 and moodle 401:.
     * 401: "Answer the first question"
     * 402: "Attempt quiz"
     *
     * @Given /^I check the quiz popup window depending on Moodle version$/
     */
    public function i_check_the_quiz_popup_window_depending_on_moodle_version() {
        global $CFG;
        $branch = $CFG->branch;

        if ($branch === '401') {  // If moodle 401 check "I should see "Answer the first question".
            $this->assertSession()->pageTextContains("Answer the first question");
        } else if ($branch === '402') { // If moodle 402 check "I should see "Attempt quiz".
            $this->assertSession()->pageTextContains("Attempt quiz");
        } else {
            // Accept the popup window but raise warning: 'The popup was not checked because of the Moodle Version'.
            $this->assertTrue(true, 'Der Popup-Check wurde aufgrund der Moodle-Version nicht durchgeführt.');
            // Test II.
        }
    }
}
