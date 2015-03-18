@javascript @block_quickmail_groups_feature
Feature: Control student use of Quickmail in courses in accordance with FERPA constraints. 
    That is, students in one section (group) should not be able to email members of other groups

    Background: 
        Given the following "courses" exist:
          | fullname | shortname | category | groupmode |
          | Course 1 | C1 | 0 | 1 |
        And the following "users" exist:
          | username | firstname | lastname | email |
          | teacher1 | Teacher   | 1        | teacher1@asd.com |
          | student1 | Student   | 1        | student1@asd.com |
          | student2 | Student   | 2        | student2@asd.com |
        And the following "course enrolments" exist:
          | user | course | role |
          | teacher1 | C1 | editingteacher |
          | student1 | C1 | student |
          | student2 | C1 | student |
        Given the following "groups" exist:
           | name    | description | course  | idnumber |
           | g1      | group1      | C1      | group1   |
           | g2      | group2      | C1      | group2   |
        Given the following "group members" exist:
           | user      | group  |
           | student1 | group1  |
           | student2 | group2 |
        Given I log in as "teacher1"
        And I follow "Course 1"
        And I turn editing mode on
        When I add the "Quickmail" block
        Then I should see "Compose New Email" in the "Quickmail" "block"
        And I log out

    Scenario: Teacher sees all
        Given I log in as "teacher1"
        And I follow "Course 1"
        When I click on "Compose New Email" "link" in the "Quickmail" "block"
        Then I should see "Student 1 (g1)" in the "#from_users" "css_element"
        And I should see "Student 2 (g2)" in the "#from_users" "css_element"
        And I should not see "Teacher 1 (Not in a group)" in the "#from_users" "css_element"

        When I click on "All Users" "text" in the "#groups" "css_element"
        Then "//select[@id='from_users']/option[text()='Student 1 (g1)' and @selected='selected']" "xpath_element" should exist
        And  "//select[@id='from_users']/option[text()='Student 2 (g2)' and @selected='selected']" "xpath_element" should exist

        When I click on "All Groups" "text" in the "#groups" "css_element"
        Then "//select[@id='from_users']/option[text()='Student 1 (g1)' and @selected='selected']" "xpath_element" should exist
        And  "//select[@id='from_users']/option[text()='Student 2 (g2)' and @selected='selected']" "xpath_element" should exist

        When I click on "g2" "text" in the "#groups" "css_element"
        Then "//select[@id='from_users']/option[text()='Student 1 (g1)' and @selected='selected']" "xpath_element" should not exist
        And  "//select[@id='from_users']/option[text()='Student 2 (g2)' and @selected='selected']" "xpath_element" should exist

        When I click on "g1" "text" in the "#groups" "css_element"
        Then "//select[@id='from_users']/option[text()='Student 1 (g1)' and @selected='selected']" "xpath_element" should exist
        And  "//select[@id='from_users']/option[text()='Student 2 (g2)' and @selected='selected']" "xpath_element" should not exist

    Scenario: Make sure students can't see other groups members
        Given I log in as "teacher1"
        And   I follow "Course 1"
        And   I click on "Configuration" "link" in the "Quickmail" "block"
        And   I set the following fields to these values:
            | Allow students to use Quickmail | Yes |
        And   I press "Save changes"
        And   I log out

        And   I log in as "student2"
        And   I follow "Course 1"
        When  I click on "Compose New Email" "link" in the "Quickmail" "block"
        And   I click on "All Users" "text" in the "groups" "css_element"
        Then  "//select[@id='from_users']/option[text()='Student 1 (g1)' and @selected='selected']" "xpath_element" should not exist
        And   "//select[@id='from_users']/option[text()='Student 2 (g2)' and @selected='selected']" "xpath_element" should not exist
        And   "//select[@id='from_users']/option[text()='Teacher 1 (Not in a group)' and @selected='selected']" "xpath_element" should exist
