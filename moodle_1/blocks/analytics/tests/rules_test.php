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
 * Test rules.
 *
 * @package    block_analytics
 * @copyright  2014 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Rules testcase.
 *
 * @package    block_analytics
 * @copyright  2014 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_analytics_rules_testcase extends advanced_testcase {

    public function test_rule_property() {
        $subject = (object) array(
            'int' => 10,
            'str' => 'I am here.'
        );

        $rule = new block_analytics_rule_property(block_analytics_rule_base::CT, 'I', 'str');
        $this->assertTrue($rule->match($subject));
        $rule = new block_analytics_rule_property(block_analytics_rule_base::CT, 'am not', 'str');
        $this->assertFalse($rule->match($subject));

        $rule = new block_analytics_rule_property(block_analytics_rule_base::EQ, 10, 'int');
        $this->assertTrue($rule->match($subject));
        $rule = new block_analytics_rule_property(block_analytics_rule_base::EQ, 11, 'int');
        $this->assertFalse($rule->match($subject));

        $rule = new block_analytics_rule_property(block_analytics_rule_base::EQ, 'I am here.', 'str');
        $this->assertTrue($rule->match($subject));
        $rule = new block_analytics_rule_property(block_analytics_rule_base::EQ, 'I am not here.', 'str');
        $this->assertFalse($rule->match($subject));

        $rule = new block_analytics_rule_property(block_analytics_rule_base::EQS, 10, 'int');
        $this->assertTrue($rule->match($subject));
        $rule = new block_analytics_rule_property(block_analytics_rule_base::EQS, '10', 'int');
        $this->assertFalse($rule->match($subject));

        $rule = new block_analytics_rule_property(block_analytics_rule_base::GT, 5, 'int');
        $this->assertTrue($rule->match($subject));
        $rule = new block_analytics_rule_property(block_analytics_rule_base::GT, 15, 'int');
        $this->assertFalse($rule->match($subject));

        $rule = new block_analytics_rule_property(block_analytics_rule_base::GTE, 5, 'int');
        $this->assertTrue($rule->match($subject));
        $rule = new block_analytics_rule_property(block_analytics_rule_base::GTE, 10, 'int');
        $this->assertTrue($rule->match($subject));
        $rule = new block_analytics_rule_property(block_analytics_rule_base::GTE, 11, 'int');
        $this->assertFalse($rule->match($subject));

        $rule = new block_analytics_rule_property(block_analytics_rule_base::LT, 5, 'int');
        $this->assertFalse($rule->match($subject));
        $rule = new block_analytics_rule_property(block_analytics_rule_base::LT, 15, 'int');
        $this->assertTrue($rule->match($subject));

        $rule = new block_analytics_rule_property(block_analytics_rule_base::LTE, 5, 'int');
        $this->assertFalse($rule->match($subject));
        $rule = new block_analytics_rule_property(block_analytics_rule_base::LTE, 10, 'int');
        $this->assertTrue($rule->match($subject));
        $rule = new block_analytics_rule_property(block_analytics_rule_base::LTE, 9, 'int');
        $this->assertFalse($rule->match($subject));

        $rule = new block_analytics_rule_property(block_analytics_rule_base::RX, '/^I/', 'str');
        $this->assertTrue($rule->match($subject));
        $rule = new block_analytics_rule_property(block_analytics_rule_base::RX, '/^You/', 'str');
        $this->assertFalse($rule->match($subject));

    }

    public function test_ruleset() {
        $subject = (object) array(
            'int' => 10,
        );

        $rs = new block_analytics_ruleset(array(), block_analytics_ruleset::ANY);
        $rs->add_rule(new block_analytics_rule_property(block_analytics_rule_base::EQ, 10, 'int'));
        $rs->add_rule(new block_analytics_rule_property(block_analytics_rule_base::EQ, 99, 'int'));
        $this->assertTrue($rs->match($subject));

        $rs = new block_analytics_ruleset(array(), block_analytics_ruleset::ANY);
        $rs->add_rule(new block_analytics_rule_property(block_analytics_rule_base::EQ, 0, 'int'));
        $rs->add_rule(new block_analytics_rule_property(block_analytics_rule_base::EQ, 99, 'int'));
        $this->assertFalse($rs->match($subject));

        $rs = new block_analytics_ruleset(array(), block_analytics_ruleset::ALL);
        $rs->add_rule(new block_analytics_rule_property(block_analytics_rule_base::EQ, 10, 'int'));
        $rs->add_rule(new block_analytics_rule_property(block_analytics_rule_base::EQ, 9, 'int'));
        $this->assertFalse($rs->match($subject));

        $rs = new block_analytics_ruleset(array(), block_analytics_ruleset::ALL);
        $rs->add_rule(new block_analytics_rule_property(block_analytics_rule_base::EQ, 10, 'int'));
        $rs->add_rule(new block_analytics_rule_property(block_analytics_rule_base::LTE, 10, 'int'));
        $this->assertTrue($rs->match($subject));

        $rs = new block_analytics_ruleset(array(), block_analytics_ruleset::NONE);
        $rs->add_rule(new block_analytics_rule_property(block_analytics_rule_base::EQ, 10, 'int'));
        $rs->add_rule(new block_analytics_rule_property(block_analytics_rule_base::EQ, 99, 'int'));
        $this->assertFalse($rs->match($subject));

        $rs = new block_analytics_ruleset(array(), block_analytics_ruleset::NONE);
        $rs->add_rule(new block_analytics_rule_property(block_analytics_rule_base::EQ, 0, 'int'));
        $rs->add_rule(new block_analytics_rule_property(block_analytics_rule_base::EQ, 99, 'int'));
        $this->assertTrue($rs->match($subject));
    }

    public function test_nested_ruleset() {
        $subject = (object) array(
            'int' => 10,
        );

        $rs = new block_analytics_ruleset(array(), block_analytics_ruleset::ANY);
        $rs->add_rule(new block_analytics_rule_property(block_analytics_rule_base::EQ, 0, 'int'));
        $rs->add_rule(new block_analytics_ruleset(array(
            new block_analytics_rule_property(block_analytics_rule_base::EQ, 10, 'int'),
            new block_analytics_rule_property(block_analytics_rule_base::LTE, 10, 'int'),
            new block_analytics_rule_property(block_analytics_rule_base::GTE, 10, 'int')
        ), block_analytics_ruleset::ALL));
        $rs->add_rule(new block_analytics_rule_property(block_analytics_rule_base::EQ, 99, 'int'));
        $this->assertTrue($rs->match($subject));

    }

    public function test_eanalyticsort_create() {
        $rule = new block_analytics_rule_property(block_analytics_rule_base::CT, 'I', 'str');
        $newrule = block_analytics_rule::create($rule->eanalyticsort());
        $this->assertEquals($rule, $newrule);

        $rule = new block_analytics_rule_property(block_analytics_rule_base::CT, 'I', 'str');
        $rs = new block_analytics_ruleset(array($rule), block_analytics_ruleset::ALL);
        $newrs = block_analytics_rule::create($rs->eanalyticsort());
        $this->assertEquals($rs, $newrs);

        // Test with bad data.
        $data = $rs->eanalyticsort();
        $data['_class'] = 'Does not exist';
        $this->assertFalse(block_analytics_rule::create($data));
    }
}
